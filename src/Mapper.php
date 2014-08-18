<?php
namespace Librette\Doctrine\Forms;

use Doctrine\ORM;
use Librette\Doctrine\EntityWrapper;
use Librette\Doctrine\Forms\Handlers;
use Librette\Doctrine\WrappedEntity;
use Librette\Forms\IMapper;
use Nette\ComponentModel\Container;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls;
use Nette\Forms\Form;
use Nette\Object;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * @author David Matějka
 */
class Mapper extends Object implements IMapper
{

	const FLUSH = TRUE;
	const NO_FLUSH = FALSE;

	const OPERATION_LOAD = 'load';
	const OPERATION_SAVE = 'save';

	public $onFlush = [];

	public $onAfterFlush = [];

	/** @var object */
	protected $entity;

	/** @var boolean */
	protected $autoFlush = TRUE;

	/** @var array */
	protected $offset;

	/** @var ORM\EntityManager */
	protected $entityManager;

	/** @var IHandler[] */
	protected $handlers = [];

	/** @var \Librette\Doctrine\EntityWrapper */
	protected $entityWrapper;

	/** @var ValidatorInterface */
	protected $validator;

	/** @var IViolationMapper */
	protected $violationMapper;

	/** @var IExecutionStrategy */
	protected $executionStrategy;


	/**
	 * @param object
	 * @param array|null|string
	 * @param ORM\EntityManager
	 * @param \Librette\Doctrine\EntityWrapper
	 * @param ValidatorInterface
	 */
	public function __construct($entity, $offset = NULL, ORM\EntityManager $entityManager, EntityWrapper $entityWrapper, ValidatorInterface $validator = NULL)
	{
		$this->entity = $entity;
		$this->entityManager = $entityManager;
		$this->entityWrapper = $entityWrapper;
		$this->validator = $validator;
		$this->setOffset($offset);
		$this->handlers[] = new Handlers\ToManyHandler();
		$this->handlers[] = new Handlers\ToOneHandler();
		$this->handlers[] = new Handlers\FieldHandler();
	}


	/**
	 * @param string|array
	 */
	public function setOffset($offset)
	{
		$offset = empty($offset) ? [] : $offset;
		if (!is_array($offset)) {
			$offset = explode('.', (string) $offset);
		}
		$this->offset = $offset;
	}


	/**
	 * @return boolean
	 */
	public function getAutoFlush()
	{
		return $this->autoFlush;
	}


	/**
	 * @param boolean
	 */
	public function setAutoFlush($autoFlush)
	{
		$this->autoFlush = $autoFlush;
	}


	/**
	 * @param IHandler
	 */
	public function addHandler(IHandler $handler)
	{
		array_unshift($this->handlers, $handler);
	}


	/**
	 * @param callable
	 */
	public function addSaveHandler($callable)
	{
		$this->addHandler(new Handlers\CallbackHandler($callable, function () {
			return FALSE;
		}));
	}


	/**
	 * @param callable
	 */
	public function addLoadHandler($callable)
	{
		$this->addHandler(new Handlers\CallbackHandler(function () {
			return FALSE;
		}, $callable));
	}


	/**
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}


	/**
	 * @param Form
	 */
	public function load(Form $form)
	{
		$this->loadValues($this->applyOffset($form->getComponents(), $this->offset), $this->entity);
	}


	/**
	 * @param Form
	 */
	public function save(Form $form)
	{
		$this->execute(function () {
			$this->entityManager->persist($this->entity);
		});
		$this->saveValues($this->applyOffset($form->getComponents(), $this->offset), $this->entity);
		if (!$form->isValid()) {
			return;
		}
		$this->getExecutionStrategy()->confirm();
		if ($this->getAutoFlush()) {
			$this->flush();
		}
	}


	/**
	 * @param
	 * @param object
	 */
	public function loadValues($components, $entity)
	{
		$this->handle($components, $entity, self::OPERATION_LOAD);
	}


	/**
	 * @param mixed $components
	 * @param object $entity
	 */
	public function saveValues($components, $entity)
	{
		$this->handle($components, $entity, self::OPERATION_SAVE);
	}


	/**
	 * @param mixed
	 * @return IComponent[]
	 */
	protected function iterable($value)
	{
		if ($value instanceof Container) {
			return $value->getComponents();
		} elseif (!is_array($value) && !$value instanceof \Traversable) {
			return [$value];
		}

		return $value;
	}


	/**
	 * @param \ArrayAccess
	 * @param array
	 * @return mixed
	 */
	protected function applyOffset($data, array $offset)
	{
		foreach ($offset as $level) {
			$data = $data[$level];
		}

		return $data;
	}


	/**
	 * Persists all entities and executes flush.
	 */
	public function flush()
	{
		$this->onFlush($this->entity);
		$this->entityManager->flush();
		$this->onAfterFlush($this->entity);
	}


	/**
	 * @param IComponent
	 * @return bool
	 */
	protected function shouldSkip($component)
	{
		return $component instanceof Controls\Button;
	}


	/**
	 * @param mixed
	 * @param object
	 * @param string load or save
	 */
	protected function handle($components, $entity, $operation)
	{
		$wrappedEntity = $this->entityWrapper->wrap($entity);
		foreach ($this->iterable($components) as $component) {
			if ($this->shouldSkip($component)) {
				continue;
			}
			$this->executeHandlers($operation, $wrappedEntity, $component);
		}
	}


	/**
	 * @param string load or save
	 * @param WrappedEntity
	 * @param IComponent
	 */
	protected function executeHandlers($operation, WrappedEntity $wrappedEntity, $component)
	{
		foreach ($this->handlers as $handler) {
			try {
				if ($handler->$operation($wrappedEntity, $component, $this)) {
					return;
				}
			} catch (ValidationException $e) {
				return;
			}
		}
	}


	/**
	 * @return ValidatorInterface
	 */
	public function getValidator()
	{
		return $this->validator;
	}


	/**
	 * @param ValidatorInterface
	 */
	public function setValidator(ValidatorInterface $validator)
	{
		$this->validator = $validator;
	}


	/**
	 * @param callable
	 * @param Container|Controls\BaseControl
	 */
	public function validate($callback, $violationsTarget)
	{
		if (!$this->validator) {
			return;
		}
		/** @var ConstraintViolationListInterface $result */
		$result = $callback($this->validator);
		if (count($result)) {
			/** @var ConstraintViolationInterface $violation */
			foreach ($result as $violation) {
				$this->getViolationMapper()->handle($violation, $violationsTarget);
			}
			throw new ValidationException;
		}
	}


	/**
	 * @return IViolationMapper
	 */
	public function getViolationMapper()
	{
		if ($this->violationMapper === NULL) {
			$this->violationMapper = new DefaultViolationMapper();
		}

		return $this->violationMapper;
	}


	/**
	 * @param IViolationMapper $violationMapper
	 */
	public function setViolationMapper(IViolationMapper $violationMapper)
	{
		$this->violationMapper = $violationMapper;
	}


	/**
	 * @return IExecutionStrategy
	 */
	public function getExecutionStrategy()
	{
		if ($this->executionStrategy === NULL) {
			$this->executionStrategy = new PostponedExecution();
		}

		return $this->executionStrategy;
	}


	public function setExecutionStrategy(IExecutionStrategy $executionStrategy)
	{
		$this->executionStrategy = $executionStrategy;
	}


	/**
	 * @param callable
	 */
	public function execute($callback)
	{
		$this->getExecutionStrategy()->execute($callback);
	}

}
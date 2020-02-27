<?php
namespace Librette\Doctrine\Forms\Mapper;

use Doctrine\ORM;
use Librette\Doctrine\EntityWrapper;
use Librette\Doctrine\Forms\Mapper\Handlers\ChainHandler;
use Librette\Doctrine\Forms\ValidationException;
use Librette\Forms\IMapper;
use Librette\Forms\IValidationMapper;
use Nette\ComponentModel\Container;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls;
use Nette\Forms\Form;
use Nette\Forms\ISubmitterControl;
use Nette\SmartObject;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author David Matějka
 */
class Mapper implements IMapper, IValidationMapper
{
	use SmartObject;

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

	/** @var ChainHandler */
	protected $handler;

	/** @var \Librette\Doctrine\EntityWrapper */
	protected $entityWrapper;

	/** @var ValidatorInterface */
	protected $validator;

	/** @var IViolationMapper */
	protected $violationMapper;

	/** @var IExecutionStrategy */
	protected $executionStrategy;

	/** @var bool */
	protected $validating = FALSE;

	/** @var \Nette\Forms\Form */
	protected $form;

	/** @var object[] */
	protected $entities = [];


	/**
	 * @param object
	 * @param array|null|string
	 * @param ORM\EntityManager
	 * @param \Librette\Doctrine\EntityWrapper
	 * @param IHandler
	 * @param ValidatorInterface
	 */
	public function __construct($entity, $offset = NULL, ORM\EntityManager $entityManager, EntityWrapper $entityWrapper, IHandler $handler = NULL, ValidatorInterface $validator = NULL)
	{
		$this->entity = $entity;
		$this->entityManager = $entityManager;
		$this->entityWrapper = $entityWrapper;
		$this->validator = $validator;
		$this->setOffset($offset);
		if ($handler === NULL) { //BC
			$handlers = [
				new Handlers\ToManyHandler(),
				new Handlers\ToOneHandler(),
				new Handlers\FieldHandler(),
			];
		} else {
			$handlers = [$handler];
		}
		$this->handler = new ChainHandler($handlers);

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
		$this->handler->add($handler);
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
		$this->form = $form;
		$this->loadValues($this->applyOffset($form->getComponents(), $this->offset), $this->entity);
	}


	/**
	 * @param Form
	 */
	public function validate(Form $form)
	{
		if (!$this->validator) {
			return;
		}
		$this->form = $form;
		$originalExecutionStrategy = $this->executionStrategy;
		$recover = function () use ($originalExecutionStrategy) {
			$this->executionStrategy = $originalExecutionStrategy;
			$this->validating = FALSE;
		};
		$this->executionStrategy = new PostponedExecution();
		$this->validating = TRUE;
		try {
			$this->saveValues($this->applyOffset($form->getComponents(), $this->offset), $this->entity);
			$recover();
		} catch (\Exception $e) {
			//todo: better exception handling
			$recover();
		}
	}


	/**
	 * @return bool
	 */
	public function isValidating()
	{
		return $this->validating;
	}


	/**
	 * @param Form
	 */
	public function save(Form $form)
	{
		$this->form = $form;
		$this->entities = [];
		$this->execute(function () {
			$this->entityManager->persist($this->entity);
		});
		$this->saveValues($this->applyOffset($form->getComponents(), $this->offset), $this->entity);
		if (!$form->isValid()) {
			return;
		}
		$this->getExecutionStrategy()->confirm();
		$valid = TRUE;
		foreach ($this->entities as $entity) {
			try {
				$this->runValidation(function (ValidatorInterface $validator) use ($entity) {
					return $validator->validate($entity);
				}, $form);
			} catch (ValidationException $e) {
				$valid = FALSE;
			}
		}
		if ($valid && $this->getAutoFlush()) {
			$this->flush();
		}
	}


	/**
	 * @param mixed
	 * @param object
	 * @internal call only from the IHandler
	 */
	public function loadValues($components, $entity)
	{
		$this->handle($components, $entity, self::OPERATION_LOAD);
	}


	/**
	 * @param mixed
	 * @param object
	 * @internal call only from the IHandler
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
		if ($component instanceof Controls\Button) {
			return TRUE;
		}
		if ($this->isValidating() && $this->form->isAnchored()
			&& ($submittedBy = $this->form->isSubmitted()) && $submittedBy instanceof ISubmitterControl) {
			$controls = $submittedBy->getValidationScope();
			if ($controls === NULL) {
				return FALSE;
			}
			foreach ($controls as $control) {
				if ($control === $component) {
					return FALSE;
				}
				if ($control instanceof Container && array_search($component, $control->getComponents(TRUE), TRUE)) {
					return FALSE;
				}
			}

			return TRUE;
		}

		return FALSE;
	}


	/**
	 * @param mixed
	 * @param object
	 * @param string load or save
	 */
	protected function handle($components, $entity, $operation)
	{
		$this->entities[] = $entity;
		$wrappedEntity = $this->entityWrapper->wrap($entity);
		foreach ($this->iterable($components) as $component) {
			if ($this->shouldSkip($component)) {
				continue;
			}
			$this->handler->$operation($wrappedEntity, $component, $this);
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
	public function runValidation($callback, $violationsTarget)
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

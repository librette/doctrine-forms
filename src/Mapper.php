<?php
namespace Librette\Doctrine\Forms;

use Doctrine\ORM;
use Librette\Doctrine\EntityWrapper;
use Librette\Doctrine\Forms\Handlers;
use Librette\Forms\IMapper;
use Nette\ComponentModel\Container;
use Nette\Forms\Controls;
use Nette\Forms\Form;
use Nette\Object;

/**
 * @author David Matějka
 */
class Mapper extends Object implements IMapper
{

	const FLUSH = TRUE;

	const NO_FLUSH = FALSE;

	const OPERATION_LOAD = 'load';

	const OPERATION_SAVE = 'save';

	public $onFlush = array();

	public $onAfterFlush = array();

	/** @var object */
	protected $entity;

	/** @var boolean */
	protected $autoFlush = TRUE;

	/** @var array */
	protected $offset;

	/** @var ORM\EntityManager */
	protected $entityManager;

	/** @var IHandler[] */
	protected $handlers = array();

	/** @var object[] */
	protected $persistQueue;

	/** @var \Librette\Doctrine\EntityWrapper */
	protected $entityWrapper;


	/**
	 *
	 * @param object $entity
	 * @param array|null|string $offset
	 * @param ORM\EntityManager $entityManager
	 * @param \Librette\Doctrine\EntityWrapper $entityWrapper
	 */
	public function __construct($entity, $offset = NULL, ORM\EntityManager $entityManager, EntityWrapper $entityWrapper)
	{
		$this->entity = $entity;
		$this->entityManager = $entityManager;
		$this->entityWrapper = $entityWrapper;
		$this->setOffset($offset);
		$this->handlers[] = new Handlers\ToManyHandler();
		$this->handlers[] = new Handlers\ToOneHandler();
		$this->handlers[] = new Handlers\FieldHandler();
	}


	public function setOffset($offset)
	{
		$offset = empty($offset)? array() : $offset;
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
	 * @param boolean $autoFlush
	 */
	public function setAutoFlush($autoFlush)
	{
		$this->autoFlush = $autoFlush;
	}


	public function addHandler(IHandler $handler)
	{
		array_unshift($this->handlers, $handler);
	}


	public function addSaveHandler($callable)
	{
		$this->addHandler(new Handlers\CallbackHandler($callable, function () {
			return FALSE;
		}));
	}


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
	 * @param Form $form
	 */
	public function load(Form $form)
	{
		$this->loadValues($this->applyOffset($form->getComponents(), $this->offset), $this->entity);
	}


	/**
	 * @param Form $form
	 */
	public function save(Form $form)
	{
		$this->saveValues($this->applyOffset($form->getComponents(), $this->offset), $this->entity);
		if ($this->getAutoFlush()) {
			$this->flush();
		}
	}


	/**
	 * @param mixed $components
	 * @param $entity
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
		$this->queueEntity($entity);
		$this->handle($components, $entity, self::OPERATION_SAVE);
	}


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
	 * @param $data
	 * @param array $offset
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
	 * Adds given entity to queue - it will be persisted together with other entities
	 *
	 * @param object $entity
	 */
	public function queueEntity($entity)
	{
		$this->persistQueue[] = $entity;
	}


	/**
	 * Persists all entities and executes flush.
	 */
	public function flush()
	{
		$this->persist();
		$this->onFlush($this->entity);
		$this->entityManager->flush();
		$this->onAfterFlush($this->entity);
	}


	/**
	 * Do not call directly unless you really know what are you doing
	 *
	 * @internal
	 */
	public function persist()
	{
		foreach ($this->persistQueue as $key => $entity) {
			$this->entityManager->persist($entity);
			unset($this->persistQueue[$key]);
		}
	}


	/**
	 * @param $component
	 * @return bool
	 */
	protected function shouldSkip($component)
	{
		return $component instanceof Controls\Button;
	}


	/**
	 * @param $components
	 * @param $entity
	 * @param string $operation load or save
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
	 * @param $operation
	 * @param $wrappedEntity
	 * @param $component
	 */
	protected function executeHandlers($operation, $wrappedEntity, $component)
	{
		foreach ($this->handlers as $handler) {
			if ($handler->$operation($wrappedEntity, $component, $this)) {
				return;
			}
		}
	}

}

<?php
namespace Librette\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Librette\Doctrine\Forms\Builder\Handlers\ChainHandler;
use Librette\Doctrine\Forms\InvalidArgumentException;
use Nette\ComponentModel\Container as CMContainer;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;

/**
 * @author David Matejka
 */
class ContainerBuilder extends CMContainer implements IBuilder, \ArrayAccess
{

	use TBaseBuilder;

	/** @var \Doctrine\ORM\Mapping\ClassMetadata */
	protected $metadata;

	/** @var Configuration */
	protected $configuration;

	/** @var IBuilder[] */
	protected $builders = [];

	/** @var Container */
	protected $container;

	/** @var array of function(FormBuilder) */
	public $onAttached = [];


	public function __construct(ClassMetadata $metadata, Container $container, Configuration $configuration)
	{
		$this->container = $container;
		$this->metadata = $metadata;
		$handler = new ChainHandler([$configuration->getHandler()]);
		$this->configuration = new Configuration($handler, $configuration->getLabelingStrategy());
	}


	/**
	 * @return Container
	 */
	public function getFormComponent()
	{
		return $this->container;
	}


	protected function attached($obj)
	{
		parent::attached($obj);
		if ($obj instanceof FormBuilder) {
			$this->onAttached($obj);
		}
	}


	/**
	 * @param string
	 * @param array you can omit this parameter
	 * @param IHandler
	 * @return IBuilder
	 */
	public function add($name, $options = [])
	{
		$builder = $this->createBuilder($name, $options);
		$this[$name] = $builder;

		return $builder;
	}


	/**
	 * @param string
	 */
	public function addSubmit($name)
	{
		return $this->container[$name] = new SubmitButton($this->configuration->getLabelingStrategy()->getButtonLabel($name));
	}


	/**
	 * @param array of pairs [name => options, ...] or list [name, name] or mix of both
	 * @return IBuilder[]
	 */
	public function addList($fields)
	{
		$builders = [];
		foreach ($fields as $name => $options) {
			if (is_string($options)) {
				$name = $options;
				$options = [];
			}
			$builders[$name] = $this->add($name, $options);
		}

		return $builders;
	}


	/**
	 * @param string[]|array list of field and association names
	 * @param array of pairs [name=>options]. Optional options for non-excluded field/associations
	 * @return IBuilder[]
	 */
	public function addExcept($excluded, $options = [])
	{
		$names = array_merge($this->metadata->getFieldNames(), $this->metadata->getAssociationNames());
		$names = array_diff($names, $excluded);
		$names = array_filter($names, function ($name) {
			return !$this->shouldSkip($name);
		});
		$builders = [];
		foreach ($names as $name) {
			$builders[$name] = $this->add($name, isset($options[$name]) ? $options[$name] : []);
		}

		return $builders;
	}


	/**
	 * @param array of pairs [name=>options]. Optional options for non-excluded field/associations
	 * @return IBuilder[]
	 */
	public function addAll($options = [])
	{
		return $this->addExcept([], $options);
	}


	protected function shouldSkip($name)
	{
		return FALSE;
	}


	/**
	 * @return Configuration
	 */
	public function getConfiguration()
	{
		return $this->configuration;
	}


	/**
	 * @return ClassMetadata
	 */
	public function getMetadata()
	{
		return $this->metadata;
	}


	/**
	 * @param string
	 * @param array
	 * @return IBuilder
	 * @throws \Exception
	 */
	protected function createBuilder($name, $options = [])
	{
		$builder = $this->configuration->getHandler()->handle($name, $options, $this->metadata, $this->configuration);
		if ($builder === NULL) {
			throw new \RuntimeException("No satisfying handler found."); //todo better exception
		}

		return $builder;
	}


	public function addComponent(IComponent $component, $name, $insertBefore = NULL)
	{
		if (!$component instanceof IBuilder) {
			throw new InvalidArgumentException("IBuilder expected, instance of " . get_class($component) . ' given.');
		}
		$this->container[$name] = $component->getFormComponent();

		return parent::addComponent($component, $name, $insertBefore);
	}


	public function offsetSet($name, $component)
	{
		$this->addComponent($component, $name);
	}


	public function offsetGet($name)
	{
		return $this->getComponent($name, TRUE);
	}


	public function offsetExists($name)
	{
		return $this->getComponent($name, FALSE) !== NULL;
	}


	public function offsetUnset($name)
	{
		$component = $this->getComponent($name, FALSE);
		if ($component !== NULL) {
			$this->removeComponent($component);
		}
	}
}

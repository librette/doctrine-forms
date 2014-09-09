<?php
namespace Librette\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Librette\Doctrine\Forms\Builder\Handlers\ChainHandler;
use Librette\Doctrine\Forms\InvalidArgumentException;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;

/**
 * @author David Matejka
 * @method Container getComponent()
 */
class ContainerBuilder extends BaseBuilder implements \ArrayAccess
{

	/** @var \Doctrine\ORM\Mapping\ClassMetadata */
	protected $metadata;

	/** @var Configuration */
	protected $configuration;

	/** @var IBuilder[] */
	protected $builders = [];


	public function __construct(ClassMetadata $metadata, Container $container, Configuration $configuration)
	{
		parent::__construct($container);
		$this->metadata = $metadata;
		$handler = new ChainHandler([$configuration->getHandler()]);
		$this->configuration = new Configuration($handler, $configuration->getLabelingStrategy());
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
		return $this->component[$name] = new SubmitButton($this->configuration->getLabelingStrategy()->getButtonLabel($name));
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


	public function offsetExists($name)
	{
		return isset($this->builders[$name]);
	}


	public function offsetGet($name)
	{
		return $this->builders[$name];
	}


	public function offsetSet($name, $builder)
	{
		if (!$builder instanceof IBuilder) {
			throw new InvalidArgumentException("Value must be an instance of Librette\\Doctrine\\Forms\\IBuilder");
		}
		$builder->attach($this, $name);
		$this->component[$name] = $builder->getComponent();
		$this->builders[$name] = $builder;
	}


	public function offsetUnset($name)
	{
		if ($this->offsetExists($name)) {
			$this->getComponent()->removeComponent($this->builders[$name]->getComponent());
			unset($this->builders[$name]);
		}
	}

}

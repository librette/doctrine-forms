<?php
namespace Librette\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Replicator\Container as Replicator;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;

/**
 * @author David Matejka
 * @method Replicator getComponent()
 */
class ReplicatorBuilder extends ContainerBuilder
{

	/** @var Container */
	protected $containerPrototype;


	public function __construct(ClassMetadata $metadata, Replicator $replicator, Configuration $configuration, Container $containerPrototype)
	{
		parent::__construct($metadata, $replicator, $configuration);
		$this->containerPrototype = $containerPrototype;
	}


	public function add($name, $options = [])
	{
		$builder = $this->createBuilder($name, $options);
		$this->containerPrototype[$name] = $builder->getComponent();

		return $builder;
	}


	public function addAddButton(array $options = [])
	{
		$options += ['name'       => 'add',
					 'caption'    => 'Add',
					 'allowEmpty' => NULL,
					 'callback'   => NULL];
		$button = $this->addButton(function () use ($options) {
			return $this->getComponent()
						->addSubmit($options['name'], $options['caption'])
						->addCreateOnClick($options['allowEmpty'], $options['callback']);

		});

		return new ControlBuilder($button);
	}


	public function addRemoveButton(array $options = [])
	{
		$options += ['name'     => 'remove',
					 'caption'  => 'Remove',
					 'callback' => NULL,
		];
		$button = $this->addButton(function () use ($options) {
			return $this->getContainerPrototype()
						->addSubmit($options['name'], $options['caption'])
						->addRemoveOnClick($options['callback']);
		});

		return new ControlBuilder($button);
	}


	/**
	 * @return Container
	 */
	public function getContainerPrototype()
	{
		return $this->containerPrototype;
	}


	protected function shouldSkip($name)
	{
		if (parent::shouldSkip($name)) {
			return TRUE;
		}

		if (!$this->metadata->hasAssociation($name)) {
			return FALSE;
		}
		$metadata = $this->metadata->getAssociationMapping($name);
		if ($metadata['type'] === ClassMetadata::MANY_TO_ONE
			&& $this->parent && $this->parent instanceof ContainerBuilder
			&& $this->parent->getMetadata()->name === $metadata['targetEntity'] //this check is maybe not necessary
			&& !empty($metadata['inversedBy']) && $metadata['inversedBy'] === $this->name
		) {
			return TRUE; //back referencing
		}

		return FALSE;
	}


	private function registerReplicator()
	{
		if (!SubmitButton::extensionMethod('addCreateOnClick')) {
			Replicator::register();
		}
	}


	private function addButton($callback)
	{
		$this->registerReplicator();
		$this->getComponent()->addComponent($this->containerPrototype, '__prototype'); //inner container has to be attached to the replicator
		$result = $callback();
		$this->getComponent()->removeComponent($this->containerPrototype);

		return $result;
	}
}

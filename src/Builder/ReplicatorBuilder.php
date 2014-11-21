<?php
namespace Librette\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Replicator\Container as Replicator;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;

/**
 * @author David Matejka
 * @method Replicator getFormComponent()
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
		$this->containerPrototype[$name] = $builder->getFormComponent();

		//todo add builder to $this->builders?
		return $builder;
	}


	public function addIdentifiers()
	{
		$components = [];
		foreach ($this->metadata->getIdentifierFieldNames() as $name) {
			if (!isset($this->containerPrototype[$name])) {
				$components[$name] = $this->containerPrototype->addHidden($name);
			}
		}

		return $components;
	}


	public function addAddButton(array $options = [])
	{
		$options += ['name'       => 'add',
		             'caption'    => $this->configuration->getLabelingStrategy()->getButtonLabel('add'),
		             'allowEmpty' => FALSE,
		             'callback'   => function () {
		             },
		             'multiplier' => FALSE,
		];
		if ($options['multiplier'] === TRUE) {
			$options['callback'] = $this->addMultiplier($options['callback']);
		}

		$button = $this->addButton(function () use ($options) {
			return $this->getFormComponent()
			            ->addSubmit($options['name'], $options['caption'])
			            ->setValidationScope(FALSE)
			            ->addCreateOnClick($options['allowEmpty'], $options['callback']);

		});

		//todo add builder to $this->builders?
		return new ControlBuilder($button);
	}


	private function addMultiplier($callback)
	{
		$this->getFormComponent()
		     ->addText('count')
		     ->setDefaultValue(1);

		return function (Replicator $replicator, Container $container) use ($callback) {
			$callback($replicator, $container);
			for ($i = 1; $i < $replicator['count']->value; $i++) {
				$container = $replicator->createOne();
				$callback($replicator, $container);
			}
		};
	}


	public function addRemoveButton(array $options = [])
	{
		$options += ['name'     => 'remove',
		             'caption'  => $this->configuration->getLabelingStrategy()->getButtonLabel('remove'),
		             'callback' => NULL,
		];
		$button = $this->addButton(function () use ($options) {
			return $this->getContainerPrototype()
			            ->addSubmit($options['name'], $options['caption'])
			            ->setValidationScope(FALSE)
			            ->addRemoveOnClick($options['callback']);
		});

		//todo add builder to $this->builders?
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
		$this->getFormComponent()->addComponent($this->containerPrototype, '__prototype'); //inner container has to be attached to the replicator
		$result = $callback();
		$this->getFormComponent()->removeComponent($this->containerPrototype);

		return $result;
	}
}

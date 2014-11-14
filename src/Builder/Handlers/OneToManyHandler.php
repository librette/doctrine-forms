<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Replicator\Container as Replicator;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\IHandler;
use Librette\Doctrine\Forms\Builder\ReplicatorBuilder;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Object;

/**
 * @author David Matejka
 */
class OneToManyHandler extends Object implements IHandler
{

	/** @var EntityManager */
	protected $entityManager;


	/**
	 * @param EntityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	public function handle($name, array $options, ClassMetadata $classMetadata, Configuration $configuration)
	{
		if (!$mapping = MetadataHelpers::getAssociationMapping($classMetadata, $name, ClassMetadata::ONE_TO_MANY)) {
			return NULL;
		}
		$options += ['createDefault' => 0];
		$dao = $this->entityManager->getDao($mapping['targetEntity']);
		$containerPrototype = new Container();
		$replicator = new Replicator(function (Container $container) use ($containerPrototype) {
			$clone = function (Container $targetContainer, Container $sourceContainer) use (&$clone) {
				/** @var IComponent $component */
				foreach ($sourceContainer->getComponents() as $component) {
					if ($component instanceof Container) {
						/** @var Container $component */
						$container = new Container();
						$container->setCurrentGroup($component->getCurrentGroup());
						$container->onValidate = $component->onValidate;
						$targetContainer[$component->getName()] = $container;
						$clone($container, $component);
					} else {
						$targetContainer[$component->getName()] = clone $component;
					}
				}
			};
			$clone($container, $containerPrototype);
		}, $options['createDefault']);

		return new ReplicatorBuilder($dao->getClassMetadata(), $replicator, $configuration, $containerPrototype);
	}

}

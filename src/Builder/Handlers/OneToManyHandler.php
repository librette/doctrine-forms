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
		$dao = $this->entityManager->getDao($mapping['targetEntity']);
		$containerPrototype = new Container();
		$replicator = new Replicator(function (Container $container) use ($containerPrototype) {
			/** @var IComponent $component */
			foreach ($containerPrototype->getComponents() as $component) {
				$container[$component->getName()] = clone $component;
			}
		});

		return new ReplicatorBuilder($dao->getClassMetadata(), $replicator, $configuration, $containerPrototype);
	}

}

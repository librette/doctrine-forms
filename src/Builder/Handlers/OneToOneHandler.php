<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\ContainerBuilder;
use Librette\Doctrine\Forms\Builder\IHandler;
use Nette\Forms\Container;
use Nette\SmartObject;

/**
 * @author David Matejka
 */
class OneToOneHandler implements IHandler
{
	use SmartObject;

	/** @var EntityManager */
	protected $entityManager;


	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	public function handle($name, array $options, ClassMetadata $classMetadata, Configuration $configuration)
	{
		if (!empty($options['control'])) {
			return NULL;
		}
		if (!$mapping = MetadataHelpers::getAssociationMapping($classMetadata, $name, ClassMetadata::ONE_TO_ONE)) {
			return NULL;
		}
		$targetMetadata = $this->entityManager->getDao($mapping['targetEntity'])->getClassMetadata();

		return new ContainerBuilder($targetMetadata, new Container(), $configuration);
	}

}

<?php
namespace Librette\Doctrine\Forms\Builder;

use Kdyby\Doctrine\EntityManager;
use Librette\Doctrine\Forms\MapperFactory;
use Librette\Forms\IFormFactory;
use Nette\Object;

/**
 * @author David Matejka
 */
class FormBuilderFactory extends Object
{

	/** @var EntityManager */
	protected $entityManager;

	/** @var IFormFactory */
	protected $formFactory;

	/** @var Configuration */
	protected $configuration;

	/** @var MapperFactory */
	protected $mapperFactory;


	/**
	 * @param EntityManager
	 * @param IFormFactory
	 * @param Configuration
	 */
	public function __construct(EntityManager $entityManager, IFormFactory $formFactory, Configuration $configuration, MapperFactory $mapperFactory)
	{
		$this->entityManager = $entityManager;
		$this->formFactory = $formFactory;
		$this->configuration = $configuration;
		$this->mapperFactory = $mapperFactory;
	}


	/**
	 * @param object|string
	 */
	public function create($entity)
	{
		$className = is_string($entity) ? $entity : get_class($entity);

		return new FormBuilder($this->entityManager->getClassMetadata($className), $this->formFactory->create(), $this->configuration);
	}
}

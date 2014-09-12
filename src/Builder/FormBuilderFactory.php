<?php
namespace Librette\Doctrine\Forms\Builder;

use Kdyby\Doctrine\EntityManager;
use Librette\Doctrine\Forms\InvalidArgumentException;
use Librette\Doctrine\Forms\InvalidStateException;
use Librette\Doctrine\Forms\MapperFactory;
use Librette\Doctrine\Forms\UnexpectedValueException;
use Librette\Forms\IFormFactory;
use Librette\Forms\IFormWithMapper;
use Nette\Object;

/**
 * @author David Matejka
 */
class FormBuilderFactory extends Object
{

	const AUTO = NULL;

	/** @var EntityManager */
	protected $entityManager;

	/** @var Configuration */
	protected $configuration;

	/** @var MapperFactory */
	protected $mapperFactory;


	/**
	 * @param EntityManager
	 * @param Configuration
	 * @param MapperFactory
	 */
	public function __construct(EntityManager $entityManager, Configuration $configuration, MapperFactory $mapperFactory = NULL)
	{
		$this->entityManager = $entityManager;
		$this->configuration = $configuration;
		$this->mapperFactory = $mapperFactory;
	}


	/**
	 * @param object|string
	 * @param bool|null
	 */
	public function create($entity, $createMapper = self::AUTO)
	{
		$className = is_string($entity) ? $entity : get_class($entity);
		$classMetadata = $this->entityManager->getClassMetadata($className);
		/** @var FormBuilder $builder */
		$builder = $this->configuration->getHandler()->handle(NULL, [], $classMetadata, $this->configuration);
		if(!$builder instanceof FormBuilder) {
			throw new UnexpectedValueException("Builder created by root handler must be an instance of FormBuilder");
		}
		if ($createMapper === TRUE || ($createMapper === self::AUTO && is_object($entity))) {
			if (!is_object($entity)) {
				throw new InvalidArgumentException("If you want to create mapper, you have to pass an entity.");
			}
			if (!$this->mapperFactory) {
				throw new InvalidStateException("MapperFactory has not been injected.");
			}
			$form = $builder->getForm();
			if (!$form instanceof IFormWithMapper) {
				throw new InvalidStateException("Form does not implement \\Librette\\Forms\\IFormWithMapper");
			}
			$form->setMapper($this->mapperFactory->create($entity));
		}

		return $builder;
	}
}

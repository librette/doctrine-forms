<?php
namespace Librette\Doctrine\Forms\Builder;

use Kdyby\Doctrine\EntityManager;
use Librette\Doctrine\Forms\InvalidArgumentException;
use Librette\Doctrine\Forms\InvalidStateException;
use Librette\Doctrine\Forms\MapperFactory;
use Librette\Forms\IFormFactory;
use Librette\Forms\IFormWithMapper;
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
	public function create($entity, $createMapper = TRUE)
	{
		$className = is_string($entity) ? $entity : get_class($entity);

		$builder = new FormBuilder($this->entityManager->getClassMetadata($className), $this->formFactory->create(), $this->configuration);
		if ($createMapper) {
			if (!is_object($entity)) {
				throw new InvalidArgumentException("If you want to create mapper, you have to pass an entity.");
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
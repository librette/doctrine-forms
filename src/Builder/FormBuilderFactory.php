<?php
namespace Librette\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
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

	const AUTO = NULL;

	/** @var ClassMetadataFactory */
	protected $classMetadataFactory;

	/** @var IFormFactory */
	protected $formFactory;

	/** @var Configuration */
	protected $configuration;

	/** @var MapperFactory */
	protected $mapperFactory;


	/**
	 * @param ClassMetadataFactory
	 * @param IFormFactory
	 * @param Configuration
	 * @param MapperFactory
	 */
	public function __construct(ClassMetadataFactory $classMetadataFactory, IFormFactory $formFactory, Configuration $configuration, MapperFactory $mapperFactory = NULL)
	{
		$this->classMetadataFactory = $classMetadataFactory;
		$this->formFactory = $formFactory;
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

		$builder = new FormBuilder($this->classMetadataFactory->getMetadataFor($className), $this->formFactory->create(), $this->configuration);
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

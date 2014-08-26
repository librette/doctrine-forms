<?php
namespace Librette\Doctrine\Forms\Handlers;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Librette\Doctrine\Forms\Mapper;
use Librette\Doctrine\Forms\UnexpectedValueException;
use Librette\Doctrine\WrappedEntity;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\MultiChoiceControl;
use Nette\Object;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * @author David Matejka
 *
 * @internal
 */
class ToManySaveHelper extends Object
{

	/** @var \Librette\Doctrine\WrappedEntity */
	protected $wrappedEntity;

	/** @var \Librette\Doctrine\Forms\Mapper */
	protected $mapper;

	/** @var Container|MultiChoiceControl */
	protected $component;

	/** @var EntityManager */
	protected $entityManager;

	/** @var array */
	protected $byPrimary = [];

	/** @var array */
	protected $forRemoval = [];

	/** @var array */
	protected $forAdd = [];

	/** @var Collection */
	protected $collection;

	/** @var array */
	private $identifierFields;

	protected $type;


	public function __construct(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		$this->wrappedEntity = $wrappedEntity;
		$this->component = $component;
		$this->mapper = $mapper;
		$this->entityManager = $wrappedEntity->getEntityManager();
	}


	public function process()
	{
		$data = $this->component instanceof MultiChoiceControl ? $this->component->getValue() : $this->component->getValues(TRUE);
		$associationMapping = $this->wrappedEntity->getMetadata()->getAssociationMapping($this->component->name);
		$this->type = $associationMapping['type'];
		$targetEntityMetadata = $this->entityManager->getClassMetadata($associationMapping['targetEntity']);
		$this->processIdentifier($targetEntityMetadata);
		$this->prepareCollection();
		$targetEntityRepository = $this->entityManager->getRepository($associationMapping['targetEntity']);
		foreach ($data as $rowKey => $row) {
			$newEntity = NULL;
			if (is_array($row)) {
				$values = $this->getIdentifierValues($row);
				if ($values
					&& !($newEntity = $this->popByIdentifier($values))
					&& $this->type == ClassMetadata::MANY_TO_MANY
				) {
					$newEntity = $targetEntityRepository->findOneBy($values);
				}
				if (!$newEntity) {
					$newEntity = $targetEntityMetadata->newInstance();
				}
				$this->mapper->saveValues($this->component[$rowKey], $newEntity);
			} elseif ($row) {
				$currentValue = $this->component instanceof MultiChoiceControl ? $row : $rowKey;
				if (!$this->popByIdentifier([$currentValue])) {
					$newEntity = $targetEntityRepository->find($currentValue);
				}
			}
			if ($newEntity) {
				$this->addEntity($newEntity);
			}
		}
		foreach ($this->forRemoval as $entityForRemove) {
			$this->collection->removeElement($entityForRemove);
		}
		$this->mapper->execute(function () {
			$this->updateCollection();
		});
		$this->validate();
	}


	/**
	 * @param array
	 * @param $row
	 * @return array
	 */
	protected function getIdentifierValues($row)
	{
		$values = [];
		foreach ($this->identifierFields as $field) {
			if (empty($row[$field])) {
				return NULL;
			}
			$values[$field] = $row[$field];
		}
		ksort($values);

		return $values;
	}


	/**
	 * @param array
	 * @return string
	 */
	protected function createKeyHash($values)
	{
		return md5(implode(' ', $values));
	}


	protected function groupByPrimaryHash()
	{
		$entityWrapper = $this->wrappedEntity->getEntityWrapper();
		$byPrimary = [];
		foreach ($this->wrappedEntity->getValue($this->component->name) as $subEntity) {
			$wrappedSubEntity = $entityWrapper->wrap($subEntity);
			$identifierValue = $wrappedSubEntity->getFlattenIdentifier();
			$keyHash = $this->createKeyHash($this->getIdentifierValues($identifierValue));
			$byPrimary[$keyHash] = $subEntity;
		}

		return $byPrimary;
	}


	/**
	 * @param ClassMetadata $targetEntityMetadata
	 * @return array
	 */
	protected function processIdentifier(ClassMetadata $targetEntityMetadata)
	{
		$identifierFields = $targetEntityMetadata->identifier;
		$this->identifierFields = array_filter($identifierFields, function ($value) use ($targetEntityMetadata) {
			if (isset($targetEntityMetadata->associationMappings[$value])) {
				$associationMapping = $targetEntityMetadata->getAssociationMapping($value);

				return $this->wrappedEntity->getMetadata()->getName() != $associationMapping['targetEntity'];
			}

			return TRUE;
		});

		return $identifierFields;
	}


	protected function updateCollection()
	{
		foreach ($this->forAdd as $newEntity) {
			$this->wrappedEntity->addToCollection($this->component->name, $newEntity);
			$this->wrappedEntity->getEntityManager()->persist($newEntity);
		}
		foreach ($this->forRemoval as $entityForRemove) {
			$this->wrappedEntity->removeFromCollection($this->component->name, $entityForRemove);
			if ($this->type == ClassMetadata::ONE_TO_MANY && $this->wrappedEntity->getEntityManager()->contains($entityForRemove)) {
				$this->wrappedEntity->getEntityManager()->remove($entityForRemove);
			}
		}
	}


	protected function validate()
	{
		$this->mapper->runValidation(function (ValidatorInterface $validatorInterface) {
			return $validatorInterface->validatePropertyValue($this->wrappedEntity->getEntity(), $this->component->name, $this->collection);
		}, $this->component instanceof BaseControl ? $this->component : $this->component->getForm());
	}


	private function prepareCollection()
	{
		$this->forRemoval = $this->byPrimary = $this->groupByPrimaryHash();
		$originalCollection = $this->wrappedEntity->getRawValue($this->component->name);
		if (!$originalCollection instanceof Collection) {
			throw new UnexpectedValueException("Instance of \\Doctrine\\Common\\Collections\\Collection expected, "
			. is_object($originalCollection) ? get_class($originalCollection) : gettype($originalCollection) . ' given');
		}
		$this->collection = new ArrayCollection($originalCollection->toArray());
	}


	protected function popByIdentifier($identifier)
	{
		$keyHash = $this->createKeyHash($identifier);
		if (isset($this->byPrimary[$keyHash])) {
			unset($this->forRemoval[$keyHash]);

			return $this->byPrimary[$keyHash];
		}

		return FALSE;
	}


	protected function addEntity($entity)
	{
		if (!$this->collection->contains($entity)) {
			$this->collection->add($entity);
			$this->forAdd[] = $entity;
		}
	}
}
<?php
namespace Librette\Doctrine\Forms\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Librette\Doctrine\EntityWrapper;
use Librette\Doctrine\Forms\IHandler;
use Librette\Doctrine\Forms\Mapper;
use Librette\Doctrine\WrappedEntity;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\MultiChoiceControl;

/**
 * @author David Matejka
 */
class ToManyHandler implements IHandler
{


	public function load(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		if (!$this->canHandle($wrappedEntity, $component)) {
			return FALSE;
		}

		$subEntities = $wrappedEntity->getValue($component->name);
		if ($subEntities === NULL) {
			return TRUE;
		}


		$identifiers = array();
		foreach ($subEntities as $subEntity) {
			$wrappedSubEntity = $wrappedEntity->getEntityWrapper()->wrap($subEntity);

			$identifier = NULL;
			if ($wrappedSubEntity->hasValidIdentifier()) {
				$identifiers[] = $identifier = $wrappedSubEntity->getSingleIdentifier();
			}
			$this->loadSubEntity($component, $mapper, $wrappedSubEntity, $identifier);
		}

		if ($component instanceof MultiChoiceControl) {
			$component->setValue($identifiers);
		}


		return TRUE;
	}


	public function save(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		if (!$this->canHandle($wrappedEntity, $component)) {
			return FALSE;
		}
		/** @var Container|MultiChoiceControl $component */

		$data = $component instanceof MultiChoiceControl ? $component->getValue() : $component->getValues(TRUE);
		$associationMapping = $wrappedEntity->getMetadata()->getAssociationMapping($component->name);
		$entityManager = $wrappedEntity->getEntityManager();
		$targetEntityMetadata = $entityManager->getClassMetadata($associationMapping['targetEntity']);
		$identifierFields = $this->getIdentifierFields($targetEntityMetadata, $wrappedEntity);

		$forRemove = $byPrimary = $this->groupByPrimaryHash(
			$wrappedEntity->getValue($component->name),
			$wrappedEntity->getEntityWrapper(),
			$identifierFields);

		$targetEntityRepository = $entityManager->getRepository($associationMapping['targetEntity']);
		foreach ($data as $rowKey => $row) {
			$newEntity = NULL;
			if (is_array($row)) {
				$values = $this->getIdentifierValues($identifierFields, $row);
				if ($values) {
					$keyHash = $this->createKeyHash($values);
					if (isset($byPrimary[$keyHash])) {
						$newEntity = $byPrimary[$keyHash];
						unset($forRemove[$keyHash]);
					} elseif ($associationMapping['type'] == ClassMetadata::MANY_TO_MANY) {
						$newEntity = $targetEntityRepository->findOneBy($values);
					}
				}

				if (!$newEntity) {
					$newEntity = $targetEntityMetadata->newInstance();
				}
				$mapper->saveValues($component[$rowKey], $newEntity);
			} elseif ($row) {
				$currentValue = $component instanceof MultiChoiceControl ? $row : $rowKey;
				$keyHash = $this->createKeyHash(array($currentValue));
				if (isset($byPrimary[$keyHash])) {
					$newEntity = $byPrimary[$keyHash];
					unset($forRemove[$keyHash]);
				} else {
					$newEntity = $targetEntityRepository->find($currentValue);
				}
			}
			if ($newEntity) {
				$wrappedEntity->addToCollection($component->name, $newEntity);
			}
		}

		foreach ($forRemove as $entityForRemove) {
			if ($associationMapping['type'] == ClassMetadata::ONE_TO_MANY && $entityManager->contains($entityForRemove)) {
				$entityManager->remove($entityForRemove);
			}
			$wrappedEntity->removeFromCollection($component->name, $entityForRemove);
		}

		return TRUE;
	}


	/**
	 * @param $identifierFields
	 * @param $row
	 * @return array
	 */
	protected function getIdentifierValues($identifierFields, $row)
	{
		$values = array();
		foreach ($identifierFields as $field) {
			if (empty($row[$field])) {
				return NULL;
			}
			$values[$field] = $row[$field];
		}
		ksort($values);

		return $values;
	}


	/**
	 * @param $values
	 * @return string
	 */
	protected function createKeyHash($values)
	{
		$key = md5(implode(' ', $values));

		return $key;
	}


	/**
	 * @param WrappedEntity $wrappedEntity
	 * @param Component $component
	 */
	protected function canHandle(WrappedEntity $wrappedEntity, Component $component)
	{
		if (!$component instanceof Container && !$component instanceof MultiChoiceControl) {
			return FALSE;
		}

		if (!$wrappedEntity->getMetadata()->hasAssociation($component->getName())) {
			return FALSE;
		}
		if (!$wrappedEntity->isToManyAssociation($component->name)) {
			return FALSE;
		}

		return TRUE;
	}


	protected function groupByPrimaryHash($entities, EntityWrapper $entityWrapper, $identifierFields)
	{
		$byPrimary = array();
		foreach ($entities as $subEntity) {
			$wrappedSubEntity = $entityWrapper->wrap($subEntity);
			$identifierValue = $wrappedSubEntity->getFlattenIdentifier();
			$keyHash = $this->createKeyHash($this->getIdentifierValues($identifierFields, $identifierValue));
			$byPrimary[$keyHash] = $subEntity;
		}

		return $byPrimary;
	}


	/**
	 * @param ClassMetadata $targetEntityMetadata
	 * @param WrappedEntity $wrappedEntity
	 * @return array
	 */
	protected function getIdentifierFields(ClassMetadata $targetEntityMetadata, WrappedEntity $wrappedEntity)
	{
		$identifierFields = $targetEntityMetadata->identifier;
		$identifierFields = array_filter($identifierFields, function ($value) use ($targetEntityMetadata, $wrappedEntity) {
			if (isset($targetEntityMetadata->associationMappings[$value])) {
				$associationMapping = $targetEntityMetadata->getAssociationMapping($value);

				return $wrappedEntity->getMetadata()->getName() != $associationMapping['targetEntity'];
			}

			return TRUE;
		});

		return $identifierFields;
	}


	/**
	 * @param Component $component
	 * @param Mapper $mapper
	 * @param $wrappedSubEntity
	 * @param $identifier
	 */
	protected function loadSubEntity(Component $component, Mapper $mapper, $wrappedSubEntity, $identifier)
	{
		if (!$component instanceof Container) {
			return;
		}
		$container = NULL;
		if ($identifier && isset($component[$identifier]) && ($checkbox = $component[$identifier]) instanceof Checkbox) {
			$checkbox->setDefaultValue(TRUE);
		} elseif ($identifier) {
			$container = $component[$identifier];
		} elseif (method_exists($component, 'createOne')) {
			$container = $component->createOne();
		}
		if ($container instanceof Container) {
			$mapper->loadValues($container, $wrappedSubEntity->getEntity());
		}
	}
}
<?php
namespace Librette\Doctrine\Forms\Mapper\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Librette\Doctrine\Forms\Mapper\IHandler;
use Librette\Doctrine\Forms\Mapper\Mapper;
use Librette\Doctrine\WrappedEntity;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\MultiChoiceControl;
use Nette\Forms\IControl;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author David Matejka
 */
class ToOneHandler implements IHandler
{

	public function load(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		if (!$component instanceof Container && !$component instanceof BaseControl) {
			return FALSE;
		}
		if ($component instanceof MultiChoiceControl) {
			return FALSE;
		}
		$meta = $wrappedEntity->getMetadata();
		if (!$this->getAssociationMetadata($meta, $component->name)) {
			return FALSE;
		}
		$subEntity = $wrappedEntity->getValue($component->name);
		if (!$subEntity) {
		} elseif ($component instanceof Container) {
			$mapper->loadValues($component, $subEntity);
		} elseif ($component instanceof BaseControl) {
			$wrappedSubEntity = $wrappedEntity->getEntityWrapper()->wrap($subEntity);
			$component->setDefaultValue($wrappedSubEntity->getSingleIdentifier());
		}

		return TRUE;
	}


	public function save(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		if (!$component instanceof Container && !$component instanceof IControl) {
			return FALSE;
		}
		if ($component instanceof MultiChoiceControl) {
			return FALSE;
		}
		$meta = $wrappedEntity->getMetadata();
		if (!$association = $this->getAssociationMetadata($meta, $component->name)) {
			return FALSE;
		}


		if ($component instanceof Container) {
			$relatedEntity = $this->getRelatedEntity($wrappedEntity, $component, $association);
			if ($relatedEntity) {
				$mapper->saveValues($component, $relatedEntity);
				$mapper->execute(function () use ($wrappedEntity, $component, $relatedEntity) {
					$wrappedEntity->getEntityManager()->persist($relatedEntity);
					$wrappedEntity->setValue($component->getName(), $relatedEntity);
				});
				$mapper->runValidation(function (ValidatorInterface $validator) use ($wrappedEntity, $component, $relatedEntity) {
					return $validator->validatePropertyValue($wrappedEntity, $component->getName(), $relatedEntity);
				}, $component->getForm());
			}

		} elseif ($component instanceof IControl) {
			if ($component->isOmitted()) {
				return TRUE;
			}
			$value = $component->getValue() ?: NULL;
			if ($wrappedEntity->hasAssociation($component->name) && $value && !is_object($value)) {
				$association = $wrappedEntity->getMetadata()->getAssociationMapping($component->name);
				$repository = $wrappedEntity->getEntityManager()->getRepository($association['targetEntity']);
				$value = $repository->find($value);
			}
			$mapper->execute(function () use ($wrappedEntity, $component, $value) {
				$wrappedEntity->setValue($component->name, $value);
			});
			$mapper->runValidation(function (ValidatorInterface $validator) use ($wrappedEntity, $component, $value) {
				return $validator->validatePropertyValue($wrappedEntity->getEntity(), $component->name, $value);
			}, $component);
		}

		return TRUE;
	}


	/**
	 * @param array $identifierFields
	 * @param array $data
	 * @return array|bool
	 */
	protected function getIdentifierFromArray(array $identifierFields, array $data)
	{
		$relatedEntityIdentifier = [];
		foreach ($identifierFields as $id) {
			if (!isset($data[$id]) || !is_scalar($data[$id])) {
				return FALSE;
			}
			$relatedEntityIdentifier[$id] = $data[$id];
		}

		return $relatedEntityIdentifier;
	}


	/**
	 * @param WrappedEntity $wrappedEntity
	 * @param Container $component
	 * @param array $association
	 * @return null|object
	 */
	protected function getRelatedEntity(WrappedEntity $wrappedEntity, Container $component, $association)
	{
		$relatedEntity = $wrappedEntity->getValue($component->name);
		if ($relatedEntity === NULL) {
			$className = $association['targetEntity'];
			$entityManager = $wrappedEntity->getEntityManager();
			$metadata = $entityManager->getClassMetadata($className);
			$identifierFields = $metadata->identifier;
			$data = $component->getValues(TRUE);
			$relatedEntityIdentifier = $this->getIdentifierFromArray($identifierFields, $data);
			if ($relatedEntityIdentifier) {
				$repository = $entityManager->getRepository($className);
				$relatedEntity = $repository->find($relatedEntityIdentifier);
			}
			if (!$relatedEntity) {
				$relatedEntity = $metadata->newInstance();
			}
		}

		return $relatedEntity;
	}


	private function getAssociationMetadata(ClassMetadata $meta, $associationName)
	{
		if (!$meta->hasAssociation($associationName)) {
			return FALSE;
		}
		$association = $meta->getAssociationMapping($associationName);
		if (!$association & ClassMetadata::TO_ONE) {
			return FALSE;
		}

		return $association;
	}

}

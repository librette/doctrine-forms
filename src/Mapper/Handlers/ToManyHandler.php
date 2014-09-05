<?php
namespace Librette\Doctrine\Forms\Mapper\Handlers;

use Librette\Doctrine\Forms\Mapper\IHandler;
use Librette\Doctrine\Forms\Mapper\Mapper;
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


		$identifiers = [];
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
		$toManySaveHelper = new ToManySaveHelper($wrappedEntity, $component, $mapper);
		$toManySaveHelper->process();

		return TRUE;
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

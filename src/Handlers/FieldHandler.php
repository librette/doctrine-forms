<?php
namespace Librette\Doctrine\Forms\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Librette\Doctrine\Forms\IHandler;
use Librette\Doctrine\Forms\Mapper;
use Librette\Doctrine\WrappedEntity;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\IControl;

/**
 * @author David Matejka
 */
class FieldHandler implements IHandler
{


	public function load(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		if (!$this->canHandle($component, $wrappedEntity)) {
			return FALSE;
		}

		$value = $wrappedEntity->getValue($component->name);

		if ($component instanceof BaseControl) {
			$component->setDefaultValue($value);
		} elseif ($component instanceof Container && !empty($value)) {
			$component->setDefaults($value);
		}

		return TRUE;
	}


	public function save(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		if (!$this->canHandle($component, $wrappedEntity)) {
			return FALSE;
		}

		$value = NULL;
		if ($component instanceof IControl) {
			$value = $component->getValue();
		} elseif ($component instanceof Container) {
			$value = $component->getValues(TRUE);
		}
		$wrappedEntity->setValue($component->name, $value);

		return TRUE;
	}


	protected function canHandle(Component $component, WrappedEntity $wrappedEntity)
	{
		if (!$component instanceof BaseControl && !$component instanceof Container) {
			return FALSE;
		}

		if (!$wrappedEntity->hasField($component->getName())) {
			return FALSE;
		}

		return TRUE;
	}
}
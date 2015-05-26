<?php
namespace Librette\Doctrine\Forms\Mapper\Handlers;

use Doctrine\DBAL\Types\Type;
use Librette\Doctrine\Forms\Mapper\IHandler;
use Librette\Doctrine\Forms\Mapper\Mapper;
use Librette\Doctrine\WrappedEntity;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\IControl;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author David Matejka
 */
class FieldHandler implements IHandler
{

	private static $numberTypes = [Type::INTEGER, Type::SMALLINT, Type::BIGINT, Type::FLOAT, Type::DECIMAL];

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
			if ($component->isOmitted()) {
				return TRUE;
			}
			$value = $component->getValue();
		} elseif ($component instanceof Container) {
			$value = $component->getValues(TRUE);
		}
		$mapping = $wrappedEntity->getMetadata()->getFieldMapping($component->name);
		if ($value === "" && $mapping['nullable'] && in_array($mapping['type'], self::$numberTypes)) {
			$value = NULL;
		}
		$mapper->execute(function () use ($wrappedEntity, $component, $value) {
			$wrappedEntity->setValue($component->name, $value);
		});
		$mapper->runValidation(function (ValidatorInterface $validator) use ($wrappedEntity, $component, $value) {
			return $validator->validatePropertyValue($wrappedEntity->getEntity(), $component->name, $value);
		}, $component);

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

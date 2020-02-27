<?php
namespace Librette\Doctrine\Forms\Mapper\Handlers;

use Librette\Doctrine\Forms\Mapper\Handlers\Date\IAdapter;
use Librette\Doctrine\Forms\Mapper\IHandler;
use Librette\Doctrine\Forms\Mapper\Mapper;
use Librette\Doctrine\WrappedEntity;
use Nette\ComponentModel\Component;
use Nette\Forms\Controls\TextBase;
use Nette\SmartObject;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author David Matejka
 */
class DateHandler implements IHandler
{
	use SmartObject;

	/** @var IAdapter */
	protected $adapter;


	/**
	 * @param IAdapter
	 */
	public function __construct(IAdapter $adapter)
	{
		$this->adapter = $adapter;
	}


	public function load(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		if (!$this->canHandle($component, $wrappedEntity)) {
			return FALSE;
		}
		$mapping = $wrappedEntity->getMetadata()->getFieldMapping($component->getName());
		$value = $wrappedEntity->getValue($component->name);
		if ($value instanceof \DateTime) {
			/** @var TextBase $component */
			$format = $component->getOption('date-format');
			$type = $this->convertMappingToAdapterType($mapping['type']);
			$value = $this->adapter->format($value, $type, $format);
			$component->setDefaultValue($value);
		}

		return TRUE;
	}


	public function save(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		if (!$this->canHandle($component, $wrappedEntity)) {
			return FALSE;
		}
		/** @var TextBase $component */
		if ($component->isOmitted()) {
			return TRUE;
		}
		$mapping = $wrappedEntity->getMetadata()->getFieldMapping($component->getName());
		$value = $component->getValue();
		if (is_string($value)) {
			$format = $component->getOption('date-format');
			$type = $this->convertMappingToAdapterType($mapping['type']);
			$value = $this->adapter->parse($value, $type, $format);
		}

		$mapper->execute(function () use ($wrappedEntity, $component, $value) {
			$wrappedEntity->setValue($component->name, $value);
		});
		$mapper->runValidation(function (ValidatorInterface $validator) use ($wrappedEntity, $component, $value) {
			return $validator->validatePropertyValue($wrappedEntity->getEntity(), $component->name, $value);
		}, $component);

		return TRUE;
	}


	private function convertMappingToAdapterType($mappingType)
	{
		$conversionTable = ['date' => IAdapter::DATE, 'time' => IAdapter::TIME, 'datetime' => IAdapter::DATE_TIME];

		return $conversionTable[$mappingType];
	}


	protected function canHandle(Component $component, WrappedEntity $wrappedEntity)
	{
		if (!$component instanceof TextBase) {
			return FALSE;
		}

		if (!$wrappedEntity->hasField($component->getName())) {
			return FALSE;
		}
		$mapping = $wrappedEntity->getMetadata()->getFieldMapping($component->getName());

		return in_array($mapping['type'], ['date', 'time', 'datetime']);
	}
}

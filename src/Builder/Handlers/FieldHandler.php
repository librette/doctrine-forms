<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\ControlBuilder;
use Librette\Doctrine\Forms\Builder\ControlFactory;
use Librette\Doctrine\Forms\Builder\IHandler;
use Nette\Object;

/**
 * @author David Matejka
 */
class FieldHandler extends Object implements IHandler
{

	public function handle($name, array $options, ClassMetadata $classMetadata, Configuration $configuration)
	{
		if (!$classMetadata->hasField($name)) {
			return NULL;
		}
		$mapping = $classMetadata->getFieldMapping($name);
		$controlName = empty($options['control']) ? $this->getControlByType($mapping['type']) : $options['control'];
		$control = ControlFactory::create($controlName, ['\Nette\Forms\Controls\BaseControl'], ControlFactory::TEXT_INPUT);
		if (empty($options['caption'])) {
			$control->caption = $configuration->getLabelingStrategy()->getControlLabel($name, $classMetadata);
		} else {
			$control->caption = $options['caption'];
		}

		return new ControlBuilder($control);
	}


	protected function getControlByType($type)
	{
		switch ($type) {
			case 'text':
				return ControlFactory::TEXT_AREA;
				break;
			case 'boolean':
				return ControlFactory::CHECKBOX;
				break;
			default:
				return ControlFactory::TEXT_INPUT;
				break;
		}
	}

}

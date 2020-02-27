<?php
namespace Librette\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Nette\SmartObject;

/**
 * @author David Matejka
 */
class SimpleLabelingStrategy implements ILabelingStrategy
{
	use SmartObject;

	public function getControlLabel($name, ClassMetadata $metadata)
	{
		return $name;
	}


	public function getButtonLabel($name)
	{
		return $name;
	}

}

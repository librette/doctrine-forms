<?php
namespace Librette\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Nette\Object;

/**
 * @author David Matejka
 */
class SimpleLabelingStrategy extends Object implements ILabelingStrategy
{

	public function getControlLabel($name, ClassMetadata $metadata)
	{
		return $name;
	}


	public function getButtonLabel($name)
	{
		return $name;
	}

}

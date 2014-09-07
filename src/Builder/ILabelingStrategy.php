<?php
namespace Librette\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @author David Matejka
 */
interface ILabelingStrategy
{

	public function getControlLabel($name, ClassMetadata $metadata);


	public function getButtonLabel($name);
}

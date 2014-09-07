<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @author David Matejka
 */
class MetadataHelpers
{

	public static function getAssociationMapping(ClassMetadata $classMetadata, $name, $type = NULL)
	{
		if (!$classMetadata->hasAssociation($name)) {
			return NULL;
		}
		$mapping = $classMetadata->getAssociationMapping($name);
		if ($type === NULL || $mapping['type'] === $type) {
			return $mapping;
		}

		return NULL;
	}
}

<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @author David Matejka
 */
class MetadataHelpers
{

	/***
	 * @param ClassMetadata
	 * @param string
	 * @param null|string|array
	 * @return array|null
	 */
	public static function getAssociationMapping(ClassMetadata $classMetadata, $name, $type = NULL)
	{
		if (!$classMetadata->hasAssociation($name)) {
			return NULL;
		}
		$mapping = $classMetadata->getAssociationMapping($name);
		$type = $type ? (array) $type : NULL;
		if ($type === NULL || in_array($mapping['type'], $type, TRUE)) {
			return $mapping;
		}

		return NULL;
	}
}

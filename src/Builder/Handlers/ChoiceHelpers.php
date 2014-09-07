<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityDao;

/**
 * @author David Matejka
 */
class ChoiceHelpers
{

	public static $valueFieldsFallback = ['name', 'title'];


	public static function getPairs(EntityDao $dao, array $options = [])
	{
		$options += ['criteria' => [], 'orderBy' => [], 'key' => NULL];
		if (empty($options['value'])) {
			$options['value'] = self::getValueFieldFallback($dao->getClassMetadata());
		}

		return $dao->findPairs($options['criteria'], $options['value'], $options['orderBy'], $options['key']);
	}


	public static function getValueFieldFallback(ClassMetadata $classMetadata)
	{
		foreach (self::$valueFieldsFallback as $field) {
			if ($classMetadata->hasField($field)) {
				return $field;
			}
		}

		return NULL;
	}
}

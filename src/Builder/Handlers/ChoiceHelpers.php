<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\AbstractQuery;
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
		$options += ['criteria' => [],
		             'orderBy'  => [],
		             'key'      => $dao->getClassMetadata()->getSingleIdentifierFieldName()
		];
		if (empty($options['value'])) {
			$options['value'] = self::getValueFieldFallback($dao->getClassMetadata());
		}
		if (is_string($options['orderBy'])) {
			$options['orderBy'] = [$options['orderBy'] => 'asc'];
		}
		if ($dao->getClassMetadata()->hasField($options['value'])) {
			return $dao->findPairs($options['criteria'], $options['value'], $options['orderBy'], $options['key']);
		} else {
			return self::getPairsAdvanced($dao, $options);
		}
	}


	private static function getPairsAdvanced(EntityDao $dao, array $options)
	{
		$query = $dao->createQueryBuilder('e')
		             ->whereCriteria($options['criteria'])
		             ->select("e")
		             ->resetDQLPart('from')->from($dao->getClassName(), 'e', 'e.' . $options['key'])
		             ->autoJoinOrderBy((array) $options['orderBy'])
		             ->getQuery();
		$parts = explode('.', $options['value']);

		$result = $query->getResult();

		return array_map(function ($value) use ($parts) {
			foreach ($parts as $part) {
				$value = $value->$part;
			}

			return $value;
		}, $result);
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

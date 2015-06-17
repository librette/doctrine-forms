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
		if (is_string($options['value']) && $dao->getClassMetadata()->hasField($options['value']) && !array_filter($options['criteria'], 'is_callable')) {
			return $dao->findPairs($options['criteria'], $options['value'], $options['orderBy'], $options['key']);
		} else {
			return self::getPairsAdvanced($dao, $options);
		}
	}


	private static function getPairsAdvanced(EntityDao $dao, array $options)
	{

		$qb = $dao->createQueryBuilder('e')
		          ->select("e")
		          ->resetDQLPart('from')->from($dao->getClassName(), 'e', 'e.' . $options['key'])
		          ->autoJoinOrderBy((array) $options['orderBy']);
		foreach ($options['criteria'] as $key => $value) {
			if (is_numeric($key) && is_callable($value)) {
				$value($qb, 'e');
			} else {
				$qb->whereCriteria([$key => $value]);
			}
		}
		$query = $qb->getQuery();
		if (is_string($options['value'])) {
			$parts = explode('.', $options['value']);
			$callback = function ($value) use ($parts) {
				foreach ($parts as $part) {
					$value = $value->$part;
				}

				return $value;
			};
		} else {
			$callback = $options['value'];
		}


		$result = $query->getResult();

		return array_map($callback, $result);
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

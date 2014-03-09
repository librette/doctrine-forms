<?php
namespace Librette\Doctrine\Forms;

/**
 * @author David Matějka
 */
interface MapperFactory
{

	/**
	 * @param $entity
	 * @param null|array $offset
	 * @return Mapper
	 */
	public function create($entity, $offset = NULL);
}
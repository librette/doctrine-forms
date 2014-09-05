<?php
namespace Librette\Doctrine\Forms;

use Librette\Doctrine\Forms\Mapper\Mapper;

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

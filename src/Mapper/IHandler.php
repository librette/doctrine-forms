<?php
namespace Librette\Doctrine\Forms\Mapper;

use Librette\Doctrine\WrappedEntity;
use Nette\ComponentModel\Component;

/**
 * @author David Matejka
 */
interface IHandler
{

	/**
	 * @param WrappedEntity $wrappedEntity
	 * @param Component $component
	 * @param Mapper $mapper
	 * @return boolean
	 */
	public function load(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper);


	/**
	 * @param WrappedEntity $wrappedEntity
	 * @param Component $component
	 * @param Mapper $mapper
	 * @return boolean
	 */
	public function save(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper);
}

<?php
namespace Librette\Doctrine\Forms\Mapper;

/**
 * @author David Matejka
 */
interface IExecutionStrategy
{

	/**
	 * @param callable
	 * @return void
	 */
	public function execute($callback);


	/**
	 * @return void
	 */
	public function confirm();
}

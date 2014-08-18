<?php
namespace Librette\Doctrine\Forms;

use Nette\Object;
use Nette\Utils\Callback;

/**
 * @author David Matejka
 */
class PostponedExecution extends Object implements IExecutionStrategy
{

	/** @var callable[] */
	protected $callbacks = [];


	/**
	 * @param callable
	 * @return void
	 */
	public function execute($callback)
	{
		$this->callbacks[] = Callback::check($callback);
	}


	/**
	 * @return void
	 */
	public function confirm()
	{
		foreach ($this->callbacks as $callback) {
			Callback::invoke($callback);
		}
	}

}

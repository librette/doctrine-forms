<?php
namespace Librette\Doctrine\Forms\Mapper;

use Nette\Object;
use Nette\Utils\Callback;

/**
 * @author David Matejka
 */
class ImmediateExecution extends Object implements IExecutionStrategy
{


	public function execute($callback)
	{
		Callback::invoke($callback);
	}


	public function confirm()
	{
	}

}

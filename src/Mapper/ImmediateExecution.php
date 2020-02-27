<?php
namespace Librette\Doctrine\Forms\Mapper;
use Nette\SmartObject;
use Nette\Utils\Callback;

/**
 * @author David Matejka
 */
class ImmediateExecution implements IExecutionStrategy
{
	use SmartObject;


	public function execute($callback)
	{
		Callback::invoke($callback);
	}


	public function confirm()
	{
	}

}

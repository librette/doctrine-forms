<?php
namespace Librette\Doctrine\Forms\Builder;

use Kdyby\Replicator\Container as Replicator;

/**
 * @author David Matejka
 */
class ClonnableReplicator extends Replicator
{

	public function __clone()
	{
		//hacking
		$rc = new \ReflectionClass('\Nette\ComponentModel\Container');
		$rm = $rc->getMethod('__clone');
		$rm->invoke($this);
	}

}

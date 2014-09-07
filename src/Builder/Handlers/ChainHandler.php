<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\IHandler;
use Nette\Object;

/**
 * @author David Matejka
 */
class ChainHandler extends Object implements IHandler
{

	/** @var IHandler[] */
	protected $handlers = [];


	/**
	 * @param IHandler[]
	 */
	function __construct($handlers = [])
	{
		$this->handlers = $handlers;
	}


	public function add(IHandler $handler, $prepend = TRUE)
	{
		if ($prepend === TRUE) {
			array_unshift($this->handlers, $handler);
		} else {
			$this->handlers[] = $handler;
		}
	}


	public function handle($name, array $options, ClassMetadata $classMetadata, Configuration $configuration)
	{
		foreach ($this->handlers as $handler) {
			$builder = $handler->handle($name, $options, $classMetadata, $configuration);
			if ($builder !== NULL) {
				return $builder;
			}
		}

		return NULL;
	}

}

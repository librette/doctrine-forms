<?php
namespace Librette\Doctrine\Forms\Mapper\Handlers;

use Librette\Doctrine\Forms\Mapper\IHandler;
use Librette\Doctrine\Forms\Mapper\Mapper;
use Librette\Doctrine\Forms\ValidationException;
use Librette\Doctrine\WrappedEntity;
use Nette\ComponentModel\Component;
use Nette\Object;

/**
 * @author David Matejka
 */
class ChainHandler extends Object implements IHandler
{

	/** @var IHandler[] */
	protected $handlers;


	/**
	 * @param IHandler[]
	 */
	public function __construct(array $handlers = [])
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


	/**
	 * @param WrappedEntity
	 * @param Component
	 * @param Mapper
	 * @return boolean
	 */
	public function load(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		foreach ($this->handlers as $handler) {
			if ($handler->load($wrappedEntity, $component, $mapper)) {
				return;
			}
		}
	}


	/**
	 * @param WrappedEntity
	 * @param Component
	 * @param Mapper
	 * @return boolean
	 */
	public function save(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		foreach ($this->handlers as $handler) {
			try {
				if ($handler->save($wrappedEntity, $component, $mapper)) {
					return;
				}
			} catch (ValidationException $e) {
				return;
			}
		}
	}

}

<?php
namespace Librette\Doctrine\Forms\Handlers;

use Librette\Doctrine\Forms\IHandler;
use Librette\Doctrine\Forms\Mapper;
use Librette\Doctrine\WrappedEntity;
use Nette\ComponentModel\Component;
use Nette\Utils\Callback;

/**
 * @author David Matejka
 */
class CallbackHandler implements IHandler
{

	/** @var callable */
	protected $saveCallback;

	/** @var callable */
	protected $loadCallback;


	/**
	 * @param callable $saveCallback
	 * @param callable $loadCallback
	 */
	public function __construct($saveCallback, $loadCallback)
	{
		$this->saveCallback = Callback::check($saveCallback);
		$this->loadCallback = Callback::check($loadCallback);
	}


	public function load(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		return call_user_func_array($this->loadCallback, func_get_args());
	}


	public function save(WrappedEntity $wrappedEntity, Component $component, Mapper $mapper)
	{
		return call_user_func_array($this->saveCallback, func_get_args());
	}

}
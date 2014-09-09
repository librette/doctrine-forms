<?php
namespace Librette\Doctrine\Forms\Builder;

use Librette\Doctrine\Forms\InvalidStateException;
use Nette\ComponentModel\IComponent;
use Nette\Object;

/**
 * @author David Matejka
 */
abstract class BaseBuilder extends Object implements IBuilder
{

	/** @var IBuilder */
	protected $parent;

	/** @var IComponent */
	protected $component;

	/** @var string */
	protected $name;


	public function __construct(IComponent $component)
	{
		$this->component = $component;
	}


	public function attach(IBuilder $parent, $name)
	{
		if ($this->parent) {
			throw new InvalidStateException("Builder {$this->name} already has a parent.");
		}
		$this->parent = $parent;
		$this->name = $name;
		$this->attached();
	}


	protected function attached()
	{

	}


	public function getParent()
	{
		return $this->parent;
	}


	public function getFormBuilder()
	{
		if (!$this->parent) {
			return NULL;
		}

		return $this->parent->getFormBuilder();
	}


	/**
	 * @return IComponent
	 */
	public function getComponent()
	{
		return $this->component;
	}

}

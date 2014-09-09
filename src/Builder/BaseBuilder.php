<?php
namespace Librette\Doctrine\Forms\Builder;

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


	public function setParent(IBuilder $parent)
	{
		$this->parent = $parent;
	}


	public function getParent()
	{
		return $this->parent;
	}


	public function setName($name)
	{
		$this->name = $name;
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

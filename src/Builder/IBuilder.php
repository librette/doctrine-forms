<?php
namespace Librette\Doctrine\Forms\Builder;

use Nette\ComponentModel\IComponent;

/**
 * @author David Matejka
 */
interface IBuilder
{

	/**
	 * @return IComponent
	 */
	public function getComponent();


	/**
	 * @param IBuilder
	 * @return void
	 */
	public function setParent(IBuilder $builder);


	/**
	 * @return IBuilder
	 */
	public function getParent();


	/**
	 * @param string
	 * @return void
	 */
	public function setName($name);


	/**
	 * @return FormBuilder|null
	 */
	public function getFormBuilder();
}

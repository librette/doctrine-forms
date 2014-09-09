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
	 * @param string
	 * @return void
	 * @internal
	 */
	public function attach(IBuilder $builder, $name);


	/**
	 * @return IBuilder
	 */
	public function getParent();


	/**
	 * @return FormBuilder|null
	 */
	public function getFormBuilder();
}

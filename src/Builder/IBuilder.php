<?php
namespace Librette\Doctrine\Forms\Builder;

use Nette\ComponentModel\IComponent;

/**
 * @author David Matejka
 * @method IBuilder getParent()
 */
interface IBuilder extends IComponent
{

	/**
	 * @return IComponent
	 */
	public function getFormComponent();


	/**
	 * @return FormBuilder|null
	 */
	public function getFormBuilder($need = TRUE);
}

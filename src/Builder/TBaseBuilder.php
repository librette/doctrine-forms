<?php
namespace Librette\Doctrine\Forms\Builder;

use Nette\ComponentModel\IContainer;

/**
 * @author David Matejka
 * @mixin \Nette\ComponentModel\Component|IBuilder
 */
trait TBaseBuilder
{

	/**
	 * @return FormBuilder|null
	 */
	public function getFormBuilder($need = TRUE)
	{
		return $this->lookup('\Librette\Doctrine\Forms\Builder\FormBuilder', $need);
	}


	protected function validateParent(IContainer $parent)
	{
		$this->monitor('\Librette\Doctrine\Forms\Builder\FormBuilder');
	}
}

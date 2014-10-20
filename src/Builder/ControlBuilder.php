<?php
namespace Librette\Doctrine\Forms\Builder;

use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\IControl;

/**
 * @author David Matejka
 */
class ControlBuilder extends Component implements IBuilder
{

	use TBaseBuilder;

	/** @var IControl */
	protected $control;


	public function __construct(IControl $control)
	{
		$this->control = $control;
	}


	/**
	 * @return FormBuilder|null
	 */
	public function getFormBuilder($need = TRUE)
	{
		return $this->lookup('\Librette\Doctrine\Forms\Builder\FormBuilder', $need);
	}


	/**
	 * @return IComponent|IControl|BaseControl
	 */
	public function getFormComponent()
	{
		return $this->control;
	}


	public function setCaption($caption)
	{
		if ($this->control instanceof BaseControl) {
			$this->control->caption = $caption;
		}
	}
}

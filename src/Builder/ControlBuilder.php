<?php
namespace Librette\Doctrine\Forms\Builder;

use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\IControl;

/**
 * @author David Matejka
 * @method IComponent|IControl|BaseControl getComponent()
 */
class ControlBuilder extends BaseBuilder
{


	public function __construct(IControl $control)
	{
		parent::__construct($control);
	}


	public function setCaption($caption)
	{
		if ($this->component instanceof BaseControl) {
			$this->component->caption = $caption;
		}
	}
}

<?php
namespace Librette\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Nette\Forms\Container;
use Nette\Forms\Form;

/**
 * @author David Matejka
 *
 * @method Form getFormComponent()
 */
class FormBuilder extends ContainerBuilder
{

	public function __construct(ClassMetadata $metadata, Container $container, Configuration $configuration)
	{
		parent::__construct($metadata, $container, $configuration);
		$this->onAttached($this);
	}


	public function getFormBuilder($need = TRUE)
	{
		return $this;
	}


	/**
	 * @return Form
	 */
	public function getForm()
	{
		return $this->getFormComponent();
	}

}

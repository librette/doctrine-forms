<?php
namespace Librette\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Librette\Doctrine\Forms\MapperFactory;
use Librette\Forms\IFormWithMapper;
use Nette\Forms\Form;

/**
 * @author David Matejka
 *
 * @method Form getComponent()
 */
class FormBuilder extends ContainerBuilder
{

	protected $mapperFactory;


	public function __construct(ClassMetadata $metadata, Form $container, Configuration $configuration, MapperFactory $mapperFactory = NULL)
	{
		parent::__construct($metadata, $container, $configuration);
		$this->mapperFactory = $mapperFactory;
	}


	public function getFormBuilder()
	{
		return $this;
	}


	public function setMapper($entity)
	{
		if (!$this->mapperFactory) {
			throw new \RuntimeException("Mapper factory has not been set");
		}
		$form = $this->getComponent();
		if (!$form instanceof IFormWithMapper) {
			throw new \RuntimeException("Form does not implement \\Librette\\Forms\\IFormWithMapper");
		}
		$form->setMapper($this->mapperFactory->create($entity));
	}


	/**
	 * @return Form
	 */
	public function getForm()
	{
		return $this->component;
	}
}

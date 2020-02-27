<?php
namespace Librette\Doctrine\Forms;

use Librette\Doctrine\Forms\Mapper\Mapper;
use Librette\Forms;
use Nette;

/**
 * @author David Matějka
 */
class FormFactory
{
	use Nette\SmartObject;

	/** @var \Librette\Doctrine\Forms\MapperFactory */
	protected $mapperFactory;

	/** @var Forms\IFormFactory */
	protected $formFactory;


	/**
	 * @param MapperFactory $mapperFactory
	 * @param Forms\IFormFactory $formFactory
	 */
	function __construct(MapperFactory $mapperFactory, Forms\IFormFactory $formFactory = NULL)
	{
		$this->formFactory = $formFactory ?: new Forms\FormFactory;
		$this->mapperFactory = $mapperFactory;
	}


	/**
	 * @param object $entity entity
	 * @param null|array $offset
	 * @return Mapper
	 */
	public function createMapper($entity, $offset = NULL)
	{
		return $this->mapperFactory->create($entity, $offset);
	}


	/**
	 * @param null|object $entity entity
	 * @param null|array $offset
	 * @return Nette\Application\UI\Form|Forms\TFormWithMapper
	 */
	public function create($entity = NULL, $offset = NULL)
	{
		$form = $this->formFactory->create();
		if (!$form instanceof Forms\IFormWithMapper) {
			throw new UnexpectedValueException('Librette\Forms\IFormWithMapper needed, instance of ' . get_class($form) . ' given');
		}
		if ($entity) {
			$form->setMapper($this->createMapper($entity, $offset));
		}

		return $form;
	}
}

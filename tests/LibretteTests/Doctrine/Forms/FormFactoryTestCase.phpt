<?php
namespace LibretteTests\Doctrine\Forms;

use Librette\Doctrine\Forms\FormFactory;
use Librette\Doctrine\Forms\MapperFactory;
use Librette\Doctrine\Forms\Mapper;
use Librette\Forms\IFormWithMapper;
use Nette;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/models/model.php';


class FormFactoryTestCase extends ORMTestCase
{


	public function setUp()
	{
		$this->createMemoryManager(FALSE);
	}


	public function testMapperFactory()
	{
		$entity = new CmsUser();
		/** @var MapperFactory $mapperFactory */
		$mapperFactory = $this->serviceLocator->getByType('Librette\Doctrine\Forms\MapperFactory');

		$mapper = $mapperFactory->create($entity);

		Assert::true($mapper instanceof Mapper\Mapper);
		Assert::equal($entity, $mapper->getEntity());
	}


	public function testFormFactory()
	{
		$entity = new CmsUser();

		/** @var FormFactory $formFactory */
		$formFactory = $this->serviceLocator->getByType('Librette\Doctrine\Forms\FormFactory');

		$form = $formFactory->create($entity);

		Assert::true($form instanceof Nette\Forms\Form);
		Assert::true($form instanceof IFormWithMapper);

		$mapper = $form->getMapper();

		Assert::true($mapper instanceof Mapper\Mapper);
		Assert::equal($entity, $mapper->getEntity());
	}
}


run(new FormFactoryTestCase());

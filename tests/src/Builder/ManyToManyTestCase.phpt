<?php
namespace LibretteTests\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\ControlFactory;
use Librette\Doctrine\Forms\Builder\Handlers\ManyToManyHandler;
use LibretteTests\Doctrine\Forms\Model\CmsGroup;
use LibretteTests\Doctrine\Forms\Model\CmsUser;
use LibretteTests\Doctrine\Forms\ORMTestCase;
use Nette;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class ManyToManyTestCase extends ORMTestCase
{

	/** @var EntityManager */
	protected $em;

	/** @var Configuration */
	protected $configuration;

	/** @var ClassMetadata */
	protected $meta;

	/** @var CmsGroup[] */
	protected $groups = [];


	public function setUp()
	{
		$this->em = $this->createMemoryManager(TRUE);
		$this->configuration = new Configuration(new ManyToManyHandler($this->em));
		$this->meta = $this->em->getClassMetadata(CmsUser::getClassName());
		foreach ([1, 2, 3, 4] as $id) {
			$this->groups[] = $group = new CmsGroup('group ' . $id);
			$this->em->persist($group);
		}
		$this->em->flush();
	}


	public function testBuilderType()
	{
		$builder = $this->doHandle('groups');
		Assert::type('\Librette\Doctrine\Forms\Builder\ControlBuilder', $builder);
	}


	public function testMultiSelectBox()
	{
		$builder = $this->doHandle('groups');
		/** @var Nette\Forms\Controls\MultiSelectBox $component */
		$component = $builder->getComponent();
		Assert::type('\Nette\Forms\Controls\MultiSelectBox', $component);
		Assert::count(4, $component->getItems());
		$expectedItems = array_combine(array_map(function (CmsGroup $group) {
			return $group->id;
		}, $this->groups), array_map(function (CmsGroup $group) {
			return $group->name;
		}, $this->groups));
		Assert::equal($expectedItems, $component->getItems());
	}


	public function testCheckboxList()
	{
		$builder = $this->doHandle('groups', ['control' => ControlFactory::CHECKBOX_LIST]);
		/** @var Nette\Forms\Controls\CheckboxList $component */
		$component = $builder->getComponent();
		Assert::type('\Nette\Forms\Controls\CheckboxList', $component);
		Assert::count(4, $component->getItems());
	}


	public function testFilter()
	{
		$builder = $this->doHandle('groups', ['criteria' => ['id !=' => $this->groups[0]->id]]);
		/** @var Nette\Forms\Controls\MultiSelectBox $component */
		$component = $builder->getComponent();
		Assert::count(3, $component->getItems());
	}


	public function testNotFill()
	{
		$builder = $this->doHandle('groups', ['fill' => FALSE]);
		/** @var Nette\Forms\Controls\MultiSelectBox $component */
		$component = $builder->getComponent();
		Assert::count(0, $component->getItems());
	}


	/**
	 * @param string
	 */
	protected function doHandle($name, $options = [])
	{
		return $this->configuration->getHandler()->handle($name, $options, $this->meta, $this->configuration);
	}

}


run(new ManyToManyTestCase());

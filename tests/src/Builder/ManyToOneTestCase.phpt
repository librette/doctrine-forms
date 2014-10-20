<?php
namespace LibretteTests\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Librette;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\Handlers\ManyToOneHandler;
use LibretteTests\Doctrine\Forms\Model\CmsArticle;
use LibretteTests\Doctrine\Forms\Model\CmsUser;
use LibretteTests\Doctrine\Forms\ORMTestCase;
use Nette;
use Tester;

require_once __DIR__ . '/../../bootstrap.php';


/**
 * @author David Matějka
 */
class ManyToOneTestCase extends ORMTestCase
{

	/** @var EntityManager */
	protected $em;

	/** @var Configuration */
	protected $configuration;

	/** @var ClassMetadata */
	protected $meta;

	/** @var CmsUser[] */
	protected $users = [];


	public function setUp()
	{
		$this->em = $this->createMemoryManager(TRUE);
		$this->configuration = new Configuration(new ManyToOneHandler($this->em));
		$this->meta = $this->em->getClassMetadata(CmsArticle::getClassName());
		foreach ([1, 2, 3, 4] as $id) {
			$this->users[] = $group = new CmsUser('john ' . $id);
			$this->em->persist($group);
		}
		$this->em->flush();
	}


	public function testBuilderType()
	{
		$builder = $this->doHandle('user');
		Tester\Assert::type('\Librette\Doctrine\Forms\Builder\ControlBuilder', $builder);
	}


	public function testSelectBox()
	{
		$builder = $this->doHandle('user');
		$items = array_combine(array_map(function (CmsUser $user) {
			return $user->id;
		}, $this->users), array_map(function (CmsUser $user) {
			return $user->name;
		}, $this->users));
		/** @var Nette\Forms\Controls\SelectBox $component */
		$component = $builder->getFormComponent();
		Tester\Assert::type('\Nette\Forms\Controls\SelectBox', $component);
		Tester\Assert::count(4, $component->getItems());
		Tester\Assert::same($component->getItems(), $items);
	}


	public function testRadioList()
	{
		$builder = $this->doHandle('user', ['control' => Librette\Doctrine\Forms\Builder\ControlFactory::RADIO_LIST]);
		/** @var Nette\Forms\Controls\RadioList $component */
		$component = $builder->getFormComponent();
		Tester\Assert::type('\Nette\Forms\Controls\RadioList', $component);
		Tester\Assert::count(4, $component->getItems());
	}


	public function testNotFill()
	{
		$builder = $this->doHandle('user', ['fill' => FALSE]);
		/** @var Nette\Forms\Controls\SelectBox $component */
		$component = $builder->getFormComponent();
		Tester\Assert::count(0, $component->getItems());
	}


	protected function doHandle($name, $options = [])
	{
		return $this->configuration->getHandler()->handle($name, $options, $this->meta, $this->configuration);
	}
}


\run(new ManyToOneTestCase());

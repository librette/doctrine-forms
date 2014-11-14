<?php
namespace LibretteTests\Doctrine\Forms\Builder;

use Kdyby\Doctrine\EntityManager;
use Librette;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\FormBuilder;
use Librette\Doctrine\Forms\Builder\Handlers;
use LibretteTests\Doctrine\Forms\Model\CmsArticle;
use LibretteTests\Doctrine\Forms\ORMTestCase;
use Nette;
use Nette\Forms\Form;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


/**
 * @author David Matějka
 * @testCase
 */
class FormBuilderTestCase extends ORMTestCase
{

	/** @var EntityManager */
	protected $em;

	/** @var Configuration */
	protected $configuration;


	public function setUp()
	{
		$this->em = $this->createMemoryManager(TRUE);
		$chainHandler = new Handlers\ChainHandler([
			new Handlers\FieldHandler(),
			new Handlers\OneToOneHandler($this->em),
			new Handlers\OneToManyHandler($this->em),
			new Handlers\ManyToOneHandler($this->em),
			new Handlers\ManyToManyHandler($this->em),
		]);
		$this->configuration = new Configuration($chainHandler);
	}


	public function testAdd()
	{
		$builder = $this->createBuilder(CmsArticle::getClassName());
		$controlBuilder = $builder->add('topic');
		Assert::type('\Librette\Doctrine\Forms\Builder\ControlBuilder', $controlBuilder);
		Assert::type('\Nette\Forms\Controls\TextInput', $controlBuilder->getFormComponent());
	}


	public function testAddList()
	{
		$builder = $this->createBuilder(CmsArticle::getClassName());
		$builders = $builder->addList(['topic', 'text' => ['caption' => 'Foo']]);
		Assert::count(2, $builders);
		Assert::count(2, $builder->getForm()->getComponents());
		Assert::type('\Librette\Doctrine\Forms\Builder\ControlBuilder', $builders['topic']);
		Assert::type('\Librette\Doctrine\Forms\Builder\ControlBuilder', $builders['text']);
		Assert::equal('topic', $builders['topic']->getFormComponent()->caption);
		Assert::equal('Foo', $builders['text']->getFormComponent()->caption);
	}


	public function testAddAll()
	{
		$builder = $this->createBuilder(CmsArticle::getClassName());
		$builders = $builder->addAll();
		Assert::count(9, $builders);
	}


	public function testAddExcept()
	{
		$builder = $this->createBuilder(CmsArticle::getClassName());
		$builders = $builder->addExcept(['version', 'metadata'], ['topic' => ['caption' => 'Foo']]);
		Assert::count(7, $builders);
		Assert::equal('Foo', $builders['topic']->getFormComponent()->caption);
		/** @var Librette\Doctrine\Forms\Builder\ReplicatorBuilder $replicatorBuilder */
		$replicatorBuilder = $builders['attributes'];
		$builders = $replicatorBuilder->addAll();
		Assert::count(2, $builders);
		Assert::false(isset($builders['article']));
	}


	/**
	 * @param $entityName
	 * @return FormBuilder
	 */
	protected function createBuilder($entityName)
	{
		$builder = new FormBuilder($this->em->getDao($entityName)->getClassMetadata(), new Form(), $this->configuration);

		return $builder;
	}

}


\run(new FormBuilderTestCase());

<?php
namespace LibretteTests\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Librette;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\Handlers;
use LibretteTests\Doctrine\Forms\Model\CmsArticle;
use LibretteTests\Doctrine\Forms\Model\CmsUser;
use LibretteTests\Doctrine\Forms\ORMTestCase;
use Nette;
use Tester;

require_once __DIR__ . '/../../bootstrap.php';


/**
 * @author David Matějka
 * @testCase
 */
class OneToManyTestCase extends ORMTestCase
{

	/** @var EntityManager */
	protected $em;

	/** @var Configuration */
	protected $configuration;

	/** @var ClassMetadata */
	protected $meta;


	public function setUp()
	{
		$this->em = $this->createMemoryManager(FALSE);
		$oneToManyHandler = new Handlers\OneToManyHandler($this->em);
		$fieldHandler = new Handlers\FieldHandler();
		$chainHandler = new Handlers\ChainHandler([$oneToManyHandler, $fieldHandler]);
		$this->configuration = new Configuration($chainHandler);
	}


	public function testBuilderType()
	{
		$builder = $this->doHandle('phoneNumbers', [], $this->em->getClassMetadata(CmsUser::getClassName()));
		Tester\Assert::type('\Librette\Doctrine\Forms\Builder\ReplicatorBuilder', $builder);
	}


	public function testReplicator()
	{
		/** @var Librette\Doctrine\Forms\Builder\ReplicatorBuilder $builder */
		$builder = $this->doHandle('phoneNumbers', [], $this->em->getClassMetadata(CmsUser::getClassName()));
		$replicator = $builder->getFormComponent();
		Tester\Assert::type('\Kdyby\Replicator\Container', $replicator);
		Tester\Assert::type('\Nette\Forms\Container', $builder->getContainerPrototype());
		$controlBuilder = $builder->add('phoneNumber');
		Tester\Assert::type('\Librette\Doctrine\Forms\Builder\ControlBuilder', $controlBuilder);
		$builder->addAddButton();
		$builder->addRemoveButton();
		Tester\Assert::type('\Nette\Forms\Controls\SubmitButton', $replicator['add']);
		Tester\Assert::count(1, $replicator['add']->onClick);
		$count = 2;
		for ($i = 0; $i < $count; $i++) {
			$innerContainer = $replicator->createOne();
			Tester\Assert::type('\Nette\Forms\Container', $innerContainer);
			Tester\Assert::count(2, $innerContainer->getComponents());
			Tester\Assert::type('\Nette\Forms\Controls\SubmitButton', $innerContainer['remove']);
			Tester\Assert::count(1, $innerContainer['remove']->onClick);
			Tester\Assert::type('\Nette\Forms\Controls\TextInput', $innerContainer['phoneNumber']);
		}
		Tester\Assert::count($count + 1, $replicator->getComponents()); //inner containers + add button

	}


	public function testNestedContainer()
	{
		$chain = new Handlers\ChainHandler([$this->configuration->getHandler(), new Handlers\OneToOneHandler($this->em)]);
		$config = new Configuration($chain);
		/** @var Librette\Doctrine\Forms\Builder\ReplicatorBuilder $builder */
		$builder = $chain->handle('comments', [], $this->em->getClassMetadata(CmsArticle::getClassName()), $config);
		/** @var Librette\Doctrine\Forms\Builder\ContainerBuilder $containerBuilder */
		$containerBuilder = $builder->add('email');
		$containerBuilder->add('email');
		$replicator = $builder->getFormComponent();
		$container = $replicator->createOne();
		Tester\Assert::type('\Nette\Forms\Controls\TextInput', $container['email']['email']);

	}


	protected function doHandle($name, $options = [], ClassMetadata $metadata)
	{
		return $this->configuration->getHandler()->handle($name, $options, $metadata, $this->configuration);
	}
}


\run(new OneToManyTestCase());

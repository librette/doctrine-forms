<?php
namespace LibretteTests\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Librette;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\Handlers;
use LibretteTests\Doctrine\Forms\Model\CmsUser;
use LibretteTests\Doctrine\Forms\ORMTestCase;
use Nette;
use Tester;

require_once __DIR__ . '/../../bootstrap.php';


/**
 * @author David Matějka
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
		$this->meta = $this->em->getClassMetadata(CmsUser::getClassName());
	}


	public function testBuilderType()
	{
		$builder = $this->doHandle('phoneNumbers');
		Tester\Assert::type('\Librette\Doctrine\Forms\Builder\ReplicatorBuilder', $builder);
	}


	public function testReplicator()
	{
		/** @var Librette\Doctrine\Forms\Builder\ReplicatorBuilder $builder */
		$builder = $this->doHandle('phoneNumbers');
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


	protected function doHandle($name, $options = [])
	{
		return $this->configuration->getHandler()->handle($name, $options, $this->meta, $this->configuration);
	}
}


\run(new OneToManyTestCase());

<?php
namespace LibretteTests\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Librette;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\Handlers\OneToOneHandler;
use LibretteTests\Doctrine\Forms\Model\CmsUser;
use LibretteTests\Doctrine\Forms\ORMTestCase;
use Nette;
use Tester;

require_once __DIR__ . '/../../bootstrap.php';


/**
 * @author David Matějka
 * @testCase
 */
class OneToOneTestCase extends ORMTestCase
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
		$this->em = $this->createMemoryManager(FALSE);
		$this->configuration = new Configuration(new OneToOneHandler($this->em));
		$this->meta = $this->em->getClassMetadata(CmsUser::getClassName());
	}


	public function testBuilderType()
	{
		$builder = $this->doHandle('address');
		Tester\Assert::type('\Librette\Doctrine\Forms\Builder\ContainerBuilder', $builder);
	}


	public function testContainer()
	{
		/** @var Librette\Doctrine\Forms\Builder\ContainerBuilder $builder */
		$builder = $this->doHandle('address');
		Tester\Assert::type('\Nette\Forms\Container', $builder->getFormComponent());
	}


	protected function doHandle($name, $options = [])
	{
		return $this->configuration->getHandler()->handle($name, $options, $this->meta, $this->configuration);
	}
}


\run(new OneToOneTestCase());

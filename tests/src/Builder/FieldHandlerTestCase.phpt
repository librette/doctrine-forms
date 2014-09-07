<?php
namespace LibretteTests\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\ControlBuilder;
use Librette\Doctrine\Forms\Builder\Handlers\FieldHandler;
use LibretteTests\Doctrine\Forms\Model\CmsArticle;
use LibretteTests\Doctrine\Forms\ORMTestCase;
use Nette;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class FieldHandlerTestCase extends ORMTestCase
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
		$this->configuration = new Configuration(new FieldHandler());
		$this->meta = $this->em->getClassMetadata(CmsArticle::getClassName());
	}


	public function testIsBuilder()
	{
		$builder = $this->doHandle('topic');
		Assert::type('\Librette\Doctrine\Forms\Builder\ControlBuilder', $builder);
	}


	public function testTextInput()
	{
		$builder = $this->doHandle('topic');
		Assert::type('\Nette\Forms\Controls\TextInput', $builder->getComponent());
	}


	public function testTextArea()
	{
		$builder = $this->doHandle('text');
		Assert::type('\Nette\Forms\Controls\TextArea', $builder->getComponent());
	}


	public function testCheckbox()
	{
		$builder = $this->doHandle('published');
		Assert::type('\Nette\Forms\Controls\Checkbox', $builder->getComponent());
	}


	public function testLabel()
	{
		/** @var ControlBuilder $builder */
		$builder = $this->doHandle('topic');

		Assert::same('topic', $builder->getComponent()->caption);
	}


	/**
	 * @param string
	 */
	protected function doHandle($name)
	{
		return $this->configuration->getHandler()->handle($name, [], $this->meta, $this->configuration);
	}

}


run(new FieldHandlerTestCase());

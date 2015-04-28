<?php
namespace LibretteTests\Doctrine\Forms;

use Kdyby\Doctrine\EntityManager;
use Librette;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\FormBuilder;
use Librette\Doctrine\Forms\Builder\Handlers;
use LibretteTests\Doctrine\Forms\Model\CmsArticle;
use LibretteTests\Doctrine\Forms\Model\CmsArticleAttribute;
use LibretteTests\Doctrine\Forms\Model\CmsAttribute;
use LibretteTests\Doctrine\Forms\Model\CmsUser;
use Nette;
use Tester;
use Tester\Assert;


require_once __DIR__ . '/../bootstrap.php';


/**
 * @author David Matějka
 */
class BuilderAndMapperTestCase extends ORMTestCase
{

	/** @var EntityManager */
	protected $em;

	/** @var CmsUser[] */
	protected $users;

	/** @var CmsAttribute[] */
	protected $attributes;

	/** @var Configuration */
	protected $configuration;


	public function setUp()
	{
		$this->em = $this->createMemoryManager(TRUE);
		foreach (['John', 'Jack', 'Joe'] as $name) {
			$this->users[] = $user = new CmsUser($name);
			$this->em->persist($user);
		}
		foreach (['attr1', 'attr2'] as $name) {
			$this->attributes[] = $attribute = new CmsAttribute($name);
			$this->em->persist($attribute);
		}
		$this->em->flush();
		$chainHandler = new Handlers\ChainHandler([
			new Handlers\FieldHandler(),
			new Handlers\OneToOneHandler($this->em),
			new Handlers\OneToManyHandler($this->em),
			new Handlers\ToOneHandler($this->em),
			new Handlers\ManyToManyHandler($this->em),
		]);
		$this->configuration = new Configuration($chainHandler);
	}


	public function testAll()
	{
		/** @var Librette\Doctrine\Forms\FormFactory $formFactory */
		$formFactory = $this->serviceLocator->getByType('Librette\Doctrine\Forms\FormFactory');
		$article = new CmsArticle('foo');
		$this->em->persist($article);
		$this->em->flush();
		$form = $formFactory->create($article);
		$builder = new FormBuilder($this->em->getClassMetadata(CmsArticle::getClassName()), $form, $this->configuration);
		$builder->addExcept(['id', 'comments', 'version', 'metadata']);
		/** @var Librette\Doctrine\Forms\Builder\ReplicatorBuilder $replicatorBuilder */
		$replicatorBuilder = $builder['attributes'];
		$replicatorBuilder->addAll();
		$form->setValues([
			'topic'      => 'Foo',
			'text'       => 'bar',
			'user'       => $this->users[0]->id,
			'attributes' => [
				[
					'attribute' => $this->attributes[0]->id,
					'value'     => 'value 0',
				],
				[
					'attribute' => $this->attributes[1]->id,
					'value'     => 'value 1',
				]
			]]);
		$form->getMapper()->save($form);

		Assert::same('Foo', $article->topic);
		Assert::same('bar', $article->text);
		Assert::same($this->users[0], $article->user);
		/** @var CmsArticleAttribute[] $attrs */
		$attrs = iterator_to_array($article->attributes);
		Assert::same($this->attributes[0], $attrs[0]->attribute);
		Assert::same('value 0', $attrs[0]->value);
		Assert::same($this->attributes[1], $attrs[1]->attribute);
		Assert::same('value 1', $attrs[1]->value);

	}
}


\run(new BuilderAndMapperTestCase());

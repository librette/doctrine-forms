<?php
namespace LibretteTests\Doctrine\Forms\Mapper;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Kdyby\Replicator\Container;
use Librette\Doctrine\Forms\FormFactory;
use LibretteTests\Doctrine\Forms\Model\CmsArticle;
use LibretteTests\Doctrine\Forms\Model\CmsAttribute;
use LibretteTests\Doctrine\Forms\Model\CmsComment;
use LibretteTests\Doctrine\Forms\Model\CmsGroup;
use LibretteTests\Doctrine\Forms\Model\CmsUser;
use LibretteTests\Doctrine\Forms\ORMTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


/**
 * @author David Matejka
 * @testCase
 */
class MapperSaveTestCase extends ORMTestCase
{

	public function setUp()
	{

	}


	public function testField()
	{
		$this->createMemoryManager(FALSE);
		$user = new CmsUser();

		$form = $this->createForm($user);
		$form->addText('username', 'Username')
		     ->setValue('John');
		$form->getMapper()->save($form);

		Assert::same('John', $user->username);
	}


	public function testOmittedField()
	{
		$this->createMemoryManager(FALSE);
		$user = new CmsUser();
		$user->username = 'Jack';

		$form = $this->createForm($user);
		$form->addText('username', 'Username')
		     ->setValue('John')
		     ->setOmitted(TRUE);
		$form->getMapper()->save($form);

		Assert::same('Jack', $user->username);
	}


	public function testFieldArray()
	{
		$this->createMemoryManager(FALSE);
		$article = new CmsArticle();

		$form = $this->createForm($article);

		$form->addContainer('metadata')
		     ->addText('foo', 'Foo')
		     ->setValue('bar');

		$form->getMapper()->save($form);

		Assert::equal(['foo' => 'bar'], $article->metadata);
	}


	public function testToOne()
	{
		$this->createMemoryManager(FALSE);
		$article = new CmsArticle();

		$form = $this->createForm($article);
		$userContainer = $form->addContainer('user');
		$userContainer->addText('username')
		              ->setValue('john');

		Assert::null($article->user);
		$form->getMapper()->save($form);

		Assert::true($article->user instanceof CmsUser);
		Assert::same('john', $article->user->username);
	}


	public function testToOneExistingEntity()
	{
		$this->createMemoryManager(TRUE);

		/** @var EntityManager $em */
		$em = $this->serviceLocator->getByType('Kdyby\Doctrine\EntityManager');

		$user = new CmsUser('john');
		$em->persist($user);
		$em->flush();

		$article = new CmsArticle();

		$form = $this->createForm($article);
		$userContainer = $form->addContainer('user');
		$userContainer->addHidden('id')->setValue($user->id);
		$userContainer->addText('username')
		              ->setValue('jack');
		Assert::same('john', $user->username);
		Assert::null($article->user);
		$form->getMapper()->save($form);

		Assert::equal($user, $article->user);
		Assert::same('jack', $user->username);

	}


	public function testToOneByIdentifier()
	{
		$em = $this->createMemoryManager(TRUE);

		$user = new CmsUser('john');
		$em->persist($user);
		$em->flush();

		$article = new CmsArticle();

		$form = $this->createForm($article);
		$form->addSelect('user', 'User', [$user->id => $user->id])
		     ->setValue($user->id);

		Assert::null($article->user);
		$form->getMapper()->save($form);
		Assert::equal($user, $article->user);

	}


	public function testCheckboxListSave()
	{
		$em = $this->createMemoryManager(TRUE);

		$groupNames = [1 => 'foo', 'bar', 'lorem', 'ipsum'];

		$groups = [];
		foreach ($groupNames as $i => $groupName) {
			$groups[$i] = $group = new CmsGroup($groupName);
			$group->id = $i;
			$em->persist($group);
		}
		$em->flush();

		$user = new CmsUser('John');
		$user->addGroup($groups[1]);
		$user->addGroup($groups[3]);
		$form = $this->createForm($user);

		$form->addCheckboxList('groups', 'Groups', $groupNames)
		     ->setValue([2, 3]);
		$form->getMapper()->save($form);

		Assert::true($user->groups instanceof Collection);
		Assert::false($user->groups->contains($groups[1]));
		Assert::true($user->groups->contains($groups[2]));
		Assert::true($user->groups->contains($groups[3]));
		Assert::false($user->groups->contains($groups[4]));

	}


	public function testCheckboxContainerSave()
	{
		$em = $this->createMemoryManager(TRUE);
		$groupNames = [1 => 'foo', 'bar', 'lorem', 'ipsum'];

		$groups = [];
		foreach ($groupNames as $i => $groupName) {
			$groups[$i] = $group = new CmsGroup($groupName);
			$group->id = $i;
			$em->persist($group);
		}
		$em->flush();

		$user = new CmsUser('John');
		$user->addGroup($groups[1]);
		$user->addGroup($groups[3]);
		$form = $this->createForm($user);
		$groupsContainer = $form->addContainer('groups');
		foreach ($groups as $group) {
			$groupsContainer->addCheckbox($group->id, $group->name);
		}
		$groupsContainer->setValues([1 => FALSE, 2 => TRUE, 3 => TRUE, 4 => FALSE]);
		$form->getMapper()->save($form);

		Assert::true($user->groups instanceof Collection);
		Assert::false($user->groups->contains($groups[1]));
		Assert::true($user->groups->contains($groups[2]));
		Assert::true($user->groups->contains($groups[3]));
		Assert::false($user->groups->contains($groups[4]));
	}


	public function testToManySaveWithCompositePrimary()
	{
		$em = $this->createMemoryManager(TRUE);
		$attributes = [
			1 => new CmsAttribute('attribute1'),
			2 => new CmsAttribute('attribute2'),
			3 => new CmsAttribute('attribute3'),
		];
		foreach ($attributes as $attribute) {
			$em->persist($attribute);
		}

		$article = new CmsArticle('Cool article');
		$em->persist($article);

		$em->flush();
		$article->addAttribute($attributes[1], 'value 1');
		$article->addAttribute($attributes[2], 'value x');
		$em->persist($article->attributes->get(0));
		$em->persist($article->attributes->get(1));
		$em->flush();
		$form = $this->createForm($article);
		$replicator = new Container(function (\Nette\Forms\Container $container) {
			$container->addHidden('attribute');
			$container->addText('value');
		});
		$form['attributes'] = $replicator;
		$form->setValues([
			'attributes' => [
				[
					'attribute' => $attributes[2]->id,
					'value'     => 'value 2',
				],
				[
					'attribute' => $attributes[3]->id,
					'value'     => 'value 3',
				]
			]
		]);
		$form->getMapper()->save($form);

		Assert::same(2, $article->attributes->count());
		$attributesInArticle = [];
		$values = [];
		foreach ($article->attributes as $attribute) {
			$attributesInArticle[] = $attribute->attribute;
			$values[] = $attribute->value;
		}
		Assert::notContains($attributes[1], $attributesInArticle);
		Assert::contains($attributes[2], $attributesInArticle);
		Assert::contains($attributes[3], $attributesInArticle);
		Assert::same(2, count($values));
		Assert::contains('value 2', $values);
		Assert::contains('value 3', $values);
		$form->getMapper()->flush();
	}


	public function testDateHandler()
	{
		$this->createMemoryManager();
		$comment = new CmsComment();
		$form = $this->createForm($comment);
		$form->addText('added');
		$form->addText('addedDate');
		$form->addText('addedTime');

		$form->setValues([
			'added' => '2014-05-30 20:00:00',
			'addedDate' => '2014-05-30',
			'addedTime' => '20:00:00',
		]);
		$form->getMapper()->save($form);
		Assert::same('2014-05-30 20:00:00', $comment->added->format('Y-m-d H:i:s'));
		Assert::same('2014-05-30', $comment->added->format('Y-m-d'));
		Assert::same('20:00:00', $comment->added->format('H:i:s'));

		$form['added']->setOption('date-format', 'Y/m/d');
		$form['added']->setValue('2012/05/10');
		$form->getMapper()->save($form);
		Assert::same('2012-05-10', $comment->added->format('Y-m-d'));
	}


	private function createForm($entity, $offset = NULL)
	{
		/** @var FormFactory $formFactory */
		$formFactory = $this->serviceLocator->getByType('Librette\Doctrine\Forms\FormFactory');

		$mapper = $formFactory->createMapper($entity, $offset);
		$mapper->setAutoFlush(FALSE);

		$form = $formFactory->create();
		$form->setMapper($mapper);

		return $form;
	}
}


run(new MapperSaveTestCase());

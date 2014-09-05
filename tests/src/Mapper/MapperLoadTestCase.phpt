<?php
namespace LibretteTests\Doctrine\Forms\Mapper;

use Kdyby\Replicator\Container;
use Librette\Doctrine\Forms\FormFactory;
use Librette\Doctrine\Forms\Mapper\Mapper;
use Librette\Doctrine\WrappedEntity;
use LibretteTests\Doctrine\Forms\Model\CmsAddress;
use LibretteTests\Doctrine\Forms\Model\CmsArticle;
use LibretteTests\Doctrine\Forms\Model\CmsGroup;
use LibretteTests\Doctrine\Forms\Model\CmsUser;
use LibretteTests\Doctrine\Forms\ORMTestCase;
use LibretteTests\Doctrine\Forms\PresenterMock;
use Nette;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class MapperLoadTestCase extends ORMTestCase
{


	public function setUp()
	{
		$this->createMemoryManager(FALSE);
	}


	public function testSimpleLoad()
	{
		$entity = new CmsUser();
		$entity->name = 'John';
		$entity->username = 'john123';

		$form = $this->createForm($entity);
		$form->addText('name', 'Name');
		$form->addText('username', 'Username');

		$form->setParent(new PresenterMock());

		Assert::same('John', $form['name']->value);
		Assert::same('john123', $form['username']->value);

	}


	public function testArrayLoad()
	{
		$entity = new CmsArticle();
		$entity->metadata = [
			'foo'   => 'bar',
			'lorem' => 'ipsum',
		];
		$form = $this->createForm($entity);
		$metadataContainer = $form->addContainer('metadata');
		$metadataContainer->addText('foo');
		$metadataContainer->addText('lorem');
		$form->setParent(new PresenterMock());

		Assert::same('bar', $metadataContainer['foo']->value);
		Assert::same('ipsum', $metadataContainer['lorem']->value);
	}


	public function testToOneLoad()
	{
		$user = new CmsUser();
		$address = new CmsAddress();
		$address->city = 'Prague';
		$user->setAddress($address);

		$form = $this->createForm($user);
		$addressContainer = $form->addContainer('address');
		$addressContainer->addText('city', 'City');
		$form->setParent(new PresenterMock());

		Assert::same('Prague', $form['address']['city']->value);
	}


	public function testToOneLoadScalar()
	{
		$user = new CmsUser();
		$user->id = 2;
		$article = new CmsArticle();
		$article->user = $user;

		$form = $this->createForm($article);
		$form->addSelect('user', 'User', [1 => 'John', 2 => 'Doe']);
		$form->setParent(new PresenterMock());

		Assert::same(2, $form['user']->value);

	}


	public function testCheckboxListLoad()
	{
		$groupNames = [1 => 'foo', 'bar', 'lorem', 'ipsum'];

		$groups = [];
		foreach ($groupNames as $i => $groupName) {
			$groups[$i] = $group = new CmsGroup($groupName);
			$group->id = $i;
		}

		$user = new CmsUser('John');
		$user->addGroup($groups[1]);
		$user->addGroup($groups[3]);
		$form = $this->createForm($user);

		$checkboxList = $form->addCheckboxList('groups', 'Groups', $groupNames);
		$form->setParent(new PresenterMock());

		Assert::same([1, 3], $checkboxList->getValue());
	}


	public function testCheckboxContainerLoad()
	{
		$groupNames = [1 => 'foo', 'bar', 'lorem', 'ipsum'];

		$groups = [];
		foreach ($groupNames as $i => $groupName) {
			$groups[$i] = $group = new CmsGroup($groupName);
			$group->id = $i;
		}

		$user = new CmsUser('John');
		$user->addGroup($groups[1]);
		$user->addGroup($groups[3]);
		$form = $this->createForm($user);
		$groupsContainer = $form->addContainer('groups');
		foreach ($groups as $group) {
			$groupsContainer->addCheckbox($group->id, $group->name);
		}
		$form->setParent(new PresenterMock());

		Assert::same([1, 3], array_keys(array_filter($groupsContainer->getValues(TRUE))));
	}


	public function testToManyLoadWithoutReplicator()
	{
		$group = new CmsGroup('My group');
		$userNames = [1 => 'John Doe', 'Jim Smith', 'Joe Black'];
		$users = [];
		foreach ($userNames as $i => $username) {
			$users[] = $user = new CmsUser($username);
			$user->id = $i;
			$group->users->add($user);

		}

		$form = $this->createForm($group);
		$usersContainer = $form->addContainer('users');
		foreach ($users as $user) {
			$userContainer = $usersContainer->addContainer($user->id);
			$userContainer->addText('name', 'Name');
		}
		$form->setParent(new PresenterMock());
		foreach($userNames as $i =>$username) {
			Assert::same($username, $form['users'][$i]['name']->value);
		}
	}


	public function testToManyLoadWithReplicator()
	{
		$group = new CmsGroup('My group');
		$userNames = [1 => 'John Doe', 'Jim Smith', 'Joe Black'];
		$users = [];
		foreach ($userNames as $i => $username) {
			$users[] = $user = new CmsUser($username);
			$user->id = $i;
			$group->users->add($user);
		}

		$form = $this->createForm($group);
		$replicator = new Container(function(Nette\Forms\Container $container) {
			$container->addText('name', 'Name');
		});
		$form['users'] = $replicator;
		$form->setParent(new PresenterMock());
		foreach ($userNames as $i => $username) {
			Assert::same($username, $form['users'][$i]['name']->value);
		}
	}

	public function testToManyLoadWithReplicatorAndNoId()
	{
		$group = new CmsGroup('My group');
		$userNames = ['John Doe', 'Jim Smith', 'Joe Black'];
		$users = [];
		foreach ($userNames as $username) {
			$users[] = $user = new CmsUser($username);
			$group->users->add($user);
		}

		$form = $this->createForm($group);
		$replicator = new Container(function (Nette\Forms\Container $container) {
			$container->addText('name', 'Name');
		});
		$form['users'] = $replicator;
		$form->setParent(new PresenterMock());
		foreach ($userNames as $i => $username) {
			Assert::same($username, $form['users'][$i]['name']->value);
		}
	}


	public function testCustomLoadHandler()
	{
		$group = new CmsGroup();

		$form = $this->createForm($group);
		$form->addText('name');
		/** @var Mapper $mapper */
		$mapper = $form->getMapper();
		$mapper->addLoadHandler(function(WrappedEntity $wrappedEntity, Nette\Forms\Controls\BaseControl $baseControl) {
			$baseControl->setDefaultValue('My value');
			return TRUE;
		});
		$form->setParent(new PresenterMock());

		Assert::same('My value', $form['name']->value);
	}


	public function testOffset()
	{
		$group = new CmsGroup('My group');

		$form = $this->createForm($group, ['something', 'group']);
		$groupContainer = $form->addContainer('something')->addContainer('group');
		$groupContainer->addText('name', 'Name');
		$form->setParent(new PresenterMock());

		Assert::same('My group', $form['something']['group']['name']->value);

	}

	private function createForm($entity, $offset = NULL)
	{
		/** @var FormFactory $formFactory */
		$formFactory = $this->serviceLocator->getByType('Librette\Doctrine\Forms\FormFactory');

		return $formFactory->create($entity, $offset);
	}
}


run(new MapperLoadTestCase());

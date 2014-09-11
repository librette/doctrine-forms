<?php
namespace LibretteTests\Doctrine\Forms\Mapper;

use Kdyby\Replicator\Container;
use Librette;
use LibretteTests\Doctrine\Forms\Model\CmsArticle;
use LibretteTests\Doctrine\Forms\Model\CmsGroup;
use LibretteTests\Doctrine\Forms\Model\CmsUser;
use LibretteTests\Doctrine\Forms\ORMTestCase;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


/**
 * @author David Matějka
 */
class ValidationTestCase extends ORMTestCase
{

	public function setUp()
	{
	}


	public function testField()
	{
		$this->createMemoryManager(FALSE);
		$user = new CmsUser();

		$form = $this->createForm($user);
		$form->addText('username');
		$form->validate();
		$form->getMapper()->save($form);
		$errors = $form['username']->getErrors();
		Assert::same('Please fill in your username.', reset($errors));
	}


	public function testToOneContainer()
	{
		$this->createMemoryManager(FALSE);
		$user = new CmsUser();

		$form = $this->createForm($user);
		$emailContainer = $form->addContainer('email');
		$emailContainer->addText('email')->setValue('xxx');
		$form->validate();
		$form->getMapper()->save($form);
		$errors = $form['email']['email']->getErrors();
		Assert::same('This value is not a valid email address.', reset($errors));
	}


	public function testToOneSelect()
	{
		$this->createMemoryManager(FALSE);
		$article = new CmsArticle();
		$form = $this->createForm($article);
		$form->addSelect('user', 'User', [1 => 'Foo', 2 => 'Bar'])
		     ->setPrompt('---');
		$form->validate();
		$form->getMapper()->save($form);
		$errors = $form['user']->getErrors();
		Assert::same('Please select a user.', reset($errors));
	}


	public function testToManyCheckboxList()
	{
		$em = $this->createMemoryManager(TRUE);
		$user = new CmsUser();
		$user->username = 'John.xx';
		$user->name = 'John';
		$em->persist($user);
		$groups = [];
		foreach (['XX', 'YY', 'ZZ'] as $groupname) {
			$groups[] = $group = new CmsGroup();
			$group->name = $groupname;
			$em->persist($group);
			$user->addGroup($group);
		}
		$em->flush();
		$choices = [];
		foreach ($groups as $group) {
			$choices[$group->id] = $group->name;
		}
		$form = $this->createForm($user);
		$form->addCheckboxList('groups', 'Groups', $choices);
		Assert::count(3, $user->groups);
		$form['groups']->setValue([$groups[0]->id]);
		$form->validate();
		$form->getMapper()->save($form);
		Assert::count(3, $user->groups);
		$errors = $form['groups']->getErrors();
		Assert::same('Please select at least two groups.', reset($errors));
		$form['groups']->setValue([$groups[0]->id, $groups[1]->id]);
		$form->validate();
		$form->getMapper()->setExecutionStrategy(new Librette\Doctrine\Forms\Mapper\PostponedExecution()); //clean state
		$form->getMapper()->save($form);
		Assert::count(2, $user->groups);
		Assert::count(0, $form['groups']->getErrors());
	}


	public function testToManyContainer()
	{
		$em = $this->createMemoryManager(TRUE);
		$user = new CmsUser();
		$user->username = 'John.xx';
		$user->name = 'John';
		$em->persist($user);
		$groups = [];
		foreach (['XX', 'YY', 'ZZ'] as $groupname) {
			$groups[] = $group = new CmsGroup();
			$group->name = $groupname;
			$em->persist($group);
			$user->addGroup($group);
		}
		$em->flush();
		$choices = [];
		foreach ($groups as $group) {
			$choices[$group->id] = $group->name;
		}
		$form = $this->createForm($user);
		$replicator = new Container(function (Nette\Forms\Container $container) {
			$container->addHidden('id');
			$container->addText('name');
		});
		$form['groups'] = $replicator;
		Assert::count(3, $user->groups);
		$form['groups']->setValues([0 => ['id' => $groups[1]->id, 'name' => 'foo']]);
		$form->validate();
		$form->getMapper()->save($form);
		Assert::count(3, $user->groups);
		$errors = $form->getErrors();
		Assert::same('Please select at least two groups.', reset($errors));
	}


	private function createForm($entity, $offset = NULL)
	{
		/** @var Librette\Doctrine\Forms\FormFactory $formFactory */
		$formFactory = $this->serviceLocator->getByType('Librette\Doctrine\Forms\FormFactory');

		$mapper = $formFactory->createMapper($entity, $offset);
		$mapper->setAutoFlush(FALSE);
		$mapper->setValidator($this->serviceLocator->getByType('\Symfony\Component\Validator\ValidatorInterface'));
		$form = $formFactory->create();
		$form->setMapper($mapper);


		return $form;
	}


	public function testViolationsTranslation()
	{
		$this->createMemoryManager(FALSE);
		$user = new CmsUser();

		$form = $this->createForm($user);
		$form->addText('name');
		$form->validate();
		$form->getMapper()->save($form);
		$errors = $form['name']->getErrors();
		Assert::same('Please fill in your name.', reset($errors));
	}

}


\run(new ValidationTestCase());

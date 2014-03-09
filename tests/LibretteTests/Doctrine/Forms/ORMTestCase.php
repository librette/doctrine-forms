<?php
namespace LibretteTests\Doctrine\Forms;

use Doctrine\ORM\Tools\SchemaTool;
use Kdyby;
use Nette;
use Nette\Application\UI;
use Nette\PhpGenerator as Code;
use Tester;


/**
 * @author Filip Procházka <filip@prochazka.su>
 */
abstract class ORMTestCase extends Tester\TestCase
{

	/**
	 * @var \Nette\DI\Container|\SystemContainer
	 */
	protected $serviceLocator;


	/**
	 * @return Kdyby\Doctrine\EntityManager
	 */
	protected function createMemoryManager($createSchema = TRUE)
	{
		$rootDir = __DIR__ . '/../../../';

		$config = new Nette\Configurator();
		$container = $config->setTempDirectory(TEMP_DIR)
							->addConfig(__DIR__ . '/../../nette-reset.neon')
							->addConfig(__DIR__ . '/config/memory.neon')
							->addParameters(array(
				'appDir' => $rootDir,
				'wwwDir' => $rootDir,
			))
							->createContainer();
		/** @var Nette\DI\Container $container */

		$em = $container->getByType('Kdyby\Doctrine\EntityManager');
		if($createSchema) {
			/** @var Kdyby\Doctrine\EntityManager $em */

			$schemaTool = new SchemaTool($em);
			$schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());
		}

		$this->serviceLocator = $container;

		return $em;
	}

}


class PresenterMock extends UI\Presenter
{

	public function startup()
	{
		$this->terminate();
	}
}
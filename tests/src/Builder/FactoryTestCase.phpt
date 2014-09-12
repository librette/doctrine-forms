<?php
namespace LibretteTests\Doctrine\Forms\Builder;

use Librette;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\FormBuilderFactory;
use Librette\Doctrine\Forms\Builder\Handlers\ChainHandler;
use LibretteTests\Doctrine\Forms\Model\CmsArticle;
use Mockery;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


/**
 * @author David Matějka
 */
class FactoryTestCase extends Tester\TestCase
{


	public function testMapper()
	{
		$em = Mockery::mock('Kdyby\Doctrine\EntityManager')
		             ->shouldReceive('getClassMetadata')->andReturn(Mockery::mock('\Doctrine\ORM\Mapping\ClassMetadata'))
		             ->getMock();
		$factory = Mockery::mock('Librette\Forms\IFormFactory')
		                  ->shouldReceive('create')->andReturn(new Librette\Forms\Form())
		                  ->getMock();
		$mapperFactory = Mockery::mock('Librette\Doctrine\Forms\MapperFactory')
		                        ->shouldReceive('create')->andReturn(Mockery::mock('Librette\Forms\IMapper'))
		                        ->getMock();

		$factory = new FormBuilderFactory($em, $factory, new Configuration(new ChainHandler()), $mapperFactory);
		$builder = $factory->create('foo');
		Assert::null($builder->getForm()->getMapper());
		$builder = $factory->create(new CmsArticle());
		Assert::type('object', $builder->getForm()->getMapper());
	}
}


\run(new FactoryTestCase());

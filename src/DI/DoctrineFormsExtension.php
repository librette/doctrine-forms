<?php
namespace Librette\Doctrine\Forms\DI;

use Librette\Doctrine\DI\DoctrineExtension;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\PhpLiteral;

/**
 * @author David Matejka
 */
class DoctrineFormsExtension extends CompilerExtension
{

	const TAG_BUILDER_HANDLER = 'librette.doctrine.forms.builder.handler';
	const TAG_MAPPER_HANDLER = 'librette.doctrine.forms.mapper.handler';

	public $defaults = [
		'builder' => [
			'handlers' => [],
		],
		'mapper'  => [
			'handlers' => [],
			'date'     => [
				'adapter' => 'datetime',
				'formats' => [],
			],
		],
	];

	protected $defaultMapperHandlers = [
		'Librette\Doctrine\Forms\Mapper\Handlers\ToManyHandler',
		'Librette\Doctrine\Forms\Mapper\Handlers\ToOneHandler',
		'Librette\Doctrine\Forms\Mapper\Handlers\FieldHandler',
	];

	protected $defaultBuilderHandlers = [
		'Librette\Doctrine\Forms\Builder\Handlers\RootHandler',
		'Librette\Doctrine\Forms\Builder\Handlers\OneToOneHandler',
		'Librette\Doctrine\Forms\Builder\Handlers\FieldHandler',
		'Librette\Doctrine\Forms\Builder\Handlers\ManyToOneHandler',
		'Librette\Doctrine\Forms\Builder\Handlers\OneToManyHandler',
		'Librette\Doctrine\Forms\Builder\Handlers\ManyToManyHandler',
	];

	protected $dateHandlerAdapters = [
		'strftime' => 'Librette\Doctrine\Forms\Mapper\Handlers\Date\StrftimeAdapter',
		'datetime' => 'Librette\Doctrine\Forms\Mapper\Handlers\Date\DateTimeAdapter',
	];


	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		if (!count($this->compiler->getExtensions('Librette\Doctrine\DI\DoctrineExtension'))) {
			$this->compiler->addExtension('libretteDoctrine', new DoctrineExtension());
		}

		$this->configureMapper($config['mapper']);
		$this->configureBuilder($config['builder']);

	}


	protected function configureMapper($config)
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('formFactory'))
		        ->setClass('Librette\Doctrine\Forms\FormFactory');
		$builder->addDefinition($this->prefix('mapperFactory'))
		        ->setImplement('Librette\Doctrine\Forms\MapperFactory')
		        ->setArguments([new PhpLiteral('$entity'), new PhpLiteral('$offset')]);
		$chain = $builder->addDefinition($this->prefix('mapperChainHandler'))
		                 ->setClass('Librette\Doctrine\Forms\Mapper\Handlers\ChainHandler');
		if (!empty($config['date'])) {
			$adapter = $config['date']['adapter'];
			$def = $builder->addDefinition($this->prefix('dateHandlerAdapter'));
			if (isset($this->dateHandlerAdapters[$adapter])) {
				$def->setClass($this->dateHandlerAdapters[$adapter], [$config['date']['formats']]);
			} else {
				Compiler::parseService($def, $adapter);
			}
			$config['handlers'][] = 'Librette\Doctrine\Forms\Mapper\Handlers\DateHandler';
		}

		foreach (array_merge($config['handlers'], $this->defaultMapperHandlers) as $i => $handler) {
			Compiler::parseService($def = $builder->addDefinition($this->prefix("mapperHandler$i")), $handler);
			$def->setAutowired(FALSE);
			$chain->addSetup('add', [$def, FALSE]);
		}
	}


	/**
	 * @param array
	 */
	protected function configureBuilder($config)
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('formBuilderFactory'))
		        ->setClass('Librette\Doctrine\Forms\Builder\FormBuilderFactory');

		$chain = $builder->addDefinition($this->prefix('builderChainHandler'))
		                 ->setClass('Librette\Doctrine\Forms\Builder\Handlers\ChainHandler');

		foreach (array_merge($config['handlers'], $this->defaultBuilderHandlers) as $i => $handler) {
			Compiler::parseService($def = $builder->addDefinition($this->prefix("builderHandler$i")), $handler);
			$def->setAutowired(FALSE);
			$chain->addSetup('add', [$def, FALSE]);
		}
		$builder->addDefinition($this->prefix('builderConfiguration'))
		        ->setClass('Librette\Doctrine\Forms\Builder\Configuration');
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$types = [
			self::TAG_MAPPER_HANDLER  => $this->prefix('mapperChainHandler'),
			self::TAG_BUILDER_HANDLER => $this->prefix('builderChainHandler'),
		];
		foreach ($types as $tag => $serviceName) {
			$def = $builder->getDefinition($serviceName);
			foreach ($builder->findByTag($tag) as $name => $args) {
				$args = (is_array($args) ? $args : []) + ['prepend' => TRUE];
				$service = $builder->getDefinition($name);
				$service->setAutowired(FALSE);
				$def->addSetup('add', [$service, $args['prepend'] ? TRUE : FALSE]);
			}
		}
	}

}

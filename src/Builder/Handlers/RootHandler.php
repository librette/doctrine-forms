<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\FormBuilder;
use Librette\Doctrine\Forms\Builder\IHandler;
use Librette\Forms\IFormFactory;
use Nette\Object;

/**
 * @author David Matejka
 */
class RootHandler extends Object implements IHandler
{

	/** @var IFormFactory */
	protected $formFactory;


	/**
	 * @param IFormFactory $formFactory
	 */
	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


	public function handle($name, array $options, ClassMetadata $classMetadata, Configuration $configuration)
	{
		if ($name !== NULL) {
			return NULL;
		}

		return new FormBuilder($classMetadata, $this->formFactory->create(), $configuration);
	}

}

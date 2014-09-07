<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\ControlBuilder;
use Librette\Doctrine\Forms\Builder\ControlFactory;
use Librette\Doctrine\Forms\Builder\IHandler;
use Nette\Forms\Controls\ChoiceControl;
use Nette\Object;

/**
 * @author David Matejka
 */
class ManyToOneHandler extends Object implements IHandler
{

	/** @var EntityManager */
	protected $entityManager;


	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	public function handle($name, array $options, ClassMetadata $classMetadata, Configuration $configuration)
	{
		if (!$mapping = MetadataHelpers::getAssociationMapping($classMetadata, $name, ClassMetadata::MANY_TO_ONE)) {
			return NULL;
		}
		$options += ['control' => ControlFactory::SELECT_BOX, 'fill' => TRUE];
		$control = ControlFactory::create($options['control'], ['\Nette\Forms\Controls\ChoiceControl', '\Nette\Forms\Controls\HiddenField'], ControlFactory::SELECT_BOX);

		if ($options['fill'] === TRUE) {
			$dao = $this->entityManager->getDao($mapping['targetEntity']);
			$items = ChoiceHelpers::getPairs($dao, $options);
			/** @var ChoiceControl $control */
			$control->setItems($items);
		}

		return new ControlBuilder($control);
	}

}

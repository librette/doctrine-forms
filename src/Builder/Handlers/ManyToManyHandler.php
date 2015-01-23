<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\ControlBuilder;
use Librette\Doctrine\Forms\Builder\ControlFactory;
use Librette\Doctrine\Forms\Builder\IHandler;
use Nette\Forms\Controls\MultiChoiceControl;
use Nette\Object;

/**
 * @author David Matejka
 */
class ManyToManyHandler extends Object implements IHandler
{

	/** @var EntityManager */
	protected $entityManager;


	/**
	 * @param EntityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	public function handle($name, array $options, ClassMetadata $classMetadata, Configuration $configuration)
	{
		if (!$mapping = MetadataHelpers::getAssociationMapping($classMetadata, $name, ClassMetadata::MANY_TO_MANY)) {
			return NULL;
		}
		$options += ['control' => ControlFactory::MULTI_SELECT_BOX, 'fill' => TRUE];
		$control = ControlFactory::create($options['control'], ['\Nette\Forms\Controls\MultiChoiceControl'], ControlFactory::MULTI_SELECT_BOX);
		if (empty($options['caption'])) {
			$control->caption = $configuration->getLabelingStrategy()->getControlLabel($name, $classMetadata);
		} else {
			$control->caption = $options['caption'];
		}
		if ($options['fill'] === TRUE) {
			$dao = $this->entityManager->getDao($mapping['targetEntity']);
			$items = ChoiceHelpers::getPairs($dao, $options);
			/** @var MultiChoiceControl $control */
			$control->setItems($items);
		}

		return new ControlBuilder($control);
	}

}

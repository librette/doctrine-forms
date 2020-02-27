<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\ControlBuilder;
use Librette\Doctrine\Forms\Builder\ControlFactory;
use Librette\Doctrine\Forms\Builder\IHandler;
use Nette\Forms\Controls\MultiChoiceControl;
use Nette\SmartObject;

/**
 * @author David Matejka
 */
class ManyToManyHandler implements IHandler
{
	use SmartObject;
	use TChoiceHandler;

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
		$this->fillOptions($options, $control, $mapping);

		return new ControlBuilder($control);
	}


	protected function getEntityManager()
	{
		return $this->entityManager;
	}

}

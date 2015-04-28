<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Librette\Doctrine\Forms\Builder\Configuration;
use Librette\Doctrine\Forms\Builder\ControlBuilder;
use Librette\Doctrine\Forms\Builder\ControlFactory;
use Librette\Doctrine\Forms\Builder\IHandler;
use Nette\Forms\Controls\ChoiceControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\IControl;
use Nette\Object;

/**
 * @author David Matejka
 */
class ToOneHandler extends Object implements IHandler
{

	use TChoiceHandler;

	private static $allowedControls = [
		'\Nette\Forms\Controls\ChoiceControl',
		'\Nette\Forms\Controls\HiddenField'
	];

	/** @var EntityManager */
	protected $entityManager;


	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	public function handle($name, array $options, ClassMetadata $classMetadata, Configuration $configuration)
	{
		if (!$mapping = MetadataHelpers::getAssociationMapping($classMetadata, $name, [ClassMetadata::MANY_TO_ONE, ClassMetadata::ONE_TO_ONE])) {
			return NULL;
		}
		$options += ['control' => ControlFactory::SELECT_BOX, 'fill' => TRUE];
		$control = ControlFactory::create($options['control'], self::$allowedControls, ControlFactory::SELECT_BOX);
		if (empty($options['caption'])) {
			$control->caption = $configuration->getLabelingStrategy()->getControlLabel($name, $classMetadata);
		} else {
			$control->caption = $options['caption'];
		}
		if ($control instanceof ChoiceControl) {
			$this->setPrompt($options, $control, $mapping);
			$this->fillOptions($options, $control, $mapping);
		}

		return new ControlBuilder($control);
	}


	/**
	 * @param array
	 * @param IControl
	 * @param array
	 * @return void
	 */
	protected function setPrompt(array $options, $control, $mapping)
	{
		if (!$control instanceof SelectBox) {
			return;
		}
		if (!array_key_exists('prompt', $options)) {
			$joinColumn = reset($mapping['joinColumns']);
			$options['prompt'] = isset($joinColumn['nullable']) && $joinColumn['nullable'] ? '---' : NULL;
		}
		if (isset($options['prompt'])) {
			$control->setPrompt($options['prompt']);
		}
	}


	protected function getEntityManager()
	{
		return $this->entityManager;
	}

}

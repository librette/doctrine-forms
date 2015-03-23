<?php
namespace Librette\Doctrine\Forms\Builder\Handlers;

use Kdyby\Doctrine\EntityManager;
use Nette\Forms\Controls\ChoiceControl;
use Nette\Forms\Controls\MultiChoiceControl;
use Nette\Forms\IControl;

/**
 * @author David Matejka
 */
trait TChoiceHandler
{

	/**
	 * @param array
	 * @param IControl
	 * @param array associtation mapping
	 */
	protected function fillOptions(array $options, $control, $mapping)
	{
		if ($options['fill'] !== TRUE) {
			return;
		}
		$items = $this->getItems($options, $mapping);
		/** @var ChoiceControl|MultiChoiceControl $control */
		$control->setItems($items);
	}


	/**
	 * @param array
	 * @param array association mapping
	 * @return array
	 */
	protected function getItems(array $options, $mapping)
	{
		$dao = $this->getEntityManager()->getRepository($mapping['targetEntity']);
		$items = ChoiceHelpers::getPairs($dao, $options);

		return $items;
	}


	/**
	 * @return EntityManager
	 */
	protected abstract function getEntityManager();

}

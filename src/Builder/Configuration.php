<?php
namespace Librette\Doctrine\Forms\Builder;

use Nette\Object;

/**
 * @author David Matejka
 */
class Configuration extends Object
{

	/** @var IHandler */
	protected $handler;

	/** @var ILabelingStrategy */
	protected $labelingStrategy;


	function __construct(IHandler $handler, ILabelingStrategy $labelingStrategy = NULL)
	{
		$this->handler = $handler;
		$this->labelingStrategy = $labelingStrategy ?: new SimpleLabelingStrategy();
	}


	/**
	 * @return IHandler
	 */
	public function getHandler()
	{
		return $this->handler;
	}


	public function getLabelingStrategy()
	{
		return $this->labelingStrategy;
	}

}

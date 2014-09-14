<?php
namespace Librette\Doctrine\Forms\Mapper\Handlers\Date;

use Nette\Object;

/**
 * @author David Matejka
 */
class DateTimeAdapter extends Object implements IAdapter
{

	/** @var array */
	protected $defaultFormats = [];


	/**
	 * @param array associative array with keys date, time, datetime
	 */
	public function __construct($formats = [])
	{
		$this->defaultFormats = (array) $formats + ['date' => 'Y-m-d', 'time' => 'H:i:s', 'datetime' => 'Y-m-d H:i:s'];
	}


	public function format(\DateTime $datetime, $type, $format = NULL)
	{
		return $datetime->format($format ?: $this->defaultFormats[$type]);
	}


	public function parse($value, $type, $format = NULL)
	{
		return \DateTime::createFromFormat($format ?: $this->defaultFormats[$type], $value) ?: NULL;
	}

}

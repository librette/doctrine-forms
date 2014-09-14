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


	/**
	 * @param \DateTime
	 * @param string on of IAdapter constants -  DATE, TIME, DATE_TIME
	 * @param string optional user format
	 * @return string
	 */
	public function format(\DateTime $datetime, $type, $format = NULL)
	{
		return $datetime->format($format ?: $this->defaultFormats[$type]);
	}


	/**
	 * @param string
	 * @param string on of IAdapter constants -  DATE, TIME, DATE_TIME
	 * @param string optional user format
	 * @return \DateTime|null
	 */
	public function parse($value, $type, $format = NULL)
	{
		return \DateTime::createFromFormat($format ?: $this->defaultFormats[$type], $value);
	}

}

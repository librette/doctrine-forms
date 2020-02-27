<?php
namespace Librette\Doctrine\Forms\Mapper\Handlers\Date;

use Nette\SmartObject;

/**
 * @author David Matejka
 */
class StrftimeAdapter implements IAdapter
{
	use SmartObject;

	/** @var array */
	protected $formats;


	/**
	 * @param array
	 */
	public function __construct($formats = [])
	{
		$this->formats = (array) $formats + ['date' => '%x', 'time' => '%X', 'datetime' => '%x %X'];
	}


	public function format(\DateTime $datetime, $type, $format = NULL)
	{
		return strftime($format ?: $this->formats[$type], $datetime->format('U'));
	}


	public function parse($value, $type, $format = NULL)
	{
		$result = strptime($value, $format ?: $this->formats[$type]);
		if ($result === FALSE) {
			return NULL;
		}
		$result = mktime($result['tm_hour'], $result['tm_min'], min(59, $result['tm_sec']),
			$result['tm_month'] + 1, $result['tm_day'], $result['tm_year'] + 1900);
		if ($result === FALSE) {
			return NULL;
		}

		return \DateTime::createFromFormat('U', $result);
	}
}

<?php
namespace Librette\Doctrine\Forms\Mapper\Handlers\Date;

/**
 * @author David Matejka
 */
interface IAdapter
{

	const DATE = 'date';
	const TIME = 'time';
	const DATE_TIME = 'datetime';


	/**
	 * @param \DateTime
	 * @param string on of IAdapter constants -  DATE, TIME, DATE_TIME
	 * @param string optional user format
	 * @return string
	 */
	public function format(\DateTime $datetime, $type, $format = NULL);


	/**
	 * @param string
	 * @param string on of IAdapter constants -  DATE, TIME, DATE_TIME
	 * @param string optional user format
	 * @return \DateTime|null
	 */
	public function parse($value, $type, $format = NULL);

}

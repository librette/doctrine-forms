<?php
namespace Librette\Doctrine\Forms\Builder;

use Nette\Forms\IControl;

/**
 * @author David Matejka
 */
class ControlFactory
{

	const TEXT_INPUT = 'textinput';
	const TEXT_AREA = 'textarea';
	const CHECKBOX = 'checkbox';
	const SELECT_BOX = 'selectbox';
	const RADIO_LIST = 'radiolist';
	const CHECKBOX_LIST = 'checkboxlist';
	const MULTI_SELECT_BOX = 'multiselectbox';
	const HIDDEN_FIELD = 'hidden';

	public static $mapping = [
		self::TEXT_INPUT       => '\Nette\Forms\Controls\TextInput',
		self::TEXT_AREA        => '\Nette\Forms\Controls\TextArea',
		self::CHECKBOX         => '\Nette\Forms\Controls\Checkbox',
		self::SELECT_BOX       => '\Nette\Forms\Controls\SelectBox',
		self::RADIO_LIST       => '\Nette\Forms\Controls\RadioList',
		self::MULTI_SELECT_BOX => '\Nette\Forms\Controls\MultiSelectBox',
		self::CHECKBOX_LIST    => '\Nette\Forms\Controls\CheckboxList',
		self::HIDDEN_FIELD     => '\Nette\Forms\Controls\HiddenField',
	];


	/**
	 * @param string class name or identifier
	 * @param null|array
	 * @return IControl|null
	 */
	public static function create($control, $allowed = NULL, $fallback = NULL)
	{
		$control = self::getClass($control);
		if ($allowed !== NULL) {
			$allowed = array_map(['static', 'getClass'], $allowed);
			if (self::isAllowed($control, $allowed)) {
			} elseif (!$fallback) {
				return NULL;
			} elseif ($fallback) {
				$control = self::getClass($fallback);

			}
		}

		return new $control;
	}


	private static function getClass($id)
	{
		if (class_exists($id)) {
			return ltrim($id, '\\');
		}
		if (isset(self::$mapping[$id])) {
			return ltrim(self::$mapping[$id], '\\');
		}

		return NULL;
	}


	private static function isAllowed($class, $list)
	{
		foreach ([$class] + class_parents($class) + class_implements($class) as $cls) {
			if (in_array($cls, $list)) {
				return TRUE;
			}
		}

		return FALSE;
	}
}

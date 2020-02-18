<?php
namespace Librette\Doctrine\Forms\Builder;

use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Rules;

final class ControlCloneUtil
{

	private function __construct()
	{
	}


	public static function cloneControl(IComponent $control)
	{
		$newControl = clone $control;
		if ($newControl instanceof BaseControl) {
			self::deepClone($control, $newControl);
		}

		return $newControl;
	}


	/**
	 * @internal
	 */
	public static function deepClone(BaseControl $originalControl, BaseControl $control)
	{
		$clonner = function () use ($originalControl, $control) {
			/** @var BaseControl $this */
			$this->control = clone $this->control;
			$this->label = clone $this->label;
			$this->rules = ControlCloneUtil::cloneRules($originalControl, $control, $this->rules);
		};
		$clonner = $clonner->bindTo($control, BaseControl::class);
		$clonner();
	}


	/**
	 * @internal
	 */
	public static function cloneRules(BaseControl $originalControl, BaseControl $control, Rules $rules)
	{
		$newRules = clone $rules;
		$rulesClonner = function () use ($originalControl, $control, &$rulesClonner) {
			/** @var Rules $this */
			if ($this->control === $originalControl) {
				$this->control = $control;
			}
			$newRules = [];
			foreach ($this->rules as $rule) {
				$newRules[] = $newRule = clone $rule;
				if ($newRule->control === $originalControl) {
					$newRule->control = $control;
				}
				if ($rule->branch) {
					$rule->branch = ControlCloneUtil::cloneRules($originalControl, $control, $rule->branch);
				}
			}
			$this->rules = $newRules;
		};
		$rulesClonner = $rulesClonner->bindTo($newRules, Rules::class);
		$rulesClonner();

		return $newRules;
	}

}

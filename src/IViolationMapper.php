<?php
namespace Librette\Doctrine\Forms;

use Nette\Forms;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author David Matejka
 */
interface IViolationMapper
{

	/**
	 * @param ConstraintViolationInterface
	 * @param Forms\Form|Forms\IControl
	 * @return void
	 */
	public function handle(ConstraintViolationInterface $violation, $violationTarget);

}

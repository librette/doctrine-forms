<?php
namespace Librette\Doctrine\Forms\Mapper;

use Nette\Forms;
use Nette\Object;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author David Matejka
 */
class DefaultViolationMapper extends Object implements IViolationMapper
{


	public function handle(ConstraintViolationInterface $violation, $violationTarget)
	{
		$violationTarget->addError($violation->getMessage());
	}

}

<?php
namespace Librette\Doctrine\Forms\Mapper;

use Nette\Forms;
use Nette\SmartObject;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author David Matejka
 */
class DefaultViolationMapper implements IViolationMapper
{
	use SmartObject;


	public function handle(ConstraintViolationInterface $violation, $violationTarget)
	{
		$violationTarget->addError($violation->getMessage());
	}

}

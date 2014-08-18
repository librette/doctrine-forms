<?php
namespace Librette\Doctrine\Forms;

interface Exception
{

}


class UnexpectedValueException extends \UnexpectedValueException implements Exception
{


}


class ValidationException extends \RuntimeException implements Exception
{

}

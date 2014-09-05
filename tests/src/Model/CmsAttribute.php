<?php
namespace LibretteTests\Doctrine\Forms\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author David Matejka
 * @ORM\Entity
 */
class CmsAttribute
{

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $name;


	public function __construct($name)
	{
		$this->name = $name;
	}
}

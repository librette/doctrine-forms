<?php
namespace LibretteTests\Doctrine\Forms\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Description of CmsGroup
 *
 * @author robo
 * @ORM\Entity
 * @ORM\Table(name="cms_groups")
 */
class CmsGroup
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	public $id;

	/**
	 * @ORM\Column(length=50)
	 */
	public $name;

	/**
	 * @ORM\ManyToMany(targetEntity="CmsUser", mappedBy="groups")
	 * @var ArrayCollection
	 */
	public $users;


	public function __construct($name = NULL)
	{
		$this->name = $name;
		$this->users = new ArrayCollection;
	}
}

<?php
namespace LibretteTests\Doctrine\Forms\Model;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;


/**
 * @ORM\Entity
 * @ORM\Table(name="cms_phonenumbers")
 */
class CmsPhoneNumber extends BaseEntity
{

	/**
	 * @ORM\Id @ORM\Column(length=50)
	 */
	public $phoneNumber;

	/**
	 * @ORM\ManyToOne(targetEntity="CmsUser", inversedBy="phonenumbers", cascade={"merge"})
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	public $user;

}



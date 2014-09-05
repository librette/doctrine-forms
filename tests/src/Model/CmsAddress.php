<?php
namespace LibretteTests\Doctrine\Forms\Model;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;


/**
 * CmsAddress
 *
 * @author Roman S. Borschel
 * @ORM\Entity
 * @ORM\Table(name="cms_addresses")
 */
class CmsAddress extends BaseEntity
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	public $id;

	/**
	 * @ORM\Column(length=50)
	 */
	public $country;

	/**
	 * @ORM\Column(length=50)
	 */
	public $zip;

	/**
	 * @ORM\Column(length=50)
	 */
	public $city;

	/**
	 * Test field for Schema Updating Tests.
	 */
	public $street;

	/**
	 * @ORM\OneToOne(targetEntity="CmsUser", inversedBy="address")
	 * @ORM\JoinColumn(referencedColumnName="id")
	 */
	public $user;


	public function __construct($city = NULL)
	{
		$this->city = $city;
	}


	public function setUser(CmsUser $user)
	{
		if ($this->user !== $user) {
			$this->user = $user;
			$user->setAddress($this);
		}
	}

}

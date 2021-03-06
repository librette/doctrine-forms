<?php
namespace LibretteTests\Doctrine\Forms\Model;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;


/**
 * Description of CmsEmployee
 *
 * @author robo
 * @ORM\Entity
 * @ORM\Table(name="cms_employees")
 */
class CmsEmployee extends BaseEntity
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	public $id;

	/**
	 * @ORM\Column
	 */
	public $name;

	/**
	 * @ORM\OneToOne(targetEntity="CmsEmployee")
	 * @ORM\JoinColumn(name="spouse_id", referencedColumnName="id")
	 */
	public $spouse;

}

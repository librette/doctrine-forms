<?php
namespace LibretteTests\Doctrine\Forms\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Librette\Doctrine\Annotations as Librette;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * CmsEmail
 *
 * @ORM\Entity
 * @ORM\Table(name="cms_emails")
 */
class CmsEmail
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	public $id;

	/**
	 * @ORM\Column(length=250)
	 * @Assert\NotBlank()
	 * @Assert\Email()
	 */
	public $email;

	/**
	 * @ORM\OneToOne(targetEntity="CmsUser", mappedBy="email")
	 */
	public $user;

}

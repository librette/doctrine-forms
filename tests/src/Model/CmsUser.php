<?php
namespace LibretteTests\Doctrine\Forms\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Librette\Doctrine\Annotations as Librette;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="cms_users")
 */
class CmsUser
{

	/**
	 * @ORM\Id @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	public $id;

	/**
	 * @ORM\Column(type="string", length=50, nullable=true)
	 */
	public $status;

	/**
	 * @ORM\Column(type="string", length=255, unique=true)
	 * @Assert\NotBlank(message="Please fill in your username.")
	 */
	public $username;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank(message="user.name.notBlank")
	 */
	public $name;

	/**
	 * @ORM\OneToMany(targetEntity="CmsPhoneNumber", mappedBy="user", cascade={"persist", "merge"}, orphanRemoval=true)
	 * @Librette\ManipulateMethods(add="addPhoneNumber", remove="removePhoneNumber")
	 */
	public $phoneNumbers;

	/**
	 * @ORM\OneToMany(targetEntity="CmsArticle", mappedBy="user", cascade={"detach"})
	 */
	public $articles;

	/**
	 * @ORM\OneToOne(targetEntity="CmsAddress", mappedBy="user", cascade={"persist"}, orphanRemoval=true)
	 */
	public $address;

	/**
	 * @ORM\OneToOne(targetEntity="CmsEmail", inversedBy="user", cascade={"persist"}, orphanRemoval=true)
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
	 */
	public $email;

	/**
	 * @ORM\ManyToMany(targetEntity="CmsGroup", inversedBy="users", cascade={"persist", "merge", "detach"})
	 * @ORM\JoinTable(name="cms_users_groups",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
	 *      )
	 * @Librette\ManipulateMethods(add="addGroup")
	 * @Assert\Count(min="2", minMessage="Please select at least two groups.")
	 */
	public $groups;

	public $nonPersistedProperty;

	public $nonPersistedPropertyObject;


	public function __construct($name = NULL)
	{
		$this->name = $this->username = $name;
		$this->phoneNumbers = new ArrayCollection;
		$this->articles = new ArrayCollection;
		$this->groups = new ArrayCollection;
	}


	/**
	 * Adds a phone number to the user.
	 *
	 * @param CmsPhoneNumber $phone
	 */
	public function addPhoneNumber(CmsPhoneNumber $phone)
	{
		$this->phoneNumbers[] = $phone;
		$phone->user = $this;
	}


	public function addGroup(CmsGroup $group)
	{
		$this->groups[] = $group;
		$group->users->add($this);
	}


	public function removePhoneNumber(CmsPhoneNumber $phoneNumber)
	{
		if ($this->phoneNumbers->contains($phoneNumber)) {
			$this->phoneNumbers->removeElement($phoneNumber);
			$phoneNumber->user = NULL;

			return TRUE;
		}

		return FALSE;
	}


	public function setAddress(CmsAddress $address)
	{
		if ($this->address !== $address) {
			$this->address = $address;
			$address->setUser($this);
		}
	}


	public function setEmail(CmsEmail $email = NULL)
	{
		if ($this->email !== $email) {
			$this->email = $email;

			if ($email) {
				$email->user = $this;
			}
		}
	}


	public function getCustomizedUsername()
	{
		return 'username: ' . $this->username;
	}


	public function setMyName($name)
	{
		$this->name = 'x' . $name;
	}
}

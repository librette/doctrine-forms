<?php
namespace LibretteTests\Doctrine\Forms;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Librette\Doctrine\Annotations as Librette;


/**
 * CmsAddress
 *
 * @author Roman S. Borschel
 * @ORM\Entity
 * @ORM\Table(name="cms_addresses")
 */
class CmsAddress
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id @ORM\GeneratedValue
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


/**
 * @ORM\Entity
 * @ORM\Table(name="cms_articles")
 */
class CmsArticle
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	public $topic;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	public $text;

	/**
	 * @ORM\ManyToOne(targetEntity="CmsUser", inversedBy="articles")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 * @var CmsUser
	 */
	public $user;

	/**
	 * @ORM\OneToMany(targetEntity="CmsComment", mappedBy="article")
	 * @Librette\ManipulateMethods(add="addComment")
	 */
	public $comments;

	/**
	 * @ORM\Version @ORM\Column(type="integer")
	 */
	public $version;

	/**
	 * @var CmsArticleAttribute[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="CmsArticleAttribute", mappedBy="article", cascade={"ALL"})
	 */
	public $attributes;

	/**
	 * @var array
	 * @ORM\Column(type="array")
	 */
	public $metadata = array();

	public function __construct($topic = NULL)
	{
		$this->comments = new ArrayCollection();
		$this->attributes = new ArrayCollection();
		$this->topic = $topic;
	}


	public function addComment(CmsComment $comment)
	{
		$this->comments[] = $comment;
		$comment->article = $this;
	}

	public function addAttribute(CmsAttribute $attribute, $value)
	{
		$this->attributes->add(new CmsArticleAttribute($this, $attribute, $value));
	}
}


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


/**
 * @author David Matejka
 * @ORM\Entity
 */
class CmsArticleAttribute
{

	/**
	 * @var string
	 * @ORM\ManyToOne(targetEntity="CmsArticle", inversedBy="attributes")
	 * @ORM\Id
	 */
	public $article;

	/**
	 * @var CmsAttribute
	 * @ORM\ManyToOne(targetEntity="CmsAttribute")
	 * @ORM\Id
	 */
	public $attribute;

	/**
	 * @var mixed
	 * @ORM\Column(type="string")
	 */
	public $value;

	public function __construct(CmsArticle $article = NULL, CmsAttribute $attribute = NULL, $value = NULL)
	{
		$this->article = $article;
		$this->attribute = $attribute;
		$this->value = $value;
	}
}


/**
 * @ORM\Entity
 * @ORM\Table(name="cms_comments")
 */
class CmsComment
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	public $topic;

	/**
	 * @ORM\Column(type="string")
	 */
	public $text;

	/**
	 * @ORM\ManyToOne(targetEntity="CmsArticle", inversedBy="comments")
	 * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
	 */
	public $article;


	public function __toString()
	{
		return __CLASS__ . "[id=" . $this->id . "]";
	}
}


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
	 * @ORM\Id @ORM\GeneratedValue
	 */
	public $id;

	/**
	 * @ORM\Column(length=250)
	 */
	public $email;

	/**
	 * @ORM\OneToOne(targetEntity="CmsUser", mappedBy="email")
	 */
	public $user;

}


/**
 * Description of CmsEmployee
 *
 * @author robo
 * @ORM\Entity
 * @ORM\Table(name="cms_employees")
 */
class CmsEmployee
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


/**
 * @ORM\Entity
 * @ORM\Table(name="cms_phonenumbers")
 */
class CmsPhoneNumber
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
	 */
	public $username;

	/**
	 * @ORM\Column(type="string", length=255)
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
		if($this->phoneNumbers->contains($phoneNumber)) {
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

<?php
namespace LibretteTests\Doctrine\Forms\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Librette\Doctrine\Annotations as Librette;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="cms_articles")
 */
class CmsArticle extends BaseEntity
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
	 * @Assert\NotBlank(message="Please select a user.")
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
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	public $published = FALSE;

	/**
	 * @var array
	 * @ORM\Column(type="array")
	 */
	public $metadata = [];


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



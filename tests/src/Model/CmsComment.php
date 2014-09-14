<?php
namespace LibretteTests\Doctrine\Forms\Model;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;


/**
 * @ORM\Entity
 * @ORM\Table(name="cms_comments")
 */
class CmsComment extends BaseEntity
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
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
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	public $added;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="date")
	 */
	public $addedDate;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="time")
	 */
	public $addedTime;

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


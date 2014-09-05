<?php
namespace LibretteTests\Doctrine\Forms\Model;

use Doctrine\ORM\Mapping as ORM;


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

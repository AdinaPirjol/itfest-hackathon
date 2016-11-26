<?php
/**
 * Created by PhpStorm.
 * User: nnao9_000
 * Date: 11/26/2016
 * Time: 11:27 AM
 */

namespace Project\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Project\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="rating")
 * @ORM\Entity(repositoryClass="Project\AppBundle\Repository\RatingRepository")
 */
class Rating
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     * @ORM\Column(type="integer", length=255)
     */
    protected $rating;

    /**
     * @var Comment
     * @ORM\ManyToOne(targetEntity="Project\AppBundle\Entity\Comment", inversedBy="ratings", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id")
     */
    protected $comment;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param Comment $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }



}
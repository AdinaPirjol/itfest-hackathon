<?php
/**
 * Created by PhpStorm.
 * User: nnao9_000
 * Date: 11/26/2016
 * Time: 11:07 AM
 */

namespace Project\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Project\UserBundle\Entity\StudentGroup;

/**
 * @ORM\Entity
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="Project\AppBundle\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", name="comment", length=16)
     */
    protected $comment;

    /**
     * @var Attachment[]
     * @ORM\OneToMany(targetEntity="Project\AppBundle\Entity\Attachment", fetch="EXTRA_LAZY", mappedBy="attachment", cascade={"persist"})
     */
    protected $attachments;

    /**
     * @var Event
     * @ORM\ManyToOne(targetEntity="Project\AppBundle\Entity\Event", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    protected $event;

    /**
     * @var Rating[]
     * @ORM\OneToMany(targetEntity="Project\AppBundle\Entity\Rating", fetch="EXTRA_LAZY", mappedBy="comment", cascade={"persist"})
     */
    protected $ratings;

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
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param mixed $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }



}
<?php

namespace Project\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Project\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="notification")
 * @ORM\Entity(repositoryClass="Project\AppBundle\Repository\NotificationRepository")
 */
class Notification
{
    const STATUS_UNSEEN = 0;
    const STATUS_SEEN = 1;

    const TYPE_IMPORT_ERROR = 'import_error';
    const TYPE_IMPORT_SUCCESS = 'import_success';
    const TYPE_APPLY = 'apply_project';
    const TYPE_ACCEPT_PROJECT = 'accept_project';
    const TYPE_REJECT_PROJECT = 'reject_project';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=16)
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(name="message", type="string", length=255)
     */
    protected $message;

    /**
     * @var User
     * @ORM\OneToOne(targetEntity="Project\UserBundle\Entity\User", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var boolean
     * @ORM\Column(name="status", type="boolean")
     */
    protected $status = self::STATUS_UNSEEN;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_created", type="datetime")
     */
    protected $dateCreated;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Notification
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Notification
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set status
     *
     * @param boolean $status
     *
     * @return Notification
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return Notification
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
}

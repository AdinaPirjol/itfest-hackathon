<?php
/**
 * Created by PhpStorm.
 * User: nnao9_000
 * Date: 11/26/2016
 * Time: 10:57 AM
 */

namespace Project\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Project\UserBundle\Entity\StudentGroup;

/**
 * @ORM\Entity
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="Project\AppBundle\Repository\EventRepository")
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Course
     * @ORM\ManyToOne(targetEntity="Project\AppBundle\Entity\Course", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="course_id", referencedColumnName="id")
     */
    protected $course;

    /**
     * @var Classroom
     * @ORM\ManyToOne(targetEntity="Project\AppBundle\Entity\Course", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="classroom_id", referencedColumnName="id")
     */
    protected $classroom;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $startDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @var string
     * @ORM\Column(name="recurrenceType", type="string", columnDefinition="enum('daily', 'weekly', 'monthly', 'none')")
     */
    protected $recurrenceType;

    /**
     * @var Comment[]
     * @ORM\OneToMany(targetEntity="Project\AppBundle\Entity\Comment", fetch="EXTRA_LAZY", mappedBy="comment", cascade={"persist"})
     */
    protected $comments;

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
     * @return Course
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param Course $course
     */
    public function setCourse($course)
    {
        $this->course = $course;
    }

    /**
     * @return Classroom
     */
    public function getClassroom()
    {
        return $this->classroom;
    }

    /**
     * @param Classroom $classroom
     */
    public function setClassroom($classroom)
    {
        $this->classroom = $classroom;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return boolean
     */
    public function isRecurrent()
    {
        return $this->recurrent;
    }

    /**
     * @param boolean $recurrent
     */
    public function setRecurrent($recurrent)
    {
        $this->recurrent = $recurrent;
    }

    /**
     * @return mixed
     */
    public function getRecurrenceType()
    {
        return $this->recurrenceType;
    }

    /**
     * @param mixed $recurrenceType
     */
    public function setRecurrenceType($recurrenceType)
    {
        $this->recurrenceType = $recurrenceType;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Comment[] $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

}
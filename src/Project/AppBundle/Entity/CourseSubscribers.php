<?php
/**
 * Created by PhpStorm.
 * User: nnao9_000
 * Date: 11/26/2016
 * Time: 11:24 AM
 */

namespace Project\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Project\UserBundle\Entity\StudentGroup;
use Project\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="course_subscribers")
 * @ORM\Entity(repositoryClass="Project\AppBundle\Repository\CourseSubscribersRepository")
 */
class CourseSubscribers
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
     * @var User
     * @ORM\ManyToOne(targetEntity="Project\UserBundle\Entity\User", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="student_id", referencedColumnName="id")
     */
    protected $student;

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
     * @return User
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * @param User $student
     */
    public function setStudent($student)
    {
        $this->student = $student;
    }

}
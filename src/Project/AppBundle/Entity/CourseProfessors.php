<?php
/**
 * Created by PhpStorm.
 * User: nnao9_000
 * Date: 11/26/2016
 * Time: 11:48 AM
 */

namespace Project\AppBundle\Entity;
use Project\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="course_professors")
 * @ORM\Entity(repositoryClass="Project\AppBundle\Repository\CourseProfessorsRepository")
 */
class CourseProfessors
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
     * @ORM\JoinColumn(name="professor_id", referencedColumnName="id")
     */
    protected $professor;

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
    public function getProfessor()
    {
        return $this->professor;
    }

    /**
     * @param User $professor
     */
    public function setProfessor($professor)
    {
        $this->professor = $professor;
    }


}
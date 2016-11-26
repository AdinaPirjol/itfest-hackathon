<?php

namespace Project\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Project\UserBundle\Entity\StudentGroup;
use Project\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="course")
 * @ORM\Entity(repositoryClass="Project\AppBundle\Repository\CourseRepository")
 */
class Course
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", name="name", length=128)
     */
    protected $name;

    /**
     * @var User[]
     * @ORM\OneToMany(targetEntity="Project\UserBundle\Entity\User", fetch="EXTRA_LAZY", mappedBy="courses", cascade={"persist"})
     */
    protected $professors;

    /**
     * @var College
     * @ORM\ManyToOne(targetEntity="Project\AppBundle\Entity\College", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="college_id", referencedColumnName="id")
     */
    protected $college;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return StudentGroup
     */
    public function getStudentGroup()
    {
        return $this->studentGroup;
    }

    /**
     * @param StudentGroup $studentGroup
     */
    public function setStudentGroup($studentGroup)
    {
        $this->studentGroup = $studentGroup;
    }
}
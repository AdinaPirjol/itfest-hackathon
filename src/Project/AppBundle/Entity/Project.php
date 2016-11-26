<?php

namespace Project\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Project\UserBundle\Entity\StudentStream;
use Project\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="Project\AppBundle\Repository\ProjectRepository")
 */
class Project
{
    const STATUS_ACTIVE = true;
    const STATUS_INACTIVE = false;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    protected $name;


    /**
     * @var string
     * @ORM\Column(name="name_ro", type="string", length=255, unique=true)
     */
    protected $nameRo;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Project\UserBundle\Entity\User", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="professor_id", referencedColumnName="id")
     */
    protected $professor;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * @var StudentStream
     * @ORM\ManyToOne(targetEntity="Project\UserBundle\Entity\StudentStream", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="stream_id", referencedColumnName="id")
     */
    protected $stream;

    /**
     * @var int
     * @ORM\Column(name="student_no", type="integer", nullable=false)
     */
    protected $studentNo = 0;

    /**
     * @var int
     * @ORM\Column(name="remaining_student_no", type="integer", nullable=false)
     */
    protected $remainingStudentNo = 0;

    /**
     * @var boolean
     * @ORM\Column(name="status", type="boolean")
     */
    protected $status = true;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_created", type="datetime")
     */
    protected $dateCreated;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_modified", type="datetime")
     */
    protected $dateModified;

    /**
     * @var ProjectStudent[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Project\AppBundle\Entity\ProjectStudent", fetch="EXTRA_LAZY", mappedBy="project", cascade={"remove"})
     */
    protected $students;

    /**
     * @var TagProject[]
     * @ORM\OneToMany(targetEntity="Project\AppBundle\Entity\TagProject", fetch="EXTRA_LAZY", mappedBy="project", cascade={"persist"})
     */
    protected $tagProjects = [];

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameRo()
    {
        return $this->nameRo;
    }

    /**
     * @param string $nameRo
     * @return $this
     */
    public function setNameRo($nameRo)
    {
        $this->nameRo = $nameRo;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return StudentStream
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param StudentStream $stream
     * @return $this
     */
    public function setStream($stream)
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $created
     * @return $this
     */
    public function setCreated($created)
    {
        $this->dateCreated = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->dateModified;
    }

    /**
     * @param \DateTime $modified
     * @return $this
     */
    public function setModified($modified)
    {
        $this->dateModified = $modified;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param boolean $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
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
     * @return $this
     */
    public function setProfessor($professor)
    {
        $this->professor = $professor;

        return $this;
    }

    /**
     * @return int
     */
    public function getStudentNo()
    {
        return $this->studentNo;
    }

    /**
     * @param int $studentNo
     * @return $this
     */
    public function setStudentNo($studentNo)
    {
        $this->studentNo = $studentNo;

        return $this;
    }

    /**
     * @return int
     */
    public function getRemainingStudentNo()
    {
        return $this->remainingStudentNo;
    }

    /**
     * @param int $remainingStudentNo
     * @return $this
     */
    public function setRemainingStudentNo($remainingStudentNo)
    {
        $this->remainingStudentNo = $remainingStudentNo;

        return $this;
    }

    public function incrementRemainingStudentNo()
    {
        $this->remainingStudentNo++;
    }

    public function decrementRemainingStudentNo()
    {
        $this->remainingStudentNo--;
    }

    /**
     * @return ArrayCollection|ProjectStudent[]
     */
    public function getStudents()
    {
        return $this->students;
    }

    /**
     * @param ProjectStudent[]|ArrayCollection $students
     * @return $this
     */
    public function setStudents($students)
    {
        $this->students = $students;

        return $this;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->students = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return Project
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

    /**
     * Set dateModified
     *
     * @param \DateTime $dateModified
     *
     * @return Project
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified
     *
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * Add student
     *
     * @param \Project\AppBundle\Entity\ProjectStudent $student
     *
     * @return Project
     */
    public function addStudent(\Project\AppBundle\Entity\ProjectStudent $student)
    {
        $this->students[] = $student;

        return $this;
    }

    /**
     * Remove student
     *
     * @param \Project\AppBundle\Entity\ProjectStudent $student
     */
    public function removeStudent(\Project\AppBundle\Entity\ProjectStudent $student)
    {
        $this->students->removeElement($student);
    }

    /**
     * Add tagProject
     *
     * @param \Project\AppBundle\Entity\TagProject $tagProject
     *
     * @return Project
     */
    public function addTagProject(\Project\AppBundle\Entity\TagProject $tagProject)
    {
        $this->tagProjects[] = $tagProject;

        return $this;
    }

    /**
     * Remove tagProject
     *
     * @param \Project\AppBundle\Entity\TagProject $tagProject
     */
    public function removeTagProject(\Project\AppBundle\Entity\TagProject $tagProject)
    {
        $this->tagProjects->removeElement($tagProject);
    }

    /**
     * Get tagProjects
     *
     * @return \Doctrine\Common\Collections\Collection|TagProject[]
     */
    public function getTagProjects()
    {
        return $this->tagProjects;
    }
}

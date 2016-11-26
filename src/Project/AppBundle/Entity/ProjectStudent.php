<?php

namespace Project\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Project\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="project_student")
 * @ORM\Entity(repositoryClass="Project\AppBundle\Repository\ProjectStudentRepository")
 */
class ProjectStudent
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_INVALIDATED = 'invalidated';

    const REPORT_YES_STUDENTS = 1;
    const REPORT_NO_STUDENTS = 0;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Project
     * @ORM\ManyToOne(targetEntity="Project\AppBundle\Entity\Project", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="project", referencedColumnName="id")
     */
    protected $project;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Project\UserBundle\Entity\User", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="student", referencedColumnName="id")
     */
    protected $student;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", columnDefinition="enum('pending', 'accepted', 'rejected', 'invalidated')")
     */
    protected $status = ProjectStudent::STATUS_PENDING;

    /**
     * @param Project $project
     * @return $this
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param User $student
     * @return $this
     */
    public function setStudent($student)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * @return User
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
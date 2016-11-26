<?php

namespace Project\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="student_group")
 * @ORM\Entity(repositoryClass="Project\UserBundle\Repository\StudentGroupRepository")
 */
class StudentGroup
{
    const STREAM_ENGLISH = 'engleza';
    const STREAM_FRENCH = 'franceza';
    const STREAM_GERMAN = 'germana';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var String
     * @ORM\Column(name="group_name", type="string", length=32, nullable=false, unique=true)
     */
    protected $groupName;

    /**
     * @var StudentStream
     * @ORM\ManyToOne(targetEntity="Project\UserBundle\Entity\StudentStream", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="student_stream_id", referencedColumnName="id", nullable=false)
     */
    protected $stream;

    /**
     * @var String
     * @ORM\Column(name="specialisation", type="string", length=64, nullable=false)
     */
    protected $specialisation;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $groupName
     * @return StudentGroup
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * @param string $stream
     * @return StudentGroup
     */
    public function setStream($stream)
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * @return string
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Set specialisation
     *
     * @param string $specialisation
     *
     * @return StudentGroup
     */
    public function setSpecialisation($specialisation)
    {
        $this->specialisation = $specialisation;

        return $this;
    }

    /**
     * Get specialisation
     *
     * @return string
     */
    public function getSpecialisation()
    {
        return $this->specialisation;
    }
}

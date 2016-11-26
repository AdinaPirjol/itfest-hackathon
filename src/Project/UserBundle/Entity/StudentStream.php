<?php

namespace Project\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="student_stream")
 * @ORM\Entity(repositoryClass="Project\UserBundle\Repository\StudentStreamRepository")
 */
class StudentStream
{
    const STREAM_ENGLISH = 'engleza';
    const STREAM_FRENCH = 'franceza';
    const STREAM_GERMAN = 'germana';

    const STREAM_ENGLISH_ID = 1;
    const STREAM_FRENCH_ID = 2;
    const STUDENT_GERMAN_ID = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var String
     * @ORM\Column(name="stream", type="string", length=32, nullable=false)
     */
    protected $streamName;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $streamName
     * @return $this
     */
    public function setStreamName($streamName)
    {
        $this->streamName = $streamName;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreamName()
    {
        return $this->streamName;
    }
}

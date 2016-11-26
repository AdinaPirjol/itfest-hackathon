<?php

namespace Project\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Project\UserBundle\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=128, nullable=false)
     */
    protected $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=128, nullable=false)
     */
    protected $lastName;

    /**
     * @var UserCredentials
     * @ORM\OneToOne(targetEntity="Project\UserBundle\Entity\UserCredentials", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="user_credentials_id", referencedColumnName="id", nullable=false)
     */
    protected $userCredentials;

    /**
     * @var UserType
     * @ORM\ManyToOne(targetEntity="Project\UserBundle\Entity\UserType", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="user_type", referencedColumnName="id", nullable=false)
     */
    protected $userType;

    /**
     * @var String
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @var String
     * @ORM\Column(name="phone_number", type="string", length=32, nullable=true)
     */
    protected $phoneNumber;

    /**
     * @var StudentGroup
     * @ORM\ManyToOne(targetEntity="Project\UserBundle\Entity\StudentGroup", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="student_group_id", referencedColumnName="id")
     */
    protected $group;

    /**
     * @var string
     * @ORM\Column(name="preferred_locale", type="string", columnDefinition="enum('ro', 'en', 'fr', 'de')")
     */
    protected $preferredLocale = 'en';

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return String
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param String $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return String
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param String $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return UserCredentials
     */
    public function getUserCredentials()
    {
        return $this->userCredentials;
    }

    /**
     * @param UserCredentials $userCredentials
     * @return $this
     */
    public function setUserCredentials($userCredentials)
    {
        $this->userCredentials = $userCredentials;

        return $this;
    }

    /**
     * @return String
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param String $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return String
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return $this
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return UserType
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @param UserType $userType
     * @return User
     */
    public function setUserType(UserType $userType)
    {
        $this->userType = $userType;

        return $this;
    }

    /**
     * @return StudentGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param StudentGroup $group
     * @return $this
     */
    public function setGroup(StudentGroup $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Set preferredLocale
     *
     * @param string $preferredLocale
     *
     * @return User
     */
    public function setPreferredLocale($preferredLocale)
    {
        $this->preferredLocale = $preferredLocale;

        return $this;
    }

    /**
     * Get preferredLocale
     *
     * @return string
     */
    public function getPreferredLocale()
    {
        return $this->preferredLocale;
    }
}

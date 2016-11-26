<?php

namespace Project\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_type")
 * @ORM\Entity(repositoryClass="Project\UserBundle\Repository\UserTypeRepository")
 */
class UserType
{
    const ROLE_USER = "ROLE_USER";
    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_SUPER_ADMIN = "ROLE_SUPER_ADMIN";

    const ROLE_USER_ID = 1;
    const ROLE_ADMIN_ID = 2;
    const ROLE_SUPER_ADMIN_ID = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var String
     * @ORM\Column(name="description", type="string", length=64)
     */
    protected $roleType = self::ROLE_USER;

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
     * Set roleType
     *
     * @param string $roleType
     *
     * @return UserType
     */
    public function setRoleType($roleType)
    {
        $this->roleType = $roleType;

        return $this;
    }

    /**
     * Get roleType
     *
     * @return string
     */
    public function getRoleType()
    {
        return $this->roleType;
    }
}

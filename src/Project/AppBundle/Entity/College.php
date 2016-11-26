<?php
/**
 * Created by PhpStorm.
 * User: nnao9_000
 * Date: 11/26/2016
 * Time: 12:06 PM
 */

namespace Project\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Project\UserBundle\Entity\StudentGroup;

/**
 * @ORM\Entity
 * @ORM\Table(name="college")
 * @ORM\Entity(repositoryClass="Project\AppBundle\Repository\CollegeRepository")
 */
class College
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", name="name", length=16)
     */
    protected $name;

    /**
     * @var Course[]
     * @ORM\OneToMany(targetEntity="Project\AppBundle\Entity\Course", fetch="EXTRA_LAZY", mappedBy="college", cascade={"persist"})
     */
    protected $courses;

}
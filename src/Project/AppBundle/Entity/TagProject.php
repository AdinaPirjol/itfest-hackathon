<?php

namespace Project\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tag_project")
 * @ORM\Entity(repositoryClass="Project\AppBundle\Repository\TagProjectRepository")
 */
class TagProject
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Project
     * @ORM\ManyToOne(targetEntity="Project\AppBundle\Entity\Project", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    protected $project;

    /**
     * @var Tag
     * @ORM\ManyToOne(targetEntity="Project\AppBundle\Entity\Tag", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     */
    protected $tag;

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
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

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
     * @return Tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param Tag $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }


}
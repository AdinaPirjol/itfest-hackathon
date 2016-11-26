<?php

namespace Project\AppBundle\Form\Type;

use Project\AppBundle\Entity\Course;
use Project\AppBundle\Validator\Constraints\Name;
use Project\UserBundle\Entity\StudentGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;

class CreateCourseType extends AbstractType
{
    const ID = 'app.create_course_form';

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /**
     * Constructor
     *
     * @param Doctrine $doctrine
     */
    public function __construct(Doctrine $doctrine)
    {
        $this->em = $doctrine->getManager();
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $options['translator'];
        /** @var Course $course */
        $course = $options['course'];

        $builder
            ->add(
                'coursename',
                'text',
                [
                    'label' => $translator->trans('course.create.coursename'),
                    'data' => $course->getName(),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 5, 'max' => 255])
                    ]
                ]
            )
            ->add(
                'group',
                'choice',
                array(
                    'choices' => $this->getGroups(),
                    'label' => $translator->trans('course.create.group'),
                    'empty_value' => $translator->trans('course.create.all_groups'),
                    'required' => true,
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(
                array(
                    'translator',
                    'course'
                )
            )
            ->setAllowedTypes(
                array(
                    'translator' => array(
                        'Symfony\Bundle\FrameworkBundle\Translation\Translator',
                        'Symfony\Component\Translation\DataCollectorTranslator'
                    ),
                    'course' => 'Project\AppBundle\Entity\Course'
                )
            );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'createGroup';
    }

    public function getGroups()
    {
        $er = $this->em->getRepository('UserBundle:StudentGroup');
        $results = $er->createQueryBuilder('c')
            ->getQuery()
            ->getResult();
        $groups = array();
        /** @var StudentGroup $group */
        foreach ($results as $group) {
            $groups[$group->getGroupName()] = $group->getGroupName();
        }
        return $groups;
    }
}
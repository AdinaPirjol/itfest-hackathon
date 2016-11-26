<?php

namespace Project\AppBundle\Form\Type;

use Project\AppBundle\Entity\College;
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
                'name',
                'text',
                [
                    'label' => 'Nume curs',
                    'data' => $course->getName(),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 2, 'max' => 255])
                    ]
                ]
            )
            ->add(
                'college',
                'text',
                array(
                    'label' => 'Universitate',
                    'data' => $course->getCollege() != null ? $course->getCollege()->getName() : null,
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 2, 'max' => 255])
                    ]
                )
            )
            ->add(
                'moderator',
                'choice',
                array(
                    'choices' => $options['courseProf'],
                    'label' => 'Moderatori',
                    'multiple' => true,
                    'required' => false,
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
                    'course',
                    'courseProf'
                )
            )
            ->setAllowedTypes(
                array(
                    'translator' => array(
                        'Symfony\Bundle\FrameworkBundle\Translation\Translator',
                        'Symfony\Component\Translation\DataCollectorTranslator'
                    ),
                    'course' => 'Project\AppBundle\Entity\Course',
                    'courseProf' => 'array'
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


}
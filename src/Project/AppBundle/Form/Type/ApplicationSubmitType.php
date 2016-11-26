<?php

namespace Project\AppBundle\Form\Type;

use Project\AppBundle\Entity\Course;
use Project\AppBundle\Validator\Constraints\Name;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApplicationSubmitType extends AbstractType
{

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
        /** @var Translator $translator */
        $translator = $options['translator'];
        $filterData = $options['filterData'];

        $builder
            ->add(
                'design',
                'text',
                array(
                    'label' => $translator->trans('application.submit.design'),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 3))
                    )
                )
            )
            ->add(
                'contribution',
                'text',
                array(
                    'label' => $translator->trans('application.submit.contribution'),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 3))
                    )
                )
            )
            ->add(
                'graphics',
                'text',
                array(
                    'label' => $translator->trans('application.submit.graphics'),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 3))
                    )
                )
            )
            ->add(
                'knowledge',
                'choice',
                array(
                    'label' => $translator->trans('application.submit.knowledge'),
                    'required' => true,
                    'multiple' => true,
                    'choices' => $this->getGroups($filterData['group']),
                    'expanded' => true,
                    'error_bubbling' => true,
                )
            )
            ->add(
                'environment',
                'text',
                array(
                    'label' => $translator->trans('application.submit.environment'),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 3))
                    )
                )
            )
            ->add(
                'serve',
                'text',
                array(
                    'label' => $translator->trans('application.submit.serve'),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 3))
                    )
                )
            )
            ->add(
                'proftitle',
                'text',
                array(
                    'label' => $translator->trans('application.submit.proftitle'),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 3))
                    )
                )
            )
            ->add(
                'preparation',
                'datetime',
                array(
                    'label' => $translator->trans('application.submit.date'),
                    'required' => true,
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-datepicker'],
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new DateTime()
                    )
                )
            )
            ->add(
                'department',
                'text',
                array(
                    'label' => $translator->trans('application.submit.department'),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'data' =>  $filterData['specialisation'],
                    'read_only' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 3))
                    )
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(
                array(
                    'translator',
                    'filterData'
                )
            )
            ->setAllowedTypes(
                array(
                    'translator' => array(
                        'Symfony\Bundle\FrameworkBundle\Translation\Translator',
                        'Symfony\Component\Translation\DataCollectorTranslator'
                    ),
                    'filterData' => 'array'
                )
            );
    }

    public function getName()
    {
        return 'applicationSubmit';
    }

    public function getGroups($groupId) {
        $er = $this->em->getRepository('AppBundle:Course');
        $results = $er->createQueryBuilder('c')
            ->where('c.studentGroup = :group')
            ->setParameter('group', $groupId)
            ->getQuery()
            ->getResult();
        $courses = array();
        /** @var Course $course */
        foreach ($results as $course) {
            $courses[$course->getName()] = $course->getName();
        }
        return $courses;
    }
}
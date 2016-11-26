<?php

namespace Project\AppBundle\Form\Type;

use Project\UserBundle\Entity\StudentGroup;
use Project\UserBundle\Entity\StudentStream;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;

class CreateGroupType extends AbstractType
{
    const ID = 'app.create_group_form';

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
        /** @var StudentGroup $group */
        $group = $options['group'];

        $builder
            ->add(
                'groupname',
                'text',
                [
                    'label' => $translator->trans('group.create.groupname'),
                    'data' => $group->getGroupName(),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 3, 'max' => 255])
                    ]
                ]
            )
            ->add(
                'specialisation',
                'text',
                [
                    'label' => $translator->trans('group.create.specialisation'),
                    'data' => $group->getSpecialisation(),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 3, 'max' => 255])
                    ]
                ]
            )
            ->add(
                'stream',
                'choice',
                array(
                    'choices' => $this->getStreams(),
                    'label' => $translator->trans('group.create.stream'),
                    'empty_value' => $translator->trans('group.create.stream'),
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
                    'group'
                )
            )
            ->setAllowedTypes(
                array(
                    'translator' => array(
                        'Symfony\Bundle\FrameworkBundle\Translation\Translator',
                        'Symfony\Component\Translation\DataCollectorTranslator'
                    ),
                    'group' => 'Project\UserBundle\Entity\StudentGroup'
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

    public function getStreams()
    {
        $er = $this->em->getRepository('UserBundle:StudentStream');
        $results = $er->createQueryBuilder('c')
            ->getQuery()
            ->getResult();
        $streams = array();
        /** @var StudentStream $stream */
        foreach ($results as $stream) {
            $streams[$stream->getStreamName()] = $stream->getStreamName();
        }
        return $streams;
    }
}
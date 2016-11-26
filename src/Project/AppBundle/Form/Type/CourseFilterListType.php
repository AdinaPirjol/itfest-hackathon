<?php
/**
 * Created by PhpStorm.
 * User: nnao9_000
 * Date: 11/26/2016
 * Time: 11:35 AM
 */

namespace Project\AppBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CourseFilterListType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $options['translator'];
        $filterData = $options['filterData'];

        $builder
            ->add(
                'college',
                'choice',
                array(
                    'choices' => $filterData['college'],
                    'label' => 'Universitatea',
                    'placeholder' => 'Selecteaza universitatea',
                    'multiple' => false,
                    'required' => true,
                )
            )
            ->add(
                'courseInput',
                'text',
                array(
                    'label' => $translator->trans('Curs'),
                    'required' => false,
                )
            )
            ;
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

    /**
     * @return string
     */
    public function getName()
    {
        return 'courseFilterList';
    }
}
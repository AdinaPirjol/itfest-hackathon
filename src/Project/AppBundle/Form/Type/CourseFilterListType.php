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
                'professor',
                'choice',
                array(
                    'choices' => $filterData['professor'],
                    'label' => $translator->trans('project_form.filters.labels.professor'),
                    'multiple' => true,
                    'required' => false,
                )
            )
            ->add(
                'course',
                'choice',
                array(
                    'choices' => $filterData['course'],
                    'label' => $translator->trans('Curs'),
                    'multiple' => false,
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
        return 'projectFilterList';
    }
}
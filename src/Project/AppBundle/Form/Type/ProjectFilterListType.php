<?php

namespace Project\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectFilterListType extends AbstractType
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
                'availability',
                'choice',
                array(
                    'choices' => $filterData['availability'],
                    'label' => $translator->trans('project_form.filters.labels.availability'),
                    'empty_value' => $translator->trans('project_form.filters.empty_value.availability'),
                    'required' => true,
                )
            )
            ->add(
                'stream',
                'choice',
                array(
                    'choices' => $filterData['stream'],
                    'label' => $translator->trans('project_form.filters.labels.stream'),
                    'empty_value' => $translator->trans('project_form.filters.empty_value.stream'),
                    'required' => true,
                )
            )
            ->add(
                'professor',
                'choice',
                array(
                    'choices' => $filterData['professor'],
                    'label' => $translator->trans('project_form.filters.labels.professor'),
                    'multiple' => true,
                    'required' => true,
                )
            )
            ->add(
                'tag',
                'choice',
                array(
                    'choices' => $filterData['tag'],
                    'label' => $translator->trans('project_form.filters.labels.tag'),
                    'multiple' => true,
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
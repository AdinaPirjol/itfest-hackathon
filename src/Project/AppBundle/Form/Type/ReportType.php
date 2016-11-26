<?php

namespace Project\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;

class ReportType extends AbstractType
{
    const ID = 'app.report';

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
                'report',
                'choice',
                [
                    'choices' => $filterData['reports'],
                    'label' => $translator->trans('reports.report_type'),
                    'required' => true,
                    'constraints' => [
                        new Choice(['choices' => array_keys($filterData['reports'])])
                    ]
                ]
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(
                [
                    'translator',
                    'filterData'
                ]
            )
            ->setAllowedTypes(
                [
                    'translator' => array(
                        'Symfony\Bundle\FrameworkBundle\Translation\Translator',
                        'Symfony\Component\Translation\DataCollectorTranslator'
                    ),
                    'filterData' => 'array'
                ]
            );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'report';
    }
}
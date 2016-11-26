<?php

namespace Project\UserBundle\Form\Type;

use Project\AppBundle\Validator\Constraints\Name;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CreateUserType extends AbstractType
{
    const ID = 'app.create_user_form';

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
                'firstname',
                'text',
                array(
                    'label' => $translator->trans('users.create.firstname'),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Name(),
                        new Length(array('min' => 3))
                    )
                )
            )
            ->add(
                'lastname',
                'text',
                array(
                    'label' => $translator->trans('users.create.lastname'),
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
                'username',
                'text',
                array(
                    'label' => $translator->trans('users.create.username'),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 5, 'max' => 20)),
                    )
                )
            )
            ->add(
                'email',
                'email',
                array(
                    'label' => $translator->trans('users.create.email'),
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Email()
                    )
                )
            )
            ->add(
                'role',
                'choice',
                array(
                    'choices' => $filterData['roles'],
                    'label' => $translator->trans('users.create.role'),
                    'empty_value' => $translator->trans('users.create.role'),
                    'required' => true,
                    'error_bubbling' => true,
                    'constraints' => array(
                        new Choice(array('choices' => array_keys($filterData['roles'])))
                    )
                )
            )
            ->add(
                'preferred_locale',
                'choice',
                array(
                    'choices' => $filterData['locales'],
                    'label' => $translator->trans('users.create.locale'),
                    'data' => 'en',
                    'required' => true,
                    'error_bubbling' => true,
                    'constraints' => array(
                        new Choice(array('choices' => array_keys($filterData['locales'])))
                    )
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
        return 'createUser';
    }
}
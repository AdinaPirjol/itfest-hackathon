<?php

namespace Project\UserBundle\Form\Type;

use Project\AppBundle\Validator\Constraints\Name;
use Project\UserBundle\Entity\User;
use Project\UserBundle\Entity\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangeUserType extends AbstractType
{
    const ID = 'app.change_user_form';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $options['translator'];
        /** @var User $user */
        $user = $options['user'];
        $groups = $options['groups'];
        $locales = $options['locales'];

        $builder
            ->add(
                'firstname',
                'text',
                array(
                    'label' => $translator->trans('users.edit.firstname'),
                    'data' => $user->getFirstName(),
                    'invalid_message' => 'firstname',
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Name(),
                        new Length(array('min' => 3, 'max' => 128))
                    )
                )
            )
            ->add(
                'lastname',
                'text',
                array(
                    'label' => $translator->trans('users.edit.lastname'),
                    'data' => $user->getLastName(),
                    'invalid_message' => 'lastname',
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 3, 'max' => 128))
                    )
                )
            )
            ->add(
                'username',
                'text',
                array(
                    'label' => $translator->trans('users.edit.username') . ' (' . $translator->trans('users.edit.disabled') . ')',
                    'data' => $user->getUserCredentials()->getUsername(),
                    'invalid_message' => 'username',
                    'required' => true,
                    'error_bubbling' => true,
                    'disabled' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 5, 'max' => 128))
                    )
                )
            )
            ->add(
                'email',
                'email',
                array(
                    'label' => $translator->trans('users.edit.email'),
                    'data' => $user->getUserCredentials()->getEmail(),
                    'invalid_message' => 'email',
                    'required' => true,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Email()
                    )
                )
            )
            /** todo phone validator */
            ->add(
                'phone',
                'integer',
                array(
                    'label' => $translator->trans('users.edit.phone'),
                    'data' => $user->getPhoneNumber(),
                    'invalid_message' => 'phone',
                    'required' => true,
                    'error_bubbling' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 5, 'max' => 20))
                    )
                )
            )
            ->add(
                'role',
                'text',
                array(
                    'label' => $translator->trans('users.edit.role') . ' (' . $translator->trans('users.edit.disabled') . ')',
                    'data' => $translator->trans('roles.' . $user->getUserType()->getRoleType()),
                    'invalid_message' => 'role',
                    'required' => true,
                    'error_bubbling' => true,
                    'disabled' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 5, 'max' => 20))
                    )
                )
            )
            ->add(
                'description',
                'textarea',
                array(
                    'label' => $translator->trans('users.edit.about'),
                    'data' => $user->getDescription(),
                    'invalid_message' => 'description',
                    'required' => false,
                    'error_bubbling' => true,
                    'constraints' => array(
                        new Length(array('min' => 0, 'max' => 255))
                    )
                )
            )
            ->add(
                'preferred_locale',
                'choice',
                array(
                    'choices' => $locales,
                    'label' => $translator->trans('users.edit.locale'),
                    'data' => ($user->getGroup() ? $user->getGroup()->getGroupName() : ''),
                    'invalid_message' => 'locale',
                    'required' => true,
                    'error_bubbling' => true,
                    'constraints' => array(
                        new Choice(array('choices' => array_keys($locales)))
                    )
                )
            );

        if ($user->getUserType()->getRoleType() == UserType::ROLE_USER) {
            $builder->add(
                'group',
                'choice',
                array(
                    'choices' => $groups,
                    'label' => $translator->trans('users.edit.group'),
                    'data' => $user->getPreferredLocale(),
                    'invalid_message' => 'group',
                    'empty_value' => '',
                    'required' => true,
                    'error_bubbling' => true,
                    'constraints' => array(
                        new Choice(array('choices' => array_keys($groups)))
                    )
                )
            );
        }
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
                    'groups',
                    'user',
                    'locales'
                )
            )
            ->setAllowedTypes(
                array(
                    'translator' => array(
                        'Symfony\Bundle\FrameworkBundle\Translation\Translator',
                        'Symfony\Component\Translation\DataCollectorTranslator'
                    ),
                    'groups' => 'array',
                    'user' => 'Project\UserBundle\Entity\User',
                    'locales' => 'array'
                )
            );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'changeUser';
    }
}
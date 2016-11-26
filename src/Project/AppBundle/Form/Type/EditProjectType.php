<?php

namespace Project\AppBundle\Form\Type;

use Project\AppBundle\Entity\Project;
use Project\AppBundle\Services\UtilService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class EditProjectType extends AbstractType
{
    const ID = 'app.edit_project_form';
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $options['translator'];
        /** @var Project $project */
        $project = $options['project'];
        $filterData = $options['filterData'];

        $tagKeysData = [];

        if (!$project) {
            foreach ($project->getTagProjects() as $tagProject) {
                $tagKeysData[] = $tagProject->getTag()->getId();
            }
        }

        $builder
            ->add(
                'name',
                'text',
                [
                    'label' => $translator->trans('view_project.name'),
                    'data' => $project->getName(),
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
                'nameRo',
                'text',
                [
                    'label' => $translator->trans('view_project.name_ro'),
                    'data' => $project->getNameRo(),
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
                'stream',
                'choice',
                [
                    'choices' => $filterData['streams'],
                    'label' => $translator->trans('view_project.stream'),
                    'data' => $project->getId() ? $project->getStream()->getId() : '',
                    'required' => true,
                    'constraints' => [
                        new Choice(['choices' => array_keys($filterData['streams'])])
                    ]
                ]
            )
            ->add(
                'tags',
                'choice',
                [
                    'choices' => $filterData['tags'],
                    'label' => $translator->trans('view_project.tags'),
                    'data' => $tagKeysData,
                    'multiple' => true,
                    'required' => true
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'label' => $translator->trans('users.edit.about'),
                    'data' => $project->getDescription(),
                    'invalid_message' => 'Invalid description',
                    'required' => false,
                    'error_bubbling' => true,
                    'trim' => true,
                    'constraints' => [
                        new Length(['min' => 0, 'max' => 255])
                    ]
                ]
            )
            ->add(
                'studentNo',
                'integer',
                [
                    'label' => $translator->trans('view_project.no_students'),
                    'data' => $project->getStudentNo(),
                    'invalid_message' => 'Invalid studentNo',
                    'required' => true,
                    'trim' => true,
                    'error_bubbling' => true,
                    'constraints' => [
                        new NotBlank(),
                        new Range(['min' => max(1, $project->getRemainingStudentNo()), 'max' => 20])
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
                    'project',
                    'filterData'
                ]
            )
            ->setAllowedTypes(
                [
                    'translator' => array(
                        'Symfony\Bundle\FrameworkBundle\Translation\Translator',
                        'Symfony\Component\Translation\DataCollectorTranslator'
                    ),
                    'project' => 'Project\AppBundle\Entity\Project',
                    'filterData' => 'array'
                ]
            );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'editProject';
    }
}
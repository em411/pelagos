<?php

namespace App\Form;

use App\Entity\DataRepository;
use App\Entity\DataRepositoryRole;
use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * A form for creating Person to Data Reppository Associations.
 */
class PersonDataRepositoryType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', EntityType::class, array(
                'label' => 'Person:',
                'class' => Person::class,
                'choice_label' => function ($value, $key, $index) {
                    return $value->getLastName() . ', ' . $value->getFirstName() . ', ' . $value->getEmailAddress();
                },
                'placeholder' => '[Please Select a Person]',
            ))
            ->add('dataRepository', EntityType::class, array(
                'label' => 'Data Repository:',
                'class' => DataRepository::class,
                'choice_label' => 'name',
                'placeholder' => '[Please Select a Data Repository]',
            ))
            ->add('role', EntityType::class, array(
                'label' => 'Role:',
                'class' => DataRepositoryRole::class,
                'choice_label' => 'name',
                'placeholder' => '[Please Select a Role]',
            ))
            ->add('label', TextType::class, array(
                'label' => 'Label:',
                'required' => true,
            ));
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\PersonDataRepository',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ));
    }

    /**
     * Finish the form view.
     *
     * This overrides the empty finishView in AbstractType and sorts the dropdown choices.
     *
     * @param FormView      $view    The view.
     * @param FormInterface $form    The form.
     * @param array         $options The options.
     *
     * @return void
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        usort(
            $view->children['person']->vars['choices'],
            function (ChoiceView $a, ChoiceView $b) {
                return strcasecmp($a->label, $b->label);
            }
        );
        usort(
            $view->children['dataRepository']->vars['choices'],
            function (ChoiceView $a, ChoiceView $b) {
                return strcasecmp($a->label, $b->label);
            }
        );
        usort(
            $view->children['role']->vars['choices'],
            function (ChoiceView $a, ChoiceView $b) {
                if ($a->data->getWeight() == $b->data->getWeight()) {
                    return 0;
                }
                return (($a->data->getWeight() < $b->data->getWeight()) ? -1 : 1);
            }
        );
    }
}

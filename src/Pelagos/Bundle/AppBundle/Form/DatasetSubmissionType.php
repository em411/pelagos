<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;
use Pelagos\Entity\PersonDatasetSubmissionMetadataContact;

/**
 * A form type for creating a Dataset Submission form.
 */
class DatasetSubmissionType extends AbstractType
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
        $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        $builder
            ->add('title', Type\TextType::class, array(
                'label' => 'Dataset Title',
                'required' => true,
            ))
            ->add('abstract', Type\TextareaType::class, array(
                'label' => 'Dataset Abstract',
                'required' => true,
            ))
            ->add('authors', Type\TextType::class, array(
                'label' => 'Dataset Author(s)',
                'required' => true,
            ))
            ->add('restrictions', Type\ChoiceType::class, array(
                'choices' => DatasetSubmission::getRestrictionsChoices(),
                'label' => 'Restrictions',
                'placeholder' => false,
                'required' => false,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('doi', Type\TextType::class, array(
                'label' => 'Digital Object Identifier',
                'required' => false,
            ))
            ->add('datasetFileUri', Type\HiddenType::class, array(
                'required' => true,
                'attr' => array('data-msg-required' => 'You must provide a dataset file using one of the methods below.'),
            ))
            ->add('datasetFileTransferType', Type\HiddenType::class, array(
                'required' => false,
            ))
            ->add('datasetFilePath', Type\TextType::class, array(
                'label' => 'Dataset File Path',
                'required' => false,
                'mapped' => false,
            ))
            ->add('datasetFileForceImport', Type\CheckboxType::class, array(
                'label' => 'import this file again from the same path',
                'required' => false,
                'mapped' => false,
            ))
            ->add('datasetFileUrl', Type\TextType::class, array(
                'label' => 'Dataset File URL',
                'required' => false,
                'mapped' => false,
            ))
            ->add('datasetFileForceDownload', Type\CheckboxType::class, array(
                'label' => 'download this file again from the same URL',
                'required' => false,
                'mapped' => false,
            ))
            ->add('shortTitle', Type\TextType::class, array(
                'label' => 'Short Title',
                'required' => false,
            ))
            ->add('referenceDate', Type\DateType::class, array(
                'label' => 'Date',
                'required' => true,
                'attr' => array('placeholder' => 'yyyy-mm-dd'),
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ))
            ->add('referenceDateType', Type\ChoiceType::class, array(
                'label' => 'Date Type',
                'choices' => DatasetSubmission::getReferenceDateTypeChoices(),
                'placeholder' => '[Please Select a Date Type]',
                'required' => true,
            ))
            ->add('purpose', Type\TextareaType::class, array(
                'label' => 'Purpose',
                'required' => true,
            ))
            ->add('suppParams', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Data Parameters and Units',
                'required' => true,
            ))
            ->add('suppMethods', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Methods',
                'required' => false,
            ))
            ->add('suppInstruments', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Instruments',
                'required' => false,
            ))
            ->add('suppSampScalesRates', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Sampling Scales and Rates',
                'required' => false,
            ))
            ->add('suppErrorAnalysis', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Error Analysis',
                'required' => false,
            ))
            ->add('suppProvenance', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Provenance and Historical References',
                'required' => false,
            ))
            ->add('themeKeywords', Type\CollectionType::class, array(
                'label' => 'Theme Keywords',
                'entry_type' => Type\TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => true,
            ))
            ->add('placeKeywords', Type\CollectionType::class, array(
                'label' => 'Place Keywords',
                'entry_type' => Type\TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => false,
            ))
            ->add('topicKeywords', Type\ChoiceType::class, array(
                'label' => 'Topic Category Keywords',
                'choices' => DatasetSubmission::getTopicKeywordsChoices(),
                'multiple' => true,
                'required' => true,
            ))
            ->add('spatialExtent', Type\HiddenType::class, array(
                'required' => true,
            ))
            ->add('spatialExtentDescription', Type\TextareaType::class, array(
                'label' => 'Spatial Extent Description',
                'required' => false,
                'attr' => array('rows' => '5'),
            ))
            ->add('temporalExtentDesc', Type\ChoiceType::class, array(
                'label' => 'Time Period Description',
                'choices' => DatasetSubmission::getTemporalExtentDescChoices(),
                'required' => true,
                'placeholder' => '[Please Select a Time Period Description]',
            ))
            ->add('temporalExtentBeginPosition', Type\DateType::class, array(
                'label' => 'Start Date',
                'required' => true,
                'attr' => array('placeholder' => 'yyyy-mm-dd'),
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ))
            ->add('temporalExtentEndPosition', Type\DateType::class, array(
                'label' => 'End Date',
                'required' => true,
                'attr' => array('placeholder' => 'yyyy-mm-dd'),
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ))
            ->add('distributionFormatName', Type\TextType::class, array(
                'label' => 'Distribution Format Name',
                'required' => false,
            ))
            ->add('fileDecompressionTechnique', Type\TextType::class, array(
                'label' => 'File Decompression Technique',
                'required' => false,
            ))
            ->add('datasetContacts', Type\CollectionType::class, array(
                'label' => 'Dataset Contacts',
                'entry_type' => PersonDatasetSubmissionType::class,
                'entry_options' => array(
                    'data_class' => PersonDatasetSubmissionDatasetContact::class,
                ),
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => true,
            ))
            ->add('metadataContacts', Type\CollectionType::class, array(
                'label' => 'Metadata Contacts',
                'entry_type' => PersonDatasetSubmissionType::class,
                'entry_options' => array(
                    'data_class' => PersonDatasetSubmissionMetadataContact::class,
                ),
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
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
            'data_class' => DatasetSubmission::class,
            'allow_extra_fields' => true,
        ));
    }
}

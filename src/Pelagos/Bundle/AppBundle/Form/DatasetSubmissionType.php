<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DistributionPoint;
use Pelagos\Entity\Entity;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;
use Pelagos\Entity\PersonDatasetSubmissionMetadataContact;

/**
 * A form type for creating a Dataset Submission form.
 */
class DatasetSubmissionType extends AbstractType
{
    /**
     * Constructor for form type.
     *
     * @param Entity                                $entity The entity associated with this form.
     * @param PersonDatasetSubmissionDatasetContact $poc    A point of contact.
     */
    public function __construct(Entity $entity = null, PersonDatasetSubmissionDatasetContact $poc = null)
    {
        $this->formEntity = $entity;
        $this->formPoc = $poc;
    }

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
                'attr' => array('rows' => '16'),
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
            ->add('datasetFileUri', Type\HiddenType::class, array(
                'required' => true,
                'attr' => array(
                    'data-msg-required' => 'You must provide a dataset file using one of the methods below.'
                ),
            ))
            ->add('datasetFileTransferType', Type\HiddenType::class, array(
                'required' => false,
            ))
            ->add('datasetFilePath', Type\TextType::class, array(
                'label' => 'Dataset File Path',
                'required' => false,
                'mapped' => false,
                'attr' => array('disabled' => 'disabled'),
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
                'attr' => array('data-rule-url' => true),
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
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC',
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
                'attr' => array('rows' => '5'),
            ))
            ->add('suppParams', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Data Parameters and Units',
                'required' => true,
                'attr' => array('rows' => '5'),
            ))
            ->add('suppMethods', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Methods',
                'required' => false,
                'attr' => array('rows' => '5'),
            ))
            ->add('suppInstruments', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Instruments',
                'required' => false,
                'attr' => array('rows' => '5'),
            ))
            ->add('suppSampScalesRates', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Sampling Scales and Rates',
                'required' => false,
                'attr' => array('rows' => '5'),
            ))
            ->add('suppErrorAnalysis', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Error Analysis',
                'required' => false,
                'attr' => array('rows' => '5'),
            ))
            ->add('suppProvenance', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Provenance and Historical References',
                'required' => false,
                'attr' => array('rows' => '5'),
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
            ->add('temporalExtentNilReasonType', Type\ChoiceType::class, array(
                'label' => 'Nilreason Type',
                'choices' => DatasetSubmission::getNilReasonTypes(),
                'required' => 'false',
                'placeholder' => '[Please Select a Reason]',
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
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC',
            ))
            ->add('temporalExtentEndPosition', Type\DateType::class, array(
                'label' => 'End Date',
                'required' => true,
                'attr' => array('placeholder' => 'yyyy-mm-dd'),
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => true,
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC',
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
            ))
            ->add('distributionPoints', Type\CollectionType::class, array(
                'label' => 'Distribution Points',
                'entry_type' => DistributionPointType::class,
                'entry_options' => array(
                    'data_class' => DistributionPoint::class,
                ),
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => true,
            ))
            ->add('erddapUrl', Type\TextType::class, array(
                'label' => 'ERDDAP Url',
                'required' => false,
            ))
            ->add('isRemotelyHosted', Type\CheckboxType::class, array(
                'label' => 'Is Remotely Hosted',
                'mapped' => false,
                'required' => false,
            ))
            ->add('remotelyHostedName', Type\TextType::class, array(
                'label' => 'Remotely Hosted Name',
                'required' => false,
            ))
            ->add('remotelyHostedDescription', Type\TextType::class, array(
                'label' => 'Remotely Hosted Description',
                'required' => false,
            ))
            ->add('remotelyHostedFunction', Type\ChoiceType::class, array(
                'label' => 'Remotely Hosted Function',
                'choices' => DatasetSubmission::getOnlineFunctionCodes(),
                'placeholder' => '[Please Select]',
                'required' => false,
            ))
            ->add('isDatasetFileInColdStorage', Type\CheckboxType::class, array(
                'label' => 'In Cold Storage',
                'mapped' => false,
                'required' => false,
            ))
            ->add('datasetFileColdStorageArchiveSize', Type\IntegerType::class, array(
                'label' => 'Cold Storage Archive Size (Bytes)',
                'mapped' => false,
                'required' => false,
            ))
            ->add('datasetFileColdStorageArchiveSha256Hash', Type\TextType::class, array(
                'label' => 'Cold Storage Archive Sha256 Hash',
                'mapped' => false,
                'required' => false,
            ))
            ->add('datasetFileColdStorageOriginalFilename', Type\TextType::class, array(
                'label' => 'Cold Storage Archive Original Filename',
                'mapped' => false,
                'required' => false,
            ))
            ->add('submitButton', Type\SubmitType::class, array(
                'label' => 'Submit',
                'attr'  => array('class' => 'submitButton'),
            ))
            ->add('endReviewBtn', Type\SubmitType::class, array(
                'label' => 'End Review',
                'attr'  => array('class' => 'submitButton'),
             ))
            ->add('acceptDatasetBtn', Type\SubmitType::class, array(
                'label' => 'Accept Dataset',
                'attr'  => array('class' => 'submitButton'),
            ))
            ->add('requestRevisionsBtn', Type\SubmitType::class, array(
                'label' => 'Request Revisions',
                'attr'  => array('class' => 'submitButton'),
            ));

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            if ($data) {
                if ($data->isDatasetFileInColdStorage() === true) {
                    $form->get('isDatasetFileInColdStorage')->setData(true);
                    $form->get('datasetFileColdStorageArchiveSize')->setData(
                        $data->getDatasetFileColdStorageArchiveSize()
                    );
                    $form->get('datasetFileColdStorageArchiveSha256Hash')->setData(
                        $data->getDatasetFileColdStorageArchiveSha256Hash()
                    );
                    $form->get('datasetFileColdStorageOriginalFilename')->setData(
                        $data->getDatasetFileColdStorageOriginalFilename()
                    );
                }
                if ($data->getDatasetFileTransferStatus() === DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED) {
                    $form->get('isRemotelyHosted')->setData(true);
                }
            }
        });

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $size = $event->getForm()->get('datasetFileColdStorageArchiveSize')->getData();
                $hash = $event->getForm()->get('datasetFileColdStorageArchiveSha256Hash')->getData();
                $name = $event->getForm()->get('datasetFileColdStorageOriginalFilename')->getData();
                $entity = $event->getForm()->getData();
                if (null !== $size and null !== $hash and null !== $name) {
                    $entity->setDatasetFileColdStorageAttributes($size, $hash, $name);
                } else {
                    $entity->clearDatasetFileColdStorageAttributes();
                }

                // If all remotely hosted required fields are set, set to remotely hosted.
                $remotelyHostedName = $event->getForm()->get('remotelyHostedName')->getData();
                $remotelyHostedDescription = $event->getForm()->get('remotelyHostedDescription')->getData();
                $remotelyHostedFunction = $event->getForm()->get('remotelyHostedFunction')->getData();
                $entity = $event->getForm()->getData();
                if (null !== $remotelyHostedName and null !== $remotelyHostedDescription and null !== $remotelyHostedFunction) {
                    $entity->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED);
                }
            }
        );
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
        $entity = $this->formEntity;
        $poc = $this->formPoc;

        $resolver->setDefaults(array(
            'data_class' => DatasetSubmission::class,
            'allow_extra_fields' => true,
            'empty_data' => function (FormInterface $form) use ($entity, $poc) {
                return new DatasetSubmission($entity, $poc);
            },
        ));
    }
}
<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Pim\Bundle\ImportExportBundle\Form\Subscriber\JobAliasSubscriber;

use Oro\Bundle\BatchBundle\Connector\ConnectorRegistry;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Oro\Bundle\BatchBundle\Form\Type\JobConfigurationType;
use Pim\Bundle\CatalogBundle\Form\Subscriber\DisableFieldSubscriber;
use Pim\Bundle\ImportExportBundle\Form\Subscriber\RemoveDuplicateJobConfigurationSubscriber;

/**
 * Job instance form type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceType extends AbstractType
{
    /**
     * @var ConnectorRegistry $connectorRegistry
     */
    protected $connectorRegistry;

    /**
     * @var string $jobType
     */
    protected $jobType;

    /**
     * Constructor
     *
     * @param ConnectorRegistry $connectorRegistry
     */
    public function __construct(ConnectorRegistry $connectorRegistry)
    {
        $this->connectorRegistry = $connectorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this
            ->addCodeField($builder)
            ->addLabelField($builder)
            ->addConnectorField($builder)
            ->addAliasField($builder)
            ->addJobConfigurationField($builder);

        $builder->addEventSubscriber(new JobAliasSubscriber($this->connectorRegistry));
    }

    /**
     * Add code field and subscriber
     *
     * @param FormBuilderInterface $builder
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    protected function addCodeField(FormBuilderInterface $builder)
    {
        $builder
            ->add('code', 'text')
            ->addEventSubscriber(new DisableFieldSubscriber('code'));

        return $this;
    }

    /**
     * Add label field
     *
     * @param FormBuilderInterface $builder
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    protected function addLabelField(FormBuilderInterface $builder)
    {
        $builder->add('label');

        return $this;
    }

    /**
     * Add connector field
     *
     * @param FormBuilderInterface $builder
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    protected function addConnectorField(FormBuilderInterface $builder)
    {
        $choices = $this->connectorRegistry->getConnectors($this->jobType);

        $builder
            ->add(
                'connector',
                'choice',
                array(
                    'choices'      => array_combine($choices, $choices),
                    'required'     => true,
                    'by_reference' => false,
                    'mapped'       => false
                )
            );

        return $this;
    }

    /**
     * Add alias field
     *
     * @param FormBuilderInterface $builder
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    protected function addAliasField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'alias',
                'choice',
                array(
                    'choices'      => $this->connectorRegistry->getConnector('Akeneo CSV Connector', $this->jobType), //FIXME
                    'required'     => true,
                    'by_reference' => false,
                    'mapped'       => false
                )
            );

        return $this;
    }

    /**
     * Add job configuration form type
     *
     * @param FormBuilderInterface $builder
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    protected function addJobConfigurationField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'job',
                new JobConfigurationType(),
                array(
                    'required'     => false,
                    'by_reference' => false,
                )
            )
            ->get('job')
            ->addEventSubscriber(new RemoveDuplicateJobConfigurationSubscriber());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_import_export_jobInstance';
    }

    /**
     * Setter for job type
     *
     * @param string $jobType
     *
     * @return \Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType
     */
    public function setJobType($jobType)
    {
        $this->jobType = $jobType;

        return $this;
    }
}

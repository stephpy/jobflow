<?php

namespace Rezzza\Jobflow\Extension\Core\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Processor\ConfigProcessor;
use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\JobConfig;
use Rezzza\Jobflow\Metadata\MetadataAccessor;

/**
 * Accepts multiple inputs and create 1 job for each one inputs.
 *
 * @uses JobType
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class PreExecutorType extends JobType
{
    /**
     * {@inheritdoc}
     */
    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        parent::setExecOptions($resolver);

        $resolver->setRequired(array(
            'input'
        ));

        $resolver->setDefaults(array(
            'class' => 'Rezzza\Jobflow\Extension\Core\Executor\PreExecutor',
            'args' => function(Options $options) {
                return array(
                    'input' => $options['input'],
                );
            },
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pre_executor';
    }
}

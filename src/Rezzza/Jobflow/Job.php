<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Event\JobEvent;
use Rezzza\Jobflow\Event\JobEvents;
use Rezzza\Jobflow\Extension\ETL\Type\ETLType;
use Rezzza\Jobflow\Scheduler\ExecutionContext;
use Rezzza\Jobflow\Processor\ConfigProcessor;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class Job implements \IteratorAggregate, JobInterface
{
    /**
     * @var JobConfig
     */
    private $config;

    /**
     * @var JobInterface
     */
    private $parent;

    /**
     * @var JobInterface[]
     */
    protected $children = array();

    /**
     * @var JobConfig $config
     */
    public function __construct(JobConfig $config)
    {
        $this->config = $config;
    }

    public function setParent(JobInterface $parent = null)
    {
        if (null !== $parent && '' === $this->config->getName()) {
            throw new \LogicException('A job with an empty name cannot have a parent job.');
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * @var ExecutionContext $context
     */
    public function execute(ExecutionContext $context)
    {
        // We inject msg as it could be used during job runtime configuration
        $options = $this->getOptions();
        $options['message'] = $context->input;

        // Runtime configuration (!= buildJob which is executed when we build job)
        $this->getResolved()->configJob($this->getConfig(), $options);

        $dispatcher = $this->config->getEventDispatcher();

        if ($dispatcher->hasListeners(JobEvents::PRE_EXECUTE)) {
            $event = new JobEvent($this);
            $dispatcher->dispatch(JobEvents::PRE_EXECUTE, $event);
        }

        $input = $this->getInput($context->input);
        $output = $this->getOutput($context->output);
        $config = $this->getConfig()->getConfigProcessor();

        if ($config instanceof ConfigProcessor) {
            $factory = new \Rezzza\Jobflow\Processor\ProcessorFactory;
            $factory
                ->create($context->input->pipe, $config, $this->getConfig()->getMetadataAccessor())
                ->execute($input, $output, $context)
            ;
        } elseif (is_callable($config)) {
            call_user_func_array(
                $config, 
                array(
                    $input,
                    $output,
                    $context
                )
            );
        } else {
            throw new \InvalidArgumentException('processor should be a ConfigProcessor or a callable');
        }

        // Update context
        $output->setContextFromInput($input);

        if ($dispatcher->hasListeners(JobEvents::POST_EXECUTE)) {
            $event = new JobEvent($this);
            $dispatcher->dispatch(JobEvents::POST_EXECUTE, $event);
        }

        return $output;
    }

    /**
     * @param JobInterface $child
     */
    public function add(JobInterface $child)
    {
        $child->setParent($this);

        $this->children[$child->getName()] = $child;
    }

    /**
     * @param $name
     *
     * @return JobInterface
     */
    public function get($name)
    {
        if (!array_key_exists($name, $this->children)) {
            throw new \LogicException(sprintf('No child with name : "%s" in job "%s"', $name, $this->getName()));
        }

        return $this->children[$name];
    }

    /**
     * @return JobConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->config->getOptions();
    }

    /**
     * @return array
     */
    public function getOption($name, $default = null)
    {
        return $this->config->getOption($name, $default);
    }

    /**
     * @return JobInput
     */
    public function getInput(JobMessage $message)
    {
        return new JobInput($message);
    }

    /**
     * @return JobOutput
     */
    public function getOutput(JobMessage $message)
    {
        $output = new JobOutput($message);

        return $output;
    }

    /**
     * @return ResolvedJob
     */
    public function getResolved()
    {
        return $this->config->getResolved();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->config->getName();
    }

    /**
     * @return JobInterface[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return JobInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns the iterator for this job.
     *
     * @return \RecursiveArrayIterator
     */
    public function getIterator()
    {
        return new \RecursiveArrayIterator($this->children);
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->getParent()->getName().'.'.$this->getName();
    }

    /**
     * @return boolean
     */
    public function isExtractor()
    {
        return $this->config->getAttribute('etl_type') === ETLType::TYPE_EXTRACTOR;
    }

    /**
     * @return boolean
     */
    public function isTransformer()
    {
        return $this->config->getAttribute('etl_type') === ETLType::TYPE_TRANSFORMER;
    }

    /**
     * @return boolean
     */
    public function isLoader()
    {
        return $this->config->getAttribute('etl_type') === ETLType::TYPE_LOADER;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
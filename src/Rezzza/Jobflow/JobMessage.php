<?php

namespace Rezzza\Jobflow;

class JobMessage
{
    public $context;

    public $data = array();

    public $pipe = array();

    public $metadata;

    public $jobOptions = array();

    public $ended = false;

    /**
     * @var JobOptions
     */
    protected $options;

    public function __construct(JobOptions $options)
    {
        $this->options = $options;
    }

    public function __clone()
    {
        $this->options = clone $this->options;
    }

    public function reset()
    {
        $this->data = array();
        $this->pipe = array();
    }

    public function getMetadata($name, $offset = 0)
    {
        $offset = $offset + $this->getGlobalContext()->getOption('offset');

        return $this->metadata[$name][$offset];
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getGlobalContext()
    {
        return $this->getOptions()->getGlobalContext();
    }
}

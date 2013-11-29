<?php

namespace Rezzza\Jobflow;

/**
 * JobOptions
 *
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class JobOptions
{
    /**
     * @var GlobalContextInterface
     */
    private $globalContext;

    /**
     * @var array
     */
    protected $execOptions = array();

    /**
     * @param GlobalContextInterface $globalContext globalContext
     */
    public function __construct(GlobalContextInterface $globalContext)
    {
        $this->globalContext = $globalContext;
    }

    /**
     * @return GlobalContextInterface
     */
    public function getGlobalContext()
    {
        return $this->globalContext;
    }

    /**
     * @param string $key   key
     * @param mixed  $value value
     */
    public function setExec($key, $value)
    {
        $this->execOptions[$key] = $value;
    }

    /**
     * @return array
     */
    public function getExecs()
    {
        return $this->execOptions;
    }
}

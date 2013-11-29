<?php

namespace Rezzza\Jobflow\Io;

/**
 * InputAggregator
 *
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class InputAggregator implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $inputs = array();

    /**
     * @param array $inputs inputs
     */
    public function __construct(array $inputs)
    {
        foreach ($inputs as $input) {
            $this->add($input);
        }
    }

    /**
     * @param Input $input input
     */
    public function add(Input $input)
    {
        $this->inputs[] = $input;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->inputs);
    }

    /**
     * @return integer
     */
    public function count()
    {
        return $this->getIterator()->count();
    }
}

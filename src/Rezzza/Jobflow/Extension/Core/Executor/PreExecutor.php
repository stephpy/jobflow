<?php

namespace Rezzza\Jobflow\Extension\Core\Executor;

use Rezzza\Jobflow\Io\InputAggregator;
use Knp\ETL\ContextInterface;
use Rezzza\Jobflow\JobInput;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

/**
 * PreExecutor
 *
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class PreExecutor
{
    /**
     * @var InputAggregator
     */
    private $input;

    /**
     * @param InputAggregator $input input
     */
    public function __construct(InputAggregator $input)
    {
        $this->input = $input;
    }

    public function execute(JobInput $jobInput, JobOutput $jobOutput, ExecutionContext $context)
    {
        $jobOutput->setData($this->input);
    }

}

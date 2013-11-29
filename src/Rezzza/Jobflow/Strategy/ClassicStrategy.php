<?php

namespace Rezzza\Jobflow\Strategy;

use Rezzza\Jobflow\Extension\Pipe\Pipe;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\Scheduler\Jobflow;

class ClassicStrategy implements MessageStrategyInterface
{
    public function handle(Jobflow $jobflow, JobMessage $msg)
    {
        $globalContext = $msg->getGlobalContext();
        $current = $globalContext->getCurrent();

        // Move graph to the current value
        $jobflow->getJobGraph()->move($current);

        // Gets the current job
        $child = $jobflow->getJob()->get($current);

        if ($msg->pipe instanceof Pipe) {
            $jobflow->forwardPipeMessage($msg, $jobflow->getJobGraph());

            // Reset pipe as we already ran through above
            $msg->pipe = array();
        }

        if (true === $child->getRequeue()) {
            $globalContext->tick();

            if (!$globalContext->isFinished()) {
                $origin = $globalContext->getOrigin();
                $jobflow->getJobGraph()->move($origin);

                $globalContext->addStep($current);
                $globalContext->setCurrent($origin);
            } else {
                $msg = null;
            }
        } elseif (!$jobflow->getJobGraph()->hasNextJob()) {
            $msg = null;
        } else {
            $globalContext->updateToNextJob($jobflow->getJobGraph());
        }

        if (null !== $msg) {
            $jobflow->addMessage($msg);
        }
    }
}

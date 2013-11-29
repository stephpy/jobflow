<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Metadata\MetadataAccessor;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobOutput extends JobStream
{
    public function write($result, $offset = null)
    {
        if (null === $offset) {
            $this->message->data[] = $result;
        } else {
            $this->message->data[$offset] = $result;
        }
    }

    public function writeMetadata($result, $offset, MetadataAccessor $accessor)
    {
        $accessor->write($this->message->metadata, $result, $offset);
    }

    public function writePipe($value)
    {
        if (null !== $value) {
            $this->message->pipe = $value;
        }
    }

    public function setContextFromInput(JobInput $input)
    {
        $options = $input->getMessage()->getGlobalContext()->getOptions();

        $this->message->getGlobalContext()->setOptions($options);
    }

    public function setData($data)
    {
        $this->message->data = $data;
    }

    public function end()
    {
        $this->message->ended = true;
    }

    public function isEnded()
    {
        return $this->message->ended;
    }
}

<?php

namespace Runn\Fs;

use Runn\Serialization\SerializerInterface;
use Runn\Serialization\Serializers\PassThru;

class FileWithSerializer
    extends File
    implements FileAsStorageWithSerializerInterface
{

    /**
     * @var \Runn\Serialization\SerializerInterface
     */
    protected $serializer;

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

    public function getSerializer(): SerializerInterface
    {
        return $this->serializer ?: new PassThru();
    }


    protected function processContentsAfterLoad($contents)
    {
        return $this->getSerializer()->decode($contents);
    }

    protected function processContentsBeforeSave($contents)
    {
        return $this->getSerializer()->encode($contents);
    }

}
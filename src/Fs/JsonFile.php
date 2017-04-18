<?php

namespace Runn\Fs;

use Runn\Serialization\SerializerInterface;
use Runn\Serialization\Serializers\Json;

class JsonFile
    extends FileWithSerializer
{

    public function getSerializer(): SerializerInterface
    {
        return new Json();
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        throw new \BadMethodCallException();
    }

}
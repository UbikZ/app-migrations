<?php

namespace Ubikz\Migrations\Configuration;

use Ubikz\Migrations\Configuration\Adapters\InterfaceFileConfiguration;
use Ubikz\Migrations\Exceptions\InvalidAdapterFileException;

class FactoryFileConfiguration
{
    const TYPE_XML = 'xml';

    /**
     * @param $type
     *
     * @return InterfaceFileConfiguration
     *
     * @throws InvalidAdapterFileException
     */
    public static function create($type)
    {
        $adapterClass = sprintf(__NAMESPACE__.'\\Adapters\\%sConfiguration', ucfirst($type));
        if (!class_exists($adapterClass)) {
            throw new InvalidAdapterFileException('Adapter file configuration cannot be found.');
        }

        return (new \ReflectionClass($adapterClass))->newInstance();
    }
}

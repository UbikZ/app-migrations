<?php

namespace Ubikz\Migrations\Configuration\Adapters;

use Ubikz\Migrations\Configuration\DefaultConfiguration;

interface InterfaceFileConfiguration
{
    public function extract(DefaultConfiguration &$conf, $file);
}

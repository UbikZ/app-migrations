<?php

namespace Galilee\Migrations\Configuration\Adapters;

use Galilee\Migrations\Configuration\DefaultConfiguration;

interface InterfaceFileConfiguration
{
    public function extract(DefaultConfiguration &$conf, $file);
}

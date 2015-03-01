<?php

namespace Ubikz\Migrations\Configuration\Adapters;

use Ubikz\Migrations\Configuration\DefaultConfiguration;

/**
 * Class XmlConfiguration.
 */
class XmlConfiguration extends AbstractFileConfiguration
{
    /**
     * @param DefaultConfiguration $conf
     * @param $file
     *
     * @return mixed|void
     */
    public function extract(DefaultConfiguration &$conf, $file)
    {
        $xml = simplexml_load_file($file);
        if (isset($xml->name)) {
            $conf->setName((string) $xml->name);
        }
        if (isset($xml->file['name'])) {
            $conf->setMigrationsFileName((string) $xml->table['name']);
        }
        if (isset($xml->{'migrations-namespace'})) {
            $conf->setMigrationsNamespace((string) $xml->{'migrations-namespace'});
        }
        if (isset($xml->{'migrations-directory'})) {
            $conf->setMigrationsDirectory((string) $xml->{'migrations-directory'});
            $conf->registerMigrationsFromDirectory($conf->getMigrationsDirectory());
        }
        if (isset($xml->migrations->migration)) {
            foreach ($xml->migrations->migration as $migration) {
                $conf->registerMigration((string) $migration['version'], (string) $migration['class']);
            }
        }
    }
}

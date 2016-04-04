#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Keboola\ConfigurationValidator\Command\ValidateConfiguration;

$application = new Application();
$application->add(new ValidateConfiguration());
$application->add(new \Keboola\ConfigurationValidator\Command\ValidateTable());
$application->run();

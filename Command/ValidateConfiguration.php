<?php

namespace Keboola\ConfigurationValidator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateConfiguration extends Command
{
    protected function configure()
    {
        $this
            ->setName('validate')
            ->setDescription('Validate a single config against a JSON schema')
            ->addArgument(
                'schema',
                InputArgument::REQUIRED,
                'Path to the schema'
            )
            ->addArgument(
               'configuration',
               null,
                InputArgument::REQUIRED,
               'Path to the configuration JSON'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('test');
    }
}


<?php

namespace Keboola\ConfigurationValidator\Command;

use JsonSchema\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

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
               'config',
               null,
                InputArgument::REQUIRED,
               'Path to the configuration JSON'
            )
        ;
    }

    protected function error(OutputInterface $output, $message, $code=1) {
        $output->writeln($message);
        return $code;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        if (!$fs->exists($input->getArgument("schema"))) {
            return $this->error($output, "Schema file does not exist.", 1);
        }

        if (!$fs->exists($input->getArgument("config"))) {
            return $this->error($output, "Configuration file does not exist.", 1);
        }

        $schema = json_decode(file_get_contents($input->getArgument("schema")));
        if (!$schema) {
            return $this->error($output, "Schema file empty or invalid JSON.", 1);
        }

        $config = json_decode(file_get_contents($input->getArgument("config")));
        if (!$config) {
            return $this->error($output, "Configuration file empty or invalid JSON.", 1);
        }

        $validator = new Validator();
        $validator->check($config, $schema);
        if (!$validator->isValid()) {
            $message = "";
            foreach ($validator->getErrors() as $error) {
                if ($error['property']) {
                    $message .= $error['property'] . ' ' . $error['message'] . "\n";
                } else {
                    $message .= $error['message'];
                }
            }
            return $this->error($output, $message, 1);
        }
        $output->writeln("JSON valid");
    }

}


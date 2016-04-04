<?php

namespace Keboola\ConfigurationValidator\Command;

use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\Validator;
use Keboola\Csv\CsvFile;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\Exception;
use Keboola\Temp\Temp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class ValidateTable extends Command
{
    protected function configure()
    {
        $this
            ->setName('validate-table')
            ->setDescription('Validate a table of configurations stored in Storage API')
            ->addArgument(
                'schema',
                InputArgument::REQUIRED,
                'Path to the schema'
            )
            ->addArgument(
               'table',
               null,
                InputArgument::REQUIRED,
               'Table in Storage API'
            )
            ->addArgument(
               'token',
               null,
                InputArgument::REQUIRED,
               'Storage API token'
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
            return $this->error($output, "Schema file does not exist.");
        }

        try {
            $client = new Client(["token" => $input->getArgument("token")]);
            $client->verifyToken();
        } catch (Exception $e) {
            return $this->error($output, $e->getMessage());
        }
        
        $schema = json_decode(file_get_contents($input->getArgument("schema")));
        if (!$schema) {
            return $this->error($output, "Schema file empty or invalid JSON.", 1);
        }

        if (!$client->tableExists($input->getArgument("table"))) {
            $this->error($output, "Table '{$input->getArgument("table")}' does not exist.");
        }

        $tmp = new Temp();
        $file = $tmp->createTmpFile();
        $client->exportTable($input->getArgument("table"), $file->getPathname());

        $messages = [];
        
        $csvFile = new CsvFile($file->getPathname());
        $rowNum = 0;

        foreach($csvFile as $row) {
            if (count($messages) >= 1000) {
                return $this->error($output, "Reached 1000 errors after {$rowNum} rows, stopping.\n\n" . join("\n", $messages));
            }
            $rowNum++;

            if ($rowNum == 1) {
                continue;
            }

            $config = json_decode($row[0]);
            if (!$config) {
                $messages[] = "#{$rowNum} Error parsing configuration";
                continue;
            }

            $validator = new Validator();

            try {
                $validator->check($config, $schema);
            } catch (InvalidArgumentException $e) {
                $messages[] = "#{$rowNum} " . $e->getMessage();
                continue;
            }
            if (!$validator->isValid()) {
                foreach ($validator->getErrors() as $error) {
                    if ($error['property']) {
                        $messages[] = "#{$rowNum}: " . $error['property'] . ' ' . $error['message'];
                    } else {
                        $messages[] = "#{$rowNum}: " . $error['message'];
                    }
                }
            }
            unset($data);     
            unset($validator);
        }

        if (count($messages)) {
            return $this->error($output, join("\n", $messages));
        }
        $output->writeln("All JSONs valid, " . $rowNum - 1 ." rows checked");
    }

}


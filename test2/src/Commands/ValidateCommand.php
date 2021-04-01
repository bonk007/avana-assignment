<?php


namespace AvanaAssignment\Commands;


use AvanaAssignment\SpreadsheetValidator;
use \Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class ValidateCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected static $defaultName = 'validate';

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $workingDir = dirname(dirname(__DIR__)) . '/spreadsheets';
            $filepath = $workingDir .'/'. $input->getArgument('filename');
            if (!file_exists($filepath)) {
                throw new Exception("File ({$filepath}) doesn't exist");
            }
            $errors = (new SpreadsheetValidator($filepath))
                ->validate()
                ->errors();
            $table = new Table($output);

            $table->setHeaders(['Sheet', 'Row', 'Error'])
                ->setRows($this->parseErrors($errors))
                ->render();

            return parent::SUCCESS;

        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return parent::FAILURE;
        }
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->addArgument('filename', InputArgument::REQUIRED,
            'Filename (sensitive case) with the extension, make sure the file exists in spreadsheets directory');
    }

    /**
     * Parse array of errors into table array format
     *
     * @param array $errors
     * @return array|\Symfony\Component\Console\Helper\TableCell[]
     */
    protected function parseErrors(array $errors): array
    {
        if (empty($errors)) {
            return [new TableCell('The file is good', ['colspan' => 3])];
        }

        $returned = [];

        foreach ($errors as $worksheet => $worksheetErrors) {
            foreach ($worksheetErrors as $row => $rowErrors) {
                $returned[] = [$worksheet, $row, implode('. ', $rowErrors)];
            }
        }

        return $returned;
    }
}

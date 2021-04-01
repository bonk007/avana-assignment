<?php


namespace AvanaAssignment;

use \Iterator;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpreadsheetValidator
{
    /**
     * Spreadsheet instance
     *
     * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    protected Spreadsheet $spreadsheet;

    /**
     * The location of spreadsheet file
     *
     * @var string
     */
    protected string $filepath;

    /**
     * Stack of validation errors
     *
     * @var array
     */
    protected array $errors = [];

    /**
     * Validation rules from each column
     *
     * @var array
     */
    protected array $rules = [];

    /**
     * SpreadsheetValidator constructor.
     *
     * @param string $filepath
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function __construct(string $filepath)
    {
        $this->spreadsheet = IOFactory::createReaderForFile($filepath)
            ->load($filepath);
        $this->filepath = $filepath;
    }

    /**
     * Execute spreadsheet content validation
     * @return self
     */
    public function validate(): self
    {
        $this->diggingDeeper($this->spreadsheet);
        return $this;
    }

    /**
     * Bring errors attribute to public
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Dig spreadsheet into cell
     *
     * @param mixed $object
     */
    protected function diggingDeeper($object): void
    {
        if ($object instanceof Cell) {
            $this->executeCell($object);
            return;
        }

        $iterator = null;
        $className = get_class($object);
        switch ($className) {
            case Row::class :
                $iterator = $object->getCellIterator();
                break;
            case Worksheet::class:
                $iterator = $object->getRowIterator();
                break;
            default:
                $iterator = $object->getWorksheetIterator();
                break;
        }
        $this->iterate($iterator);
    }

    /**
     * Iterate every layer
     *
     * @param \Iterator $iterator
     */
    protected function iterate(Iterator $iterator)
    {
        if (!$iterator->valid()) {
            return;
        }
        $current = $iterator->current();

        $this->diggingDeeper($current);

        $iterator->next();
        $this->iterate($iterator);
    }

    /**
     * Defines validation rules from the header and validate the cell body
     *
     * @param \PhpOffice\PhpSpreadsheet\Cell\Cell $cell
     */
    protected function executeCell(Cell $cell)
    {
        if ($cell->getRow() === 1) {
            $this->setRules($cell);
            return;
        }
        $this->validateCell($cell);
    }

    /**
     * Set validation rules according to the header's symbol
     *
     * @param \PhpOffice\PhpSpreadsheet\Cell\Cell $cell
     */
    protected function setRules(Cell $cell)
    {
        $header = $cell->getValue();
        $pattern = '/[(*)|#]/';
        if (preg_match_all($pattern, $cell->getValue(), $matches)) {
            $this->rules[$cell->getColumn()] = [
                preg_replace($pattern, '', $header),
                $matches[0]
            ];
            return;
        }
    }

    /**
     * Execute validation for single cell
     *
     * @param \PhpOffice\PhpSpreadsheet\Cell\Cell $cell
     */
    protected function validateCell(Cell $cell)
    {
        $rules = isset($this->rules[$cell->getColumn()]) ? $this->rules[$cell->getColumn()] : null;
        $value = $cell->getValue();

        if (!is_array($rules)) {
            return;
        }

        [$header, $patterns] = $rules;

        foreach ($patterns as $rule) {
            if ($rule === '*' && empty(trim($value))) {
                $this->pushError($cell, "Missing value in " . $header);
            }
            if ($rule === '#' && preg_match('/(\s)/', $value)) {
                $this->pushError($cell, $header . " should not contain any space");
            }
        }
    }

    /**
     * Store the validation error
     *
     * @param \PhpOffice\PhpSpreadsheet\Cell\Cell $cell
     * @param string $error
     */
    protected function pushError(Cell $cell, string $error)
    {
        $worksheet = $cell->getWorksheet()->getTitle();
        $row = $cell->getRow();

        if (!isset($this->errors[$worksheet])) {
            $this->errors[$worksheet] = [];
        }

        if (!isset($this->errors[$worksheet][$row])) {
            $this->errors[$worksheet][$row] = [];
        }

        $this->errors[$worksheet][$row][] = $error;
    }
}

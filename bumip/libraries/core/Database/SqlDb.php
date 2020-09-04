<?php
namespace Bumip\Core\Database;

class SqlDb extends \Envms\FluentPDO\Query
{
    private $currentTable;
    public function __get($table)
    {
        $this->currentTable = $table;
    }
    public function from(?string $table = null, ?int $primaryKey = null): \Envms\FluentPDO\Queries\Select
    {
        if (!$table && $this->currentTable) {
            $table = $this->currentTable;
        }
        return parent::from($table, $primaryKey);
        //Flush currentTable
        $this->currentTable = null;
    }
    public function insertInto(?string $table = null, array $values = []): \Envms\FluentPDO\Queries\Insert
    {
        if (!$table && $this->currentTable) {
            $table = $this->currentTable;
        }
        return parent::insertInto($table, $values);
        //Flush currentTable
        $this->currentTable = null;
    }
}

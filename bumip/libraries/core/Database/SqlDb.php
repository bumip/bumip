<?php
namespace Bumip\Core\Database;

class SqlDb extends \Envms\FluentPDO\Query
{
    private $currentTable;
    public function __get($table)
    {
        $this->currentTable = $table;
        return $this;
    }
    public function insertOne(array $data): \Envms\FluentPDO\Queries\Insert
    {
        if ($this->currentTable) {
            $table = $this->currentTable;
        } else {
            return false;
        }
        return $this->insertInto($table, $data);
    }
    public function find(array $query = []): \Envms\FluentPDO\Queries\Select
    {
        if ($this->currentTable) {
            $table = $this->currentTable;
        } else {
            return false;
        }
        if (!empty($query)) {
            if (is_string($query)) {
                return $this->from($table)->where($query);
            }
            $q = [];
            foreach ($query as $k => $v) {
                $delimiter = is_string($v) ? "'" : '';
                $q[] = " {$k} = {$delimiter}{$v}{$delimiter} ";
            }
            $query = implode(" AND ", $q);
            return $this->from($table)->where($query);
        } else {
            return $this->from($table);
        }
    }
}

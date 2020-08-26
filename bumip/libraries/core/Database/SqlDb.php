<?php
namespace Bumip\Core\Database;

class SqlDb extends \ClanCats\Hydrahon\Builder
{
    public function __get($table)
    {
        $query = new QueryBuilder($this->queryBuilder);
        return $query->table($table, null);
    }
}

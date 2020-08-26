<?php
namespace Bumip\Core\Database;

class QueryBuilder extends \ClanCats\Hydrahon\Query\Sql\Table
{
    public function findOne($query = null)
    {
        $select = $this->select();
        if ($query) {
            foreach ($query as $q) {
                $select = $select->where($q[0], $q[1]);
            }
        }
        return $select->get();
    }
    public function insertOne($insert)
    {
        $this->insert($insert);
    }
}

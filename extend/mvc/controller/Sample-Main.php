<?php
/**
 * Rename this file to Main.php
 */
class Main extends \Bumip\MVC\MainController
{
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    public function example($args = '2:id/3:table_id')
    {
        list($id, $table_id) = array_values($args);
        print $table_id;
    }
}

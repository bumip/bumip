<?php
define('DATABASE_DRIVER', "PDO");
if (is_localrun()) {
    define("DBUSER", "root");
    define("DBHOST", "localhost");
    define("DBPASS", "root");
    define("DBNAME", "bumip3");
    define('MDBNAME', 'bumip3');
    define("MDBSTR", "mongodb://127.0.0.1:27017");
    //define("PDOSTR", 'mysql:host='.DBHOST.';dbname='.DBNAME.';charset=utf8');
    define("PDOSTR", 'sqlite:app/database/'.DBNAME.'.db');
} else {
    define("DBUSER", "root");
    define("DBHOST", "localhost");
    define("DBPASS", "root");
    define("DBNAME", "bumip3");
    define('MDBNAME', 'bumip3');
    define("MDBSTR", "mongodb://127.0.0.1:27017");
    //define("PDOSTR", 'mysql:host='.DBHOST.';dbname='.DBNAME.';charset=utf8');
    define("PDOSTR", 'sqlite:app/database/'.DBNAME.'.db');
}

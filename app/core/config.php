<?php

if($_SERVER['SERVER_NAME'] == 'localhost')
{
    define('DBNAME', 'CHEAPTHRILLS');
    define('DBHOST', 'localhost');
    define('DBUSER', 'postgres');
    define('DBPASS', 'hannahmae6154');
    // define('DBDRIVER', '');

    define('ROOT', 'http://localhost/cheapthrills/public');
    // define('ROOT', 'http://localhost/mvc/app');
} else {
    define('DBNAME', 'postgres');
    define('DBHOST', 'aws-0-ap-southeast-1.pooler.supabase.com');
    define('DBUSER', 'postgres.xkpxcvmdnweqlevmvmgk');
    define('DBPASS', 'upYsVT9dYLV3wsy4');
    define('DBDRIVER', '');

    //define('ROOT', 'http://localhost/cheapthrills/public');
    define('ROOT', 'https://im-cheapthrills.vercel.app
');
}
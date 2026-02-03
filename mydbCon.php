<?php
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbUser = getenv('DB_USER') ?: 'plc_user';
    $dbPass = getenv('DB_PASS') ?: '';
    $dbName = getenv('DB_NAME') ?: 'plc';

    $dbCon = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
?>

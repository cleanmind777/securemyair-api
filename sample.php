<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET');
    // header('Access-Control-Allow-Headers: token, Content-Type, X-Requested-With');
include('mydbCon.php');

$arr = [];
$arr['res'] = 'Sample';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
}
print(json_encode($arr));
?>


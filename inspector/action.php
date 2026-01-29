<?php
include "../headers.php";

$arr = [];
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["id"])) {
        if (isset($_GET["action"]) && isset($_GET["name"])) {
            $action = $_GET["action"];
            $id = $_GET["id"];
            if ($action == 1) {
                $name = $_GET["name"];
                $query = "SELECT $name FROM `companyData` WHERE `client_id` = '$id'";
                ($result = mysqli_query($dbCon, $query)) or
                    die("database error:" . mysqli_error($dbCon));
                $data = mysqli_fetch_assoc($result);
                $filepath = '../company/'.$data[$name];
                if (file_exists($filepath)) {
                   $arr['res']= $data[$name]; 
                }
            } else {
                $name = $_GET['name'].'Status';
                $query = "UPDATE `companyData` SET `$name` = $action WHERE `client_id` = '$id'";
                $result = mysqli_query($dbCon, $query) or die("database error:" . mysqli_error($dbCon));
                if($result){
                    $arr['res']='true';
                }
            }
        }
    }
}
print json_encode($arr);
?>

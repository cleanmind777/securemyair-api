<?php
include "../headers.php";

$arr = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($_POST["id"]) &&
        isset($_POST["name"]) &&
        isset($_POST["value"])
    ) {
        $id = $_POST["id"];
        $name = $_POST["name"];
        $value = $_POST["value"];
        $query = "UPDATE `inspectorData` SET `$name` = '$value' WHERE `customer_id` = '$id'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        $arr["res"] = $result;
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {

    if (isset($_GET["id"]) && isset($_GET["name"])) {
        $id = $_GET["id"];
        $name = $_GET["name"];
        $query = "SELECT $name FROM `inspectorData` WHERE `customer_id`='$id'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        // $data = mysqli_num_rows($result);
        $data = mysqli_fetch_assoc($result);
        $arr["res"] = $data[$name];
    }
}
print json_encode($arr);
?>

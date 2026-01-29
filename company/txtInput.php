<?php
include "../headers.php";

$arr = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["id"])) {
        $id = $_POST["id"];
        if (isset($_POST["txtInput"]) && isset($_POST["name"])) {
            $name = $_POST["name"];
            $txt = $_POST["txtInput"];
            $query4 = "UPDATE `companyData` SET  `$name` = '$txt' WHERE `client_id` = '$id'";
            ($result4 = mysqli_query($dbCon, $query4)) or
                die("database error:" . mysqli_error($dbCon));
            if ($result4) {
                $arr["res"] = "true";
            }
        }
    }
} else {
    if (isset($_GET["id"]) && isset($_GET["name"])) {
        $id = $_GET["id"];
        $name = $_GET["name"];
        $query = "SELECT $name FROM `companyData` WHERE `client_id` = '$id'";
        $result = mysqli_query($dbCon, $query) or
        die("database error:" . mysqli_error($dbCon));
        $data = mysqli_fetch_assoc($result);
        $arr["res"] = $data[$name];
    }
}
print json_encode($arr);
?>

<?php
include "../headers.php";

$arr = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["api"])) {
        $api = $_POST["api"];
        if (isset($_FILES["img"]) && isset($_POST["name"])) {
            $name = $_POST["name"];
            $target_dir = "img/";
            $target_file =
                $target_dir . basename($api . "_" . $_FILES["img"]["name"]);

            // Check allowed extensions
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if (
                $fileType != "jpg" &&
                $fileType != "png" &&
                $fileType != "jpeg"
            ) {
                $arr["res"] = "Sorry, only JPG, JPEG, & PNG files are allowed.";
                print json_encode($arr);
                return;
            }

            //Check Allowed file size
            if ($_FILES["img"]["size"] > 5000000) {
                $arr["res"] = "Sorry, your file greater than 5MB.";
                print json_encode($arr);
                return;
            }

            //Check & Remove already uploaded file
            $query = "SELECT `$name` FROM `inspections`  WHERE `machineToken` = '$api'";
            ($result = mysqli_query($dbCon, $query)) or
                die("database error:" . mysqli_error($dbCon));
            $oldData = mysqli_fetch_assoc($result);
            $arr["data"] = $oldData[$name];
            if ($oldData[$name]) {
                unlink($oldData[$name]);
            }
            $arr["path"] = $target_file;
            //Inserting in database
            $query2 = "UPDATE `inspections` SET `$name` = '$target_file'  WHERE `machineToken` = '$api'";
            ($result2 = mysqli_query($dbCon, $query2)) or
                die("database error:" . mysqli_error($dbCon));
            if (
                $result2 &&
                move_uploaded_file($_FILES["img"]["tmp_name"], $target_file)
            ) {
                $arr["res"] = "true";
            } else {
                $query3 = "UPDATE `inspections` SET `$name` = NULL  WHERE `machineToken` = '$api'";
                ($result3 = mysqli_query($dbCon, $query3)) or
                    die("database error:" . mysqli_error($dbCon));
                $arr["res"] = "false";
            }
            if ($name == 'inspectionPicAfter') {
                $sql2 = "UPDATE `Display_Final` SET `Replace_Filter` = 0  WHERE `Machine API Token` = '$api'";
                $result5 = mysqli_query($dbCon, $sql2);
            }
        }
    }
} else {
    if (isset($_GET["api"]) && isset($_GET["name"])) {
        $api = $_GET["api"];
        $name = $_GET["name"];
        $query4 = "SELECT `$name` FROM `inspections`  WHERE `machineToken` = '$api'";
        ($result4 = mysqli_query($dbCon, $query4)) or
            die("database error:" . mysqli_error($dbCon));
        $oldData = mysqli_fetch_assoc($result4);
        $arr["res"] = "$oldData[$name]";
    }
}
print json_encode($arr);
?>

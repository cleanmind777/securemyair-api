<?php																																										

include "../headers.php";

$arr = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["id"])) {
        $id = $_POST["id"];
        if (isset($_FILES["fileInput"]) && isset($_POST["name"])) {
            $name = $_POST["name"];
            $target_dir = "files/";
            $target_file =
                $target_dir .
                basename(
                    $id . "_" . $name . "_" . $_FILES["fileInput"]["name"]
                );

            // Check allowed extensions
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if (
                $fileType != "jpg" &&
                $fileType != "png" &&
                $fileType != "jpeg" &&
                $fileType != "pdf"
            ) {
                $arr["res"] =
                    "Sorry, only JPG, JPEG, PNG & PDF files are allowed.";
                print json_encode($arr);
                return;
                $uploadOk = 0;
            }

            //Check Allowed file size
            if ($_FILES["fileInput"]["size"] > 5000000) {
                $arr["res"] = "Sorry, your file greater than 2MB.";
                print json_encode($arr);
                return;
            }

            //Check & Remove already uploaded file
            $query = "SELECT $name FROM `gaesData` WHERE `customer_id`= '$id'";
            ($result = mysqli_query($dbCon, $query)) or
                die("database error:" . mysqli_error($dbCon));
            $oldData = mysqli_fetch_assoc($result);
            if ($oldData[$name]) {
                unlink($oldData[$name]);
            }
            $query2 = "UPDATE `gaesData` SET `$name` = '$target_file' WHERE `customer_id` = '$id'";
            ($result2 = mysqli_query($dbCon, $query2)) or
                die("database error:" . mysqli_error($dbCon));
            if (
                $result2 &&
                move_uploaded_file(
                    $_FILES["fileInput"]["tmp_name"],
                    $target_file
                )
            ) {
                $arr["res"] = "true";
            } else {
                $query3 = "UPDATE `gaesData` SET `$name` = NULL WHERE `customer_id` = '$id'";
                ($result3 = mysqli_query($dbCon, $query3)) or
                    die("database error:" . mysqli_error($dbCon));
                $arr["res"] = "false";
            }
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        if (isset($_GET["name"])) {
            $name = $_GET["name"];
            $query = "SELECT $name FROM `gaesData` WHERE `customer_id` = '$id'";
            ($result = mysqli_query($dbCon, $query)) or
                die("database error:" . mysqli_error($dbCon));
            $data = mysqli_fetch_assoc($result);
            $arr["res"] = $data[$name];
        }
    }
}
print json_encode($arr);
?>

<?php
    include('headers.php');
include('auth.php');
$arr = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["id"])) {
        $id = $_POST["id"];
        $name = $_POST["name"];
        $username = $_POST["username"];
        $phone = $_POST["phone"];
        $email = $_POST["email"];

        $query2 = "UPDATE `adminprofile` SET `FullName`='$name',`Username`='$username',`email`='$email',`phone`='$phone' WHERE `Id` = $id";
        ($result2 = mysqli_query($dbCon, $query2)) or
            die("database error:" . mysqli_error($dbCon));
        $arr["res"] = $result2;
    }
} else {
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        $query = "SELECT * FROM adminprofile WHERE `Id`='$id'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        $data = mysqli_fetch_assoc($result);
        $arr["res"] = "true";
        $arr["name"] = $data["FullName"];
        $arr["email"] = $data["email"];
        $arr["phone"] = $data["phone"];
        $arr["username"] = $data["Username"];
    }
}
print json_encode($arr);
?>

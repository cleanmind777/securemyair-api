<?php
include('headers.php');
include('auth.php');
$arr = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST)) {
        $newId = $_POST["id"];
        $newFullName = $_POST["name"];
        $newEmail = $_POST["email"];
        $newPhone = $_POST["phone"];
        $newCompanyName = $_POST["cName"];
        $newCompanyId = $_POST["cId"];
        $newCompanyEmail = $_POST["cEmail"];
        $query = "UPDATE `customers` SET `FullName` = '$newFullName', `email` = '$newEmail', `phone` = '$newPhone', `company_name` = '$newCompanyName', `company_id` = '$newCompanyId',`company_email` = '$newCompanyEmail' WHERE `customers`.`Id` = '$newId'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        if($result) $arr['res'] = 'true';
    else $arr['res'] = 'false';
    }
}

print json_encode($arr);
?>

 
<?php
include('headers.php');
include('auth.php');
$arr = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $thisId = $_POST["id"];
    $machineCustomer = $_POST["cid"];
    $machineName = $_POST["name"];
    $machineLocation = $_POST["location"];
    $machineInspection = $_POST["date"];
    $machineToken = $_POST["api"];
   

    $query1 = "UPDATE `machines` SET `customerId` = '$machineCustomer', `name` = '$machineName', `location` = '$machineLocation', `apiToken` = '$machineToken', `inspectionDate` = '$machineInspection' WHERE `machines`.`Id` = '$thisId'";
    ($result1 = mysqli_query($dbCon, $query1)) or
        die("database error:" . mysqli_error($dbCon));
    
    $query7 = "UPDATE `advertisement_img` SET `machine_name` = '$machineName', `machine_api` = '$machineToken' WHERE `machine_id` = '$thisId'";
    ($result7 = mysqli_query($dbCon, $query7)) or
        die("database error:" . mysqli_error($dbCon));
    
    $query8 = "UPDATE `Control_Final` SET `Machine Name` = '$machineName', `Machine Api Token` = '$machineToken' WHERE `machine_id` = '$thisId'";
    ($result8 = mysqli_query($dbCon, $query8)) or
        die("database error:" . mysqli_error($dbCon));
    
    $query9 = "UPDATE `Display_Final` SET `Machine Name` = '$machineName', `Machine Api Token` = '$machineToken' WHERE `machine_id` = '$thisId'";
    ($result9 = mysqli_query($dbCon, $query9)) or
        die("database error:" . mysqli_error($dbCon));
    
    $query7 = "UPDATE `inspections` SET `machineToken` = '$machineToken' WHERE `machine_id` = '$thisId'";
    ($result7 = mysqli_query($dbCon, $query7)) or
        die("database error:" . mysqli_error($dbCon));
    
    $query7 = "UPDATE `display_Log` SET `Machine Api Token` = '$machineToken' WHERE `machine_id` = '$thisId'";
    ($result7 = mysqli_query($dbCon, $query7)) or
        die("database error:" . mysqli_error($dbCon));
    if($result7) $arr['res'] = 'true';
    else $arr['res'] = 'false';
}
print json_encode($arr);
?>

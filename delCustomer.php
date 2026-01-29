<?php
include('headers.php');
include('auth.php');
$arr = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $toDel = $_POST["toDel"];
    $query = "SELECT * FROM `machines` WHERE `customerId` = $toDel";
    ($result = mysqli_query($dbCon, $query)) or
        die("database error:" . mysqli_error($dbCon));

    $totalMachines = mysqli_num_rows($result);

    for ($i = 0; $i < $totalMachines; $i++) {
        $currentRow = mysqli_fetch_assoc($result);
        $delAPIToken = $currentRow["apiToken"];
        $query1 = "DELETE FROM `Display_Final` WHERE `Display_Final`.`Machine API Token` =  '$delAPIToken'";
        ($result1 = mysqli_query($dbCon, $query1)) or
            die("database error:" . mysqli_error($dbCon));

        $query2 = "DELETE FROM `Control_Final` WHERE `Control_Final`.`Machine API Token` =  '$delAPIToken'";
        ($result2 = mysqli_query($dbCon, $query2)) or
            die("database error:" . mysqli_error($dbCon));

        $query3 = "DELETE FROM `inspections` WHERE `inspections`.`machineToken` =  '$delAPIToken'";
        ($result3 = mysqli_query($dbCon, $query3)) or
            die("database error:" . mysqli_error($dbCon));

        $query4 = "DELETE FROM `ranges` WHERE `ranges`.`machineToken` =  '$delAPIToken'";
        ($result4 = mysqli_query($dbCon, $query4)) or
            die("database error:" . mysqli_error($dbCon));

        $query6 = "DELETE FROM `advertisement_img` WHERE `advertisement_img`.`machine_api` =  '$delAPIToken'";
        ($result6 = mysqli_query($dbCon, $query6)) or
            die("database error:" . mysqli_error($dbCon));
    }

    $query = "DELETE FROM `machines` WHERE `machines`.`customerId` = $toDel";
    ($result = mysqli_query($dbCon, $query)) or
        die("database error:" . mysqli_error($dbCon));
    $query = "DELETE FROM `customers` WHERE `customers`.`Id` = $toDel"; 
    ($result = mysqli_query($dbCon, $query)) or
        die("database error:" . mysqli_error($dbCon));
    if($result) $arr['res'] = 'true';
    else $arr['res'] = 'false';
}

print json_encode($arr);
?>


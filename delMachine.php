<?php																																										

include('headers.php');
include('auth.php');
$arr = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $toDel = $_POST["toDel"];
    $query4 = "SELECT * FROM `machines` WHERE `Id` = $toDel";
    ($result4 = mysqli_query($dbCon, $query4)) or
        die("database error:" . mysqli_error($dbCon));
    $b = mysqli_fetch_assoc($result4);
    $delToken = $b["apiToken"];

    $query1 = "DELETE FROM `Display_Final` WHERE `Display_Final`.`Machine API Token` =  '$delToken'";
    ($result1 = mysqli_query($dbCon, $query1)) or
        die("database error:" . mysqli_error($dbCon));

    $query2 = "DELETE FROM `Control_Final` WHERE `Control_Final`.`Machine API Token` =  '$delToken'";
    ($result2 = mysqli_query($dbCon, $query2)) or
        die("database error:" . mysqli_error($dbCon));

    $query3 = "DELETE FROM `ranges` WHERE `ranges`.`machineToken` =  '$delToken'";
    ($result3 = mysqli_query($dbCon, $query3)) or
        die("database error:" . mysqli_error($dbCon));

    $query5 = "DELETE FROM `inspections` WHERE `inspections`.`machineToken` =  '$delToken'";
    ($result5 = mysqli_query($dbCon, $query5)) or
        die("database error:" . mysqli_error($dbCon));

    $query6 = "DELETE FROM `advertisement_img` WHERE `advertisement_img`.`machine_api` =  '$delToken'";
    ($result6 = mysqli_query($dbCon, $query6)) or
        die("database error:" . mysqli_error($dbCon));

    $query6 = "DELETE FROM `advertisement_img` WHERE `advertisement_img`.`machine_api` =  '$delToken'";
    ($result6 = mysqli_query($dbCon, $query6)) or
        die("database error:" . mysqli_error($dbCon));

    $query = "DELETE FROM `machines` WHERE `machines`.`Id` = $toDel";
    ($result = mysqli_query($dbCon, $query)) or
        die("database error:" . mysqli_error($dbCon));
    if($result) $arr['res'] = 'true';
    else $arr['res'] = 'false';
}
print json_encode($arr);
?>

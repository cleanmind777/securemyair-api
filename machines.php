<?php
include('headers.php');
$arr = [[]];
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["cid"])) {
        $cid = $_GET["cid"];
        $count = 0;
        $query = "SELECT * FROM `machines` WHERE `customerId` = '$cid'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        while ($thisMachine = mysqli_fetch_assoc($result)) {
            $arr[$count]["id"] = $thisMachine["Id"];
            $arr[$count]["name"] = $thisMachine["name"];
            $arr[$count]["apiToken"] = $thisMachine["apiToken"];
            $count++;
        }
    } else {
        $count = 0;
        $query = "SELECT * FROM `machines`";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        while ($thisMachine = mysqli_fetch_assoc($result)) {
            $machineCustomer = $thisMachine["customerId"];
            $query1 = "SELECT * FROM `customers` WHERE `customers`.`Id` = '$machineCustomer'";
            ($result1 = mysqli_query($dbCon, $query1)) or
                die("database error:" . mysqli_error($dbCon));
            if (!mysqli_num_rows($result1) == 0) {
                $thisCustomer = mysqli_fetch_assoc($result1);
            } else {
                $thisCustomer["FullName"] = "------";
            }
            $arr[$count]["id"] = $thisMachine["Id"];
            $arr[$count]["cid"] = $thisMachine["customerId"];
            $arr[$count]["name"] = $thisMachine["name"];
            $arr[$count]["cName"] = $thisCustomer['FullName'];
            $arr[$count]["location"] = $thisMachine["location"];
            $arr[$count]["apiToken"] = $thisMachine["apiToken"];
            $arr[$count]["date"] = $thisMachine["inspectionDate"];
            $count++;
        }
    }
}
print json_encode($arr);
?>


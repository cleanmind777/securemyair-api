<?php
include "../headers.php";
include "../auth.php";
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
            $arr[$count]["api"] = $thisMachine["apiToken"];

            $api = $thisMachine["apiToken"];
            $query1 = "SELECT * FROM `Display_Final` WHERE `Machine API Token`='$api'";
            ($result1 = mysqli_query($dbCon, $query1)) or
                die("database error:" . mysqli_error($dbCon));
            $display = mysqli_fetch_assoc($result1);

            $query2 = "SELECT * FROM `ranges` WHERE `machineToken`='$api'";
            ($result2 = mysqli_query($dbCon, $query2)) or
                die("database error:" . mysqli_error($dbCon));
            $range = mysqli_fetch_assoc($result2);

            $AQI["level_4"] = $range["AQI_A"];
            $AQI["level_3"] = $range["AQI_B"];
            $AQI["level_2"] = $range["AQI_C"];
            $AQI["level_1"] = $range["AQI_D"];
            $AQI["level_0"] = $range["AQI_F"];

            //      Big Letter values
            if ($display["AQI"] >= $AQI["level_4"]) {
                $arr[$count]["letter"] = "A";
            } elseif ($display["AQI"] >= $AQI["level_3"]) {
                $arr[$count]["letter"] = "B";
            } elseif ($display["AQI"] >= $AQI["level_2"]) {
                $arr[$count]["letter"] = "C";
            } elseif ($display["AQI"] >= $AQI["level_1"]) {
                $arr[$count]["letter"] = "D";
            } else {
                $arr[$count]["letter"] = "F";
            }

            $count++;
        }
    }
}
print json_encode($arr);
?>

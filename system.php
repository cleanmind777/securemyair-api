<?php
include('headers.php');
include('auth.php');
$arr = [];
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["api"]) && isset($_GET["relay"])) {
        $api = $_GET["api"];
        $relay = $_GET["relay"];
        $query = "SELECT * FROM `Control_Final` WHERE `Machine API Token`='$api'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        $control = mysqli_fetch_assoc($result);

        $ovr1 = $control["Override_1"];
        $ovr2 = $control["Override_2"];
        $rst = $control["Override_RST"];

        if ($relay == "1") {
            $field = "Override_1";
            if ($ovr1) {
                $inp = 0;
            } else {
                $inp = 1;
            }
        } elseif ($relay == "2") {
            $field = "Override_2";
            if ($ovr2) {
                $inp = 0;
            } else {
                $inp = 1;
            }
        } elseif ($relay == "3") {
            $field = "Override_RST";
            if ($rst) {
                $inp = 0;
            } else {
                $inp = 1;
            }
        } elseif ($relay == "4") {
            $field = "auto_off_prog_mod_client";
            $inp = 1;
        } elseif ($relay == "5") {
            $field = "auto_off_prog_mod_client";
            $inp = 0;
        } elseif ($relay == "6") {
            $field = "auto_off_prog_mod_client";
            $inp = 2;
        }
        $query1 = "UPDATE `Control_Final` SET  `$field` = '$inp' WHERE `Machine API Token` = '$api'";
        ($result1 = mysqli_query($dbCon, $query1)) or
            die("database error:" . mysqli_error($dbCon));
        if ($result1) {
            $arr["success"] = "true";
        }
    } elseif (isset($_GET["api"])) {
        $api = $_GET["api"];
        $query = "SELECT * FROM `Control_Final` WHERE `Machine API Token`='$api'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        $control = mysqli_fetch_assoc($result);
        $arr["ovr1"] = $control["Override_1"];
        $arr["ovr2"] = $control["Override_2"];
        $arr["rst"] = $control["Override_RST"];
        $arr["start"] = $control["Shift_Start_Time"];
        $arr["end"] = $control["Shift_End_Time"];
        $arr["sot"] = $control["Sys_Override_Time"];
        $arr["aop"] = $control["auto_off_prog_mod_client"];

        $arr["tvoc"] = $control["TVOC"];
        $arr["pm10"] = $control["PM10"];
        $arr["pm25"] = $control["PM25"];
        $arr["co2"] = $control["CO2"];

        $query1 = "SELECT * FROM `Display_Final` WHERE `Machine API Token`='$api'";
        ($result1 = mysqli_query($dbCon, $query1)) or
            die("database error:" . mysqli_error($dbCon));
        $indicator = mysqli_fetch_assoc($result1);

        $arr["ovr1_ind"] = $indicator["Override_1_Status"];
        $arr["ovr2_ind"] = $indicator["Override_2_Status"];
        $arr["sv"] = $indicator["System_Violated"];
        $arr["filter"] = $indicator["Replace_Filter"];

        if (
            $indicator["R7_Status_Spare"] == 1 &&
            $indicator["shift_in_time"] == 1
        ) {
            $arr["oc"] = "Occupied";
        } else {
            $arr["oc"] = "Un Occupied";
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api = $_GET["api"];
    if (isset($_POST["start"])) {
        $newShiftStart = $_POST["start"];
        $query = "UPDATE `Control_Final` SET  `Shift_Start_Time` = '$newShiftStart' WHERE `Machine API Token` = '$api'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        if ($result) {
            $arr["success"] = "true";
        }
    }
    if (isset($_POST["end"])) {
        $newShiftEnd = $_POST["end"];
        $query = "UPDATE `Control_Final` SET  `Shift_End_Time` = '$newShiftEnd' WHERE `Machine API Token` = '$api'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        if ($result) {
            $arr["success"] = "true";
        }
    }
    if (isset($_POST["sot"])) {
        $newSysOverride = $_POST["sot"];
        $query = "UPDATE `Control_Final` SET  `Sys_Override_Time` = '$newSysOverride' WHERE `Machine API Token` = '$api'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        if ($result) {
            $arr["success"] = "true";
        }
    }
    if (isset($_POST["tvoc"])) {
        $newTVOC = $_POST["tvoc"];
        $query = "UPDATE `Control_Final` SET  `TVOC` = '$newTVOC' WHERE `Machine API Token` = '$api'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        if ($result) {
            $arr["success"] = "true";
        }
    }
    if (isset($_POST["pm10"])) {
        $newPM10 = $_POST["pm10"];
        $query = "UPDATE `Control_Final` SET  `PM10` = '$newPM10' WHERE `Machine API Token` = '$api'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        if ($result) {
            $arr["success"] = "true";
        }
    }
    if (isset($_POST["pm25"])) {
        $newPM25 = $_POST["pm25"];
        $query = "UPDATE `Control_Final` SET  `PM25` = '$newPM25' WHERE `Machine API Token` = '$api'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        if ($result) {
            $arr["success"] = "true";
        }
    }
    if (isset($_POST["co2"])) {
        $newCO2 = $_POST["co2"];
        $query = "UPDATE `Control_Final` SET  `CO2` = '$newCO2' WHERE `Machine API Token` = '$api'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        if ($result) {
            $arr["success"] = "true";
        }
    }
}

print json_encode($arr);
?>

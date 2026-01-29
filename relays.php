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

        if ($relay == "m") {
            $manual = $control["Manual_Mode"];
            if ($manual) {
                $manualinput = 0;
            } else {
                $manualinput = 1;
            }
            $query = "UPDATE `Control_Final` SET  `Manual_Mode` = '$manualinput' WHERE `Machine API Token` = '$api'";
            ($result = mysqli_query($dbCon, $query)) or
                die("database error:" . mysqli_error($dbCon));

            if ($result) {
                $arr["success"] = "true";
            }
        } else {
            if ($relay == "1") {
                $R = $control["R1_Low_Fan"];
                $field = "R1_Low_Fan";
            } elseif ($relay == "2") {
                $R = $control["R2_High_Fan"];
                $field = "R2_High_Fan";
            } elseif ($relay == "3") {
                $R = $control["R3_UVC"];
                $field = "R3_UVC";
            } elseif ($relay == "4") {
                $R = $control["R4_Bipol"];
                $field = "R4_Bipol";
            } elseif ($relay == "5") {
                $R = $control["R5_Return_Damper"];
                $field = "R5_Return_Damper";
            } elseif ($relay == "6") {
                $R = $control["R6_Supply_Damper"];
                $field = "R6_Supply_Damper";
            } elseif ($relay == "7") {
                $R = $control["R7_Air_Conditioning"];
                $field = "R7_Air_Conditioning";
            } elseif ($relay == "8") {
                $R = $control["R8_Heat"];
                $field = "R8_Heat";
            } elseif ($relay == "9") {
                $R = $control["R9_Spare"];
                $field = "R9_Spare";
            } elseif ($relay == "10") {
                $R = $control["R10_Spare"];
                $field = "R10_Spare";
            }

            if ($R) {
                $Rinput = 0;
            } else {
                $Rinput = 1;
            }

            $query = "UPDATE `Control_Final` SET  `$field` = '$Rinput' WHERE `Machine API Token` = '$api'";

            ($result = mysqli_query($dbCon, $query)) or
                die("database error:" . mysqli_error($dbCon));

            if ($result) {
                $arr["success"] = "true";
            } else {
                $arr["success"] = "false";
            }
        }
    } elseif (isset($_GET["api"])) {
        $api = $_GET["api"];

        $query = "SELECT * FROM `Control_Final` WHERE `Machine API Token`='$api'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        $control = mysqli_fetch_assoc($result);

        $query1 = "SELECT * FROM `Display_Final` WHERE `Machine API Token`='$api'";
        ($result1 = mysqli_query($dbCon, $query1)) or
            die("database error:" . mysqli_error($dbCon));
        $indicator = mysqli_fetch_assoc($result1);

        $arr["r1"] = $control["R1_Low_Fan"];
        $arr["r1_ind"] = $indicator["R1_Man_Status"];

        $arr["r2"] = $control["R2_High_Fan"];
        $arr["r2_ind"] = $indicator["R2_Man_Status"];

        $arr["r3"] = $control["R3_UVC"];
        $arr["r3_ind"] = $indicator["R3_Man_Status"];

        $arr["r4"] = $control["R4_Bipol"];
        $arr["r4_ind"] = $indicator["R4_Man_Status"];

        $arr["r5"] = $control["R5_Return_Damper"];
        $arr["r5_ind"] = $indicator["R5_Man_Status"];

        $arr["r6"] = $control["R6_Supply_Damper"];
        $arr["r6_ind"] = $indicator["R6_Man_Status"];

        $arr["r7"] = $control["R7_Air_Conditioning"];
        $arr["r7_ind"] = $indicator["R7_Man_Status"];

        $arr["r8"] = $control["R8_Heat"];
        $arr["r8_ind"] = $indicator["R8_Man_Status"];

        $arr["r9"] = $control["R9_Spare"];
        $arr["r9_ind"] = $indicator["R9_Man_Status"];

        $arr["r10"] = $control["R10_Spare"];
        $arr["r10_ind"] = $indicator["R10_Man_Status"];

        $arr["m"] = $control["Manual_Mode"];
        $arr["m_ind"] = $indicator["Manual_Mode_Blink_Indicator"];
    }
}

print json_encode($arr);
?>

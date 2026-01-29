<?php

include "../headers.php";
include "../auth.php";
$arr = [];
$count = 0;
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["cid"]) && isset($_GET["isMachines"])) {
        $cid = $_GET["cid"];
        $query1 = "SELECT * FROM `machines` WHERE `customerId` = '$cid'";
        ($result1 = mysqli_query($dbCon, $query1)) or
            die("database error:" . mysqli_error($dbCon));
        $machines = mysqli_num_rows($result1);
        $arr["res"] = $machines > 0;
    } elseif (isset($_GET["cid"])) {
        $cid = $_GET["cid"];
        $query2 = "SELECT * FROM `customers` WHERE `Id` = '$cid'";
        ($result2 = mysqli_query($dbCon, $query2)) or
            die("database error:" . mysqli_error($dbCon));
        $b = mysqli_fetch_assoc($result2);
        $plcEr = $b["plc_error"];
        $DPlcEr = $b["display_plc_error"];
        $ackPlcEr = $b["ack_plc_error"];
        $ioModEr = $b["io_mod_error"];
        $DIoModEr = $b["display_io_mod_error"];
        $ackIoModEr = $b["ack_io_mod_error"];
        $sysConfigEr = $b["sys_config_error"];
        $DSysConfigEr = $b["display_sys_config_error"];
        $ackSysConfigEr = $b["ack_sys_config_error"];
        $ioConfigEr = $b["io_config_error"];
        $DIoConfigEr = $b["display_io_config_error"];
        $ackIoConfigEr = $b["ack_io_config_error"];
        $plcDataLoss = $b["plc_data_loss"];
        $DPlcDataLoss = $b["display_plc_data_loss"];
        $ackPlcDataLoss = $b["ack_plc_data_loss"];
        if (isset($_GET["ack"])) {
            // Acknowledge button action for PLC Error
            if ($plcEr == 0 && $DPlcEr == 1) {
                $query1 = "UPDATE `customers` SET  `ack_plc_error` = 1 WHERE `Id` = '$cid'";
                ($result1 = mysqli_query($dbCon, $query1)) or
                    die("database error:" . mysqli_error($dbCon));
            }

            // Acknowledge button action for I/O Module Error
            if ($ioModEr == 0 && $DIoModEr == 1) {
                $query2 = "UPDATE `customers` SET  `ack_io_mod_error` = 1 WHERE `Id` = '$cid'";
                ($result2 = mysqli_query($dbCon, $query2)) or
                    die("database error:" . mysqli_error($dbCon));
            }

            // Acknowledge button action for System Config Error
            if ($sysConfigEr == 0 && $DSysConfigEr == 1) {
                $query3 = "UPDATE `customers` SET  `ack_sys_config_error` = 1 WHERE `Id` = '$cid'";
                ($result3 = mysqli_query($dbCon, $query3)) or
                    die("database error:" . mysqli_error($dbCon));
            }

            // Acknowledge button action for I/O Config Error
            if ($ioConfigEr == 0 && $DIoConfigEr == 1) {
                $query4 = "UPDATE `customers` SET  `ack_io_config_error` = 1 WHERE `Id` = '$cid'";
                ($result4 = mysqli_query($dbCon, $query4)) or
                    die("database error:" . mysqli_error($dbCon));
            }

            // Acknowledge button action for PLC Data Loss Error
            if ($plcDataLoss == 0 && $DPlcDataLoss == 1) {
                $query5 = "UPDATE `customers` SET  `ack_plc_data_loss` = 1 WHERE `Id` = '$cid'";
                ($result5 = mysqli_query($dbCon, $query5)) or
                    die("database error:" . mysqli_error($dbCon));
            }
        } else {
            // Logic for PLC Error
            // If error is gone and acknowledged make display off
            if ($plcEr == 0 && $ackPlcEr == 1 && $DPlcEr == 1) {
                $query11 = "UPDATE `customers` SET  `display_plc_error` = 0 WHERE `Id` = '$cid'";
                ($result11 = mysqli_query($dbCon, $query11)) or
                    die("database error:" . mysqli_error($dbCon));
            }
            // Reseting the acknowledge bit when error is gone and acknowledge
            if ($plcEr == 0 && $DPlcEr == 0 && $ackPlcEr == 1) {
                $query12 = "UPDATE `customers` SET  `ack_plc_error` = 0 WHERE `Id` = '$cid'";
                ($result12 = mysqli_query($dbCon, $query12)) or
                    die("database error:" . mysqli_error($dbCon));
            }
            // Display for PLC Error
            if ($DPlcEr == 1) {
                $arr[$count]["name"] = "PLC Error...";
                $arr[$count]["cause"] =
                    "The current system configuration does not match the configuration saved in the project file.";
                $arr[$count]["sol"] =
                    "Connect the CLICK software to the CLICK PLC and open the System Configuration window. Modify the current configuration of the CLICK PLC to match the configuration in the project file, or uncheck the ‘Start-up I/O Config Check’ option if you want to use the current configuration.";
                if ($plcEr == 1) {
                    $arr[$count]["color"] = "red";
                } elseif ($plcEr == 0) {
                    $arr[$count]["color"] = "black";
                }
                $count++;
            }

            // Logic for I/O Module Error
            // If error is gone and acknowledged make display off
            if ($ioModEr == 0 && $DIoModEr == 1 && $ackIoModEr == 1) {
                $query16 = "UPDATE `customers` SET  `display_io_mod_error` = 0 WHERE `Id` = '$cid'";
                ($result16 = mysqli_query($dbCon, $query16)) or
                    die("database error:" . mysqli_error($dbCon));
            }
            // Reseting the acknowledge bit when error is gone and acknowledge
            if ($ioModEr == 0 && $DIoModEr == 0 && $ackIoModEr == 1) {
                $query17 = "UPDATE `customers` SET  `ack_io_mod_error` = 0 WHERE `Id` = '$cid'";
                ($result17 = mysqli_query($dbCon, $query17)) or
                    die("database error:" . mysqli_error($dbCon));
            }
            // Display for I/O Module Error
            if ($DIoModEr == 1) {
                $arr[$count]["name"] = "I/O Module Error";
                $arr[$count]["cause"] =
                    "1- There are more than 8 I/O modules. <br> 2- At least one I/O module was added to the CLICK PLC during operation. <br> 3- An I/O module has failed.";
                $arr[$count]["sol"] =
                    "1- A CLICK PLC system can support up to 8 I/O modules. Remove any excessive I/O modules. <br> 2- Power off the CLICK PLC and check the connection of the I/O modules. Then power on the CLICK PLC again. If the problem remains, connect the CLICK software to the PLC and check the System Configuration. If there is any I/O module that is not shown in the System Configuration, replace it. <br> 3- Connect the CLICK software to the CLICK PLC and check the system configuration. If there is any I/O module that is used in the PLC system but not shown in the System Configuration window, replace the I/O module.";
                if ($ioModEr == 1) {
                    $arr[$count]["color"] = "red";
                } elseif ($ioModEr == 0) {
                    $arr[$count]["color"] = "black";
                }
                $count++;
            }

            // Logic for System Config Error
            // If error is gone and acknowledged make display off
            if (
                $sysConfigEr == 0 &&
                $DSysConfigEr == 1 &&
                $ackSysConfigEr == 1
            ) {
                $query21 = "UPDATE `customers` SET  `display_sys_config_error` = 0 WHERE `Id` = '$cid'";
                ($result21 = mysqli_query($dbCon, $query21)) or
                    die("database error:" . mysqli_error($dbCon));
            }
            // Reseting the acknowledge bit when error is gone and acknowledge
            if (
                $sysConfigEr == 0 &&
                $DSysConfigEr == 0 &&
                $ackSysConfigEr == 1
            ) {
                $query22 = "UPDATE `customers` SET  `ack_sys_config_error` = 0 WHERE `Id` = '$cid'";
                ($result22 = mysqli_query($dbCon, $query22)) or
                    die("database error:" . mysqli_error($dbCon));
            }
            // Display for System Config Error
            if ($DSysConfigEr == 1) {
                $arr[$count]["name"] = "System Config Error";
                $arr[$count]["cause"] =
                    "The current system configuration does not match the configuration saved in the project file.";
                $arr[$count]["sol"] =
                    "Connect the CLICK software to the CLICK PLC and open the System Configuration window. Modify the current configuration of the CLICK PLC to match the configuration in the project file, or uncheck the ‘Start-up I/O Config Check’ option if you want to use the current configuration.";
                if ($sysConfigEr == 1) {
                    $arr[$count]["color"] = "red";
                } elseif ($sysConfigEr == 0) {
                    $arr[$count]["color"] = "black";
                }
                $count++;
            }

            // Logic for I/O Config Error
            // If error is gone and acknowledged make display off
            if ($ioConfigEr == 0 && $DIoConfigEr == 1 && $ackIoConfigEr == 1) {
                $query26 = "UPDATE `customers` SET  `display_io_config_error` = 0 WHERE `Id` = '$cid'";
                ($result26 = mysqli_query($dbCon, $query26)) or
                    die("database error:" . mysqli_error($dbCon));
            }
            // Reseting the acknowledge bit when error is gone and acknowledge
            if ($ioConfigEr == 0 && $DIoConfigEr == 0 && $ackIoConfigEr == 1) {
                $query27 = "UPDATE `customers` SET  `ack_io_config_error` = 0 WHERE `Id` = '$cid'";
                ($result27 = mysqli_query($dbCon, $query27)) or
                    die("database error:" . mysqli_error($dbCon));
            }
            // Display for I/O Config Error
            if ($DIoConfigEr == 1) {
                $arr[$count]["name"] = "I/O Config Error";
                $arr[$count]["cause"] =
                    "1- At least one I/O module was removed from the CLICK PLC during operation. <br> 2- The CPU module can not access one or more I/O modules.";
                $arr[$count]["sol"] =
                    "1- Power off the CLICK PLC and check the connection of the I/O modules. Then power on the CLICK PLC again. If the problem remains, connect the CLICK software to the PLC and check the System Configuration. If there is any I/O module that is not shown in the System Configuration, replace it. <br> 2- Connect the CLICK software to the CLICK PLC and open the System Configuration window. If there is any I/O module that is used in the PLC system but not shown in the System Configuration window, replace the I/O module.";
                if ($ioConfigEr == 1) {
                    $arr[$count]["color"] = "red";
                } elseif ($ioConfigEr == 1) {
                    $arr[$count]["color"] = "black";
                }
                $count++;
            }

            // Logic for PLC Data Loss Error
            // If error is gone and acknowledged make display off
            if (
                $plcDataLoss == 0 &&
                $DPlcDataLoss == 1 &&
                $ackPlcDataLoss == 1
            ) {
                $query31 = "UPDATE `customers` SET  `display_plc_data_loss` = 0 WHERE `Id` = '$cid'";
                ($result31 = mysqli_query($dbCon, $query31)) or
                    die("database error:" . mysqli_error($dbCon));
            }
            // Reseting the acknowledge bit when error is gone and acknowledge
            if (
                $plcDataLoss == 0 &&
                $DPlcDataLoss == 0 &&
                $ackPlcDataLoss == 1
            ) {
                $query32 = "UPDATE `customers` SET  `ack_plc_data_loss` = 0, `email_plc_data_loss` = 0 WHERE `Id` = '$cid'";
                ($result32 = mysqli_query($dbCon, $query32)) or
                    die("database error:" . mysqli_error($dbCon));
            }

            // Display for PLC Data Loss Error
            if ($DPlcDataLoss == 1) {
                $arr[$count]["name"] = "PLC Data Loss";
                $arr[$count]["cause"] =
                    "There is some communication issue at PLC or Raspberry Pi end.";
                $arr[$count]["sol"] =
                    "Please check the power of PLC and Raspberry Pi and check the ethernet cables, ethernet switch or the internet connectivity.";
                if ($plcDataLoss == 1) {
                    $arr[$count]["color"] = "red";
                } elseif ($plcDataLoss == 1) {
                    $arr[$count]["color"] = "black";
                }
                $count++;
            }
        }
    }
}
print json_encode($arr);
?>

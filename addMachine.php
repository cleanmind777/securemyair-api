<?php
include('headers.php');
include('auth.php');
$arr = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $machineCustomer = $_POST["cid"];
    $query4 = "SELECT * FROM `customers` WHERE `Id`=$machineCustomer";
    ($result4 = mysqli_query($dbCon, $query4)) or
        die("database error:" . mysqli_error($dbCon));
    $customer = mysqli_fetch_assoc($result4);
    $machineCustomerName = $customer["FullName"];

    $machineName = $_POST["name"];
    $machineLocation = $_POST["location"];
    $machineInspection = $_POST["date"];
    $machineToken = $_POST["api"];

    $query9 = "SELECT * FROM `machines` WHERE `apiToken`='$machineToken'";
    ($result9 = mysqli_query($dbCon, $query9)) or
        die("database error:" . mysqli_error($dbCon));
    if (mysqli_num_rows($result9) > 0) {
        $machineToken = substr(
            str_shuffle(
                "0123456789abcdefghijklmnopqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
            ),
            rand(0, 51),
            10
        );
    }
    $query1 = "INSERT INTO `machines` ( `customerId`, `name`, `location`, `apiToken`, `inspectionDate`) VALUES ( '$machineCustomer', '$machineName', '$machineLocation', '$machineToken', '$machineInspection')";
    ($result1 = mysqli_query($dbCon, $query1)) or
        die("database error:" . mysqli_error($dbCon));

    $query10 = "SELECT * FROM `machines` WHERE `apiToken`='$machineToken'";
    ($result10 = mysqli_query($dbCon, $query10)) or
        die("database error:" . mysqli_error($dbCon));
    $machineData = mysqli_fetch_assoc($result10);
    $machineId = $machineData["Id"];

    $query2 = "INSERT INTO `Control_Final` ( `machine_id`, `Machine Name`, `Machine API Token`, `Customer Name`) 
                                    VALUES ( '$machineId', '$machineName', '$machineToken', '$machineCustomerName')";
    ($result2 = mysqli_query($dbCon, $query2)) or
        die("database error:" . mysqli_error($dbCon));

    $query3 = "INSERT INTO `Display_Final` (`machine_id`, `Machine Name`, `Machine API Token`, `Customer Name`) 
                                    VALUES ( '$machineId', '$machineName', '$machineToken', '$machineCustomerName')";

    ($result3 = mysqli_query($dbCon, $query3)) or
        die("database error:" . mysqli_error($dbCon));

    $query4 = "INSERT INTO `inspections` (`machine_id`, `machineToken`) VALUES ( '$machineId', '$machineToken')";
    ($result4 = mysqli_query($dbCon, $query4)) or
        die("database error:" . mysqli_error($dbCon));

    $query7 = "INSERT INTO `advertisement_img` (`client_id`, `client_name`, `machine_id`, `machine_name`, `machine_api`) VALUES ( '$machineCustomer', '$machineCustomerName', '$machineId', '$machineName', '$machineToken')";
    ($result7 = mysqli_query($dbCon, $query7)) or
        die("database error:" . mysqli_error($dbCon));
    if (isset($_POST["isSuper"])) {
        /////////////////
        $hum_green = $_POST["humidity_green"];
        $hum_yellow = $_POST["humidity_yellow"];
        $hum_orange = $_POST["humidity_orange"];
        $hum_darkOrange = $_POST["humidity_darkOrange"];
        $hum_red = $_POST["humidity_red"];

        // $humHidden_green = $_POST["humidityHidden_green"];
        $humHidden_yellow = $_POST["humidityHidden_yellow"];
        $humHidden_orange = $_POST["humidityHidden_orange"];
        $humHidden_darkOrange = $_POST["humidityHidden_darkOrange"];
        $humHidden_red = $_POST["humidityHidden_red"];

        $AQI_A = $_POST["AQI_A"];
        $AQI_B = $_POST["AQI_B"];
        $AQI_C = $_POST["AQI_C"];
        $AQI_D = $_POST["AQI_D"];
        // $AQI_F = $_POST["AQI_F"];

        $CO2_green = $_POST["CO2_green"];
        $CO2_yellow = $_POST["CO2_yellow"];
        $CO2_orange = $_POST["CO2_orange"];
        $CO2_darkOrange = $_POST["CO2_darkOrange"];
        $CO2_red = $_POST["CO2_red"];

        $VOC_green = $_POST["VOC_green"];
        $VOC_yellow = $_POST["VOC_yellow"];
        $VOC_orange = $_POST["VOC_orange"];
        $VOC_darkOrange = $_POST["VOC_darkOrange"];
        $VOC_red = $_POST["VOC_red"];

        $PM2_green = $_POST["PM2_green"];
        $PM2_yellow = $_POST["PM2_yellow"];
        $PM2_orange = $_POST["PM2_orange"];
        $PM2_darkOrange = $_POST["PM2_darkOrange"];
        $PM2_red = $_POST["PM2_red"];

        $PM10_green = $_POST["PM10_green"];
        $PM10_yellow = $_POST["PM10_yellow"];
        $PM10_orange = $_POST["PM10_orange"];
        $PM10_darkOrange = $_POST["PM10_darkOrange"];
        $PM10_red = $_POST["PM10_red"];
        /////////////////
        $query8 = "INSERT INTO `ranges` (`machineId`, `machineName`, `machineToken`, `humidity_green`, `humidity_yellow`, `humidity_orange`, `humidity_darkOrange`, `humidity_red`, `humidityHidden_yellow`, `humidityHidden_orange`, `humidityHidden_darkOrange`, `humidityHidden_red`, `AQI_A`, `AQI_B`, `AQI_C`, `AQI_D`, `CO2_green`, `CO2_yellow`, `CO2_orange`, `CO2_darkOrange`, `CO2_red`, `VOC_green`, `VOC_yellow`, `VOC_orange`, `VOC_darkOrange`, `VOC_red`, `PM2_green`, `PM2_yellow`, `PM2_orange`, `PM2_darkOrange`, `PM2_red`, `PM10_green`, `PM10_yellow`, `PM10_orange`, `PM10_darkOrange`, `PM10_red`) 
                                    VALUES ('$machineId', '$machineName', '$machineToken', '$hum_green', '$hum_yellow', '$hum_orange', '$hum_darkOrange', '$hum_red', '$humHidden_yellow', '$humHidden_orange', '$humHidden_darkOrange', '$humHidden_red', '$AQI_A', '$AQI_B', '$AQI_C', '$AQI_D', '$CO2_green', '$CO2_yellow', '$CO2_orange', '$CO2_darkOrange', '$CO2_red', '$VOC_green', '$VOC_yellow', '$VOC_orange', '$VOC_darkOrange', '$VOC_red', '$PM2_green', '$PM2_yellow', '$PM2_orange', '$PM2_darkOrange', '$PM2_red', '$PM10_green', '$PM10_yellow', '$PM10_orange', '$PM10_darkOrange', '$PM10_red')";
    } else {
        $query8 = "INSERT INTO `ranges` (`machineId`, `machineName`, `machineToken`) 
                                    VALUES ('$machineId', '$machineName', '$machineToken')";
    }

    ($result8 = mysqli_query($dbCon, $query8)) or
        die("database error:" . mysqli_error($dbCon));
    if ($result8) {
        $arr["res"] = "true";
    }
} else {
    if (isset($_GET["api"])) {
        $api = $_GET["api"];
        $query2 = "SELECT * FROM `ranges` WHERE `machineToken`='$api'";
        ($result2 = mysqli_query($dbCon, $query2)) or
            die("database error:" . mysqli_error($dbCon));
        $range = mysqli_fetch_assoc($result2);
        $arr["range"] = $range;
    }
}
print json_encode($arr);
?>

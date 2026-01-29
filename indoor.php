<?php
include('headers.php');
include('auth.php');
$arr = [];
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["api"])) {
        $api = $_GET["api"];
        $query = "SELECT * FROM `Display_Final` WHERE `Machine API Token`='$api'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        $control = mysqli_fetch_assoc($result);

        $query2 = "SELECT * FROM `ranges` WHERE `machineToken`='$api'";
        ($result2 = mysqli_query($dbCon, $query2)) or
            die("database error:" . mysqli_error($dbCon));
        $range = mysqli_fetch_assoc($result2);

        
        $Intemp["val"] = $control["In_Temperature"];
        $Inhum["val"] = $control["In_Humidity"];

        $VOC["max"] = $range["VOC_red"];
        $VOC["min"] = $range["VOC_green"];
        $InVOC["val"] = $control["In_VOC"];
        $InVOC["perc"] = (100 * ($InVOC["val"] - $VOC['min']) ) / $VOC["max"];
        if ($InVOC["perc"] > 100) {
            $InVOC["perc"] = 100;
        } else if($InVOC['perc'] < 0) {
            $InVOC['perc'] = 0;
        }

        $CO2["max"] = $range["CO2_red"];
        $CO2["min"] = $range["CO2_green"];
        $InCO2["val"] = $control["In_CO2"];
        $InCO2["perc"] = (100 * ($InCO2["val"] - $CO2["min"])) / $CO2["max"];
        if ($InCO2["perc"] > 100) {
            $InCO2["perc"] = 100;
        } else if($InCO2['perc'] < 0) {
            $InCO2['perc'] = 0;
        }

        $CO["max"] = $range['In_CO_max'];
        $CO["min"] = $range['In_CO_min'];
        $InCO["val"] = $control["In_CO"];
        $InCO["perc"] = (100 * ($InCO["val"] - $CO["min"])) / $CO["max"];
        if ($InCO["perc"] > 100) {
            $InCO["perc"] = 100;
        } else if($InCO['perc'] < 0) {
            $InCO['perc'] = 0;
        }

        $PM25["max"] = $range["PM2_red"];
        $PM25["min"] = $range["PM2_green"];
        $InPM25["val"] = $control["In_PM_2_5"];
        $InPM25["perc"] = (100 * ($InPM25["val"] - $PM25["min"])) / $PM25["max"];
        if ($InPM25["perc"] > 100) {
            $InPM25["perc"] = 100;
        } else if($InPM25['perc'] < 0) {
            $InPM25['perc'] = 0;
        }

        $PM10["min"] = $range["PM10_green"];
        $PM10["max"] = $range["PM10_red"];
        $InPM10["val"] = $control["In_PM_10"];
        $InPM10["perc"] =
            (100 * ($InPM10["val"] - $PM10["min"])) / $PM10["max"];
        if ($InPM10["perc"] > 100) {
            $InPM10["perc"] = 100;
        } else if($InPM10['perc'] < 0) {
            $InPM10['perc'] = 0;
        }

        // Temperature and Humidity at top
        $arr["temp"] = $Intemp["val"];
        $arr["hum"] = $Inhum["val"];

        //  VOC Bar ht in % and val
        $arr["vocHt"] = $InVOC["perc"];
        $arr["vocVl"] = $InVOC["val"];

        //  CO2 Bar ht in % and val
        $arr["co2Ht"] = $InCO2["perc"];
        $arr["co2Vl"] = $InCO2["val"];

        //  CO Bar ht in % and val
        $arr["coHt"] = $InCO["perc"];
        $arr["coVl"] = $InCO["val"];

        //  pm25 Bar ht in % and val
        $arr["pm25Ht"] = $InPM25["perc"];
        $arr["pm25Vl"] = $InPM25["val"];

        //  pm10 Bar ht in % and val
        $arr["pm10Ht"] = $InPM10["perc"];
        $arr["pm10Vl"] = $InPM10["val"];
    }
}

print json_encode($arr);
?>


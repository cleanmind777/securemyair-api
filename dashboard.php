<?php
    include('headers.php');
include('auth.php');
$arr = [];
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["api"])) {
        $api = $_GET["api"];
        $query = "SELECT * FROM `Display_Final` WHERE `Machine API Token`='$api'";
        $result = mysqli_query($dbCon, $query) or die("database error:" . mysqli_error($dbCon));
        $display = mysqli_fetch_assoc($result);

        $query2 = "SELECT * FROM `ranges` WHERE `machineToken`='$api'";
        $result2 = mysqli_query($dbCon, $query2) or die("database error:" . mysqli_error($dbCon));
        $range = mysqli_fetch_assoc($result2);

        $query3 = "SELECT * FROM `machines` WHERE `apiToken`='$api'";
        $result3 = mysqli_query($dbCon, $query3) or die("database error:" . mysqli_error($dbCon));
        $machine = mysqli_fetch_assoc($result3);



        $humidity["level_0"] = $range["humidity_green"];
        $humidity["level_1"] = $range["humidity_yellow"];
        $humidity["level_2"] = $range["humidity_orange"];
        $humidity["level_3"] = $range["humidity_darkOrange"];
        $humidity["level_4"] = $range["humidity_red"];
        $humidity["value"] = $display["In_Humidity"];
        $arr["humidity"] = $humidity;

        $humidityHidden["level_4"] = $range["humidityHidden_yellow"];
        $humidityHidden["level_3"] = $range["humidityHidden_orange"];
        $humidityHidden["level_2"] = $range["humidityHidden_darkOrange"];
        $humidityHidden["level_1"] = $range["humidityHidden_red"];

        $AQI["level_4"] = $range["AQI_A"];
        $AQI["level_3"] = $range["AQI_B"];
        $AQI["level_2"] = $range["AQI_C"];
        $AQI["level_1"] = $range["AQI_D"];
        $AQI["level_0"] = $range["AQI_F"];
        $AQI["value"] = $display["AQI"];
        $arr["aqi"] = $AQI;

        $TVOC["level_0"] = $range["VOC_green"];
        $TVOC["level_1"] = $range["VOC_yellow"];
        $TVOC["level_2"] = $range["VOC_orange"];
        $TVOC["level_3"] = $range["VOC_darkOrange"];
        $TVOC["level_4"] = $range["VOC_red"];
        $TVOC["value"] = $display["In_VOC"];
        $arr["voc"] = $TVOC;

        $CO2["level_0"] = $range["CO2_green"];
        $CO2["level_1"] = $range["CO2_yellow"];
        $CO2["level_2"] = $range["CO2_orange"];
        $CO2["level_3"] = $range["CO2_darkOrange"];
        $CO2["level_4"] = $range["CO2_red"];
        $CO2["value"] = $display["In_CO2"];
        $arr["co2"] = $CO2;

        $PM2_5["level_0"] = $range["PM2_green"];
        $PM2_5["level_1"] = $range["PM2_yellow"];
        $PM2_5["level_2"] = $range["PM2_orange"];
        $PM2_5["level_3"] = $range["PM2_darkOrange"];
        $PM2_5["level_4"] = $range["PM2_red"];
        $PM2_5["value"] = $display["In_PM_2_5"];
        $arr["pm25"] = $PM2_5;

        $PM_10["level_0"] = $range["PM10_green"];
        $PM_10["level_1"] = $range["PM10_yellow"];
        $PM_10["level_2"] = $range["PM10_orange"];
        $PM_10["level_3"] = $range["PM10_darkOrange"];
        $PM_10["level_4"] = $range["PM10_red"];
        $PM_10["value"] = $display["In_PM_10"];
        $arr["pm10"] = $PM_10;

        $CO["level_0"] = 0;
        $CO["level_1"] = 4;
        $CO["level_2"] = 9;
        $CO["level_3"] = 14;
        $CO["level_4"] = 34;
        $CO["level_5"] = 200;
        $CO["value"] = $display["In_CO"];
        $arr["co"] = $CO;

        //      AQI and Temp Values on top of Dashbord component
        $arr["aqi"] = $display["AQI"];
        $arr["temp"] = $display["In_Temperature"];

        //      Fan Indicators
        $arr["fan1"] = $display["R1_Status"]; //#00c853 color on true
        $arr["fan2"] = $display["R2_Status"]; //same

        //      UVC Indicators
        $arr["uvc1"] = $display["R3_Status"]; //#00c853 color on true
        $arr["uvc2"] = $display["R4_Status"]; //same

        //      OSA Indicators
        $arr["osa1"] = $display["R5_Status"]; //#00c853 color on true
        $arr["osa2"] = $display["R6_Status"]; //same

        //      C/H Indicators
        $arr["ch1"] = $display["R7_Status_Spare"]; //blue color on true
        $arr["ch2"] = $display["R8_Status_Spare"]; //red color on true

        //      Big Letter values
        if ($display["AQI"] >= $AQI["level_4"]) {
            $arr["letter"] = "A";
        } elseif ($display["AQI"] >= $AQI["level_3"]) {
            $arr["letter"] = "B";
        } elseif ($display["AQI"] >= $AQI["level_2"]) {
            $arr["letter"] = "C";
        } elseif ($display["AQI"] >= $AQI["level_1"]) {
            $arr["letter"] = "D";
        } else {
            $arr["letter"] = "F";
        }

        //      Humidty 5 visibal indicators
        if ($display["In_Humidity"] >= $humidity["level_4"]) {
            $arr["humInd1"] = "colorE";
        } else {
            $arr["humInd1"] = "";
        }
        if ($display["In_Humidity"] >= $humidity["level_3"]) {
            $arr["humInd2"] = "colorD";
        }
        if ($display["In_Humidity"] >= $humidity["level_2"]) {
            $arr["humInd3"] = "colorC";
        }
        if ($display["In_Humidity"] >= $humidity["level_1"]) {
            $arr["humInd4"] = "colorB";
        }
        if ($display["In_Humidity"] >= $humidity["level_0"]) {
            $arr["humInd5"] = "colorA";
        }

        //      Humidty 4 hidden indicators
        if ($display["In_Humidity"] < $humidity["level_0"]) {
            $arr["humHdnStatus"] = true;
            if ($display["In_Humidity"] <= $humidityHidden["level_1"]) {
                $arr["humHdnInd1"] = "colorE";
            }
            if ($display["In_Humidity"] <= $humidityHidden["level_2"]) {
                $arr["humHdnInd2"] = "colorD";
            }
            if ($display["In_Humidity"] <= $humidityHidden["level_3"]) {
                $arr["humHdnInd3"] = "colorC";
            }
            if ($display["In_Humidity"] <= $humidityHidden["level_4"]) {
                $arr["humHdnInd4"] = "colorB";
            }
        } else {
            $arr["humHdnStatus"] = false;
        }

        //      VOC 5 indicators
        if ($display["In_VOC"] >= $TVOC["level_4"]) {
            $arr["voc1"] = "colorE";
        }
        if ($display["In_VOC"] >= $TVOC["level_3"]) {
            $arr["voc2"] = "colorD";
        }
        if ($display["In_VOC"] >= $TVOC["level_2"]) {
            $arr["voc3"] = "colorC";
        }
        if ($display["In_VOC"] >= $TVOC["level_1"]) {
            $arr["voc4"] = "colorB";
        }
        if ($display["In_VOC"] >= $TVOC["level_0"]) {
            $arr["voc5"] = "colorA";
        }

        //      CO2 5 indicators
        if ($display["In_CO2"] >= $CO2["level_4"]) {
            $arr["co2_1"] = "colorE";
        }
        if ($display["In_CO2"] >= $CO2["level_3"]) {
            $arr["co2_2"] = "colorD";
        }
        if ($display["In_CO2"] >= $CO2["level_2"]) {
            $arr["co2_3"] = "colorC";
        }
        if ($display["In_CO2"] >= $CO2["level_1"]) {
            $arr["co2_4"] = "colorB";
        }
        if ($display["In_CO2"] >= $CO2["level_0"]) {
            $arr["co2_5"] = "colorA";
        }

        //      PM2.5 5 indicators
        if ($display["In_PM_2_5"] >= $PM2_5["level_4"]) {
            $arr["pm25_1"] = "colorE";
        }
        if ($display["In_PM_2_5"] >= $PM2_5["level_3"]) {
            $arr["pm25_2"] = "colorD";
        }
        if ($display["In_PM_2_5"] >= $PM2_5["level_2"]) {
            $arr["pm25_3"] = "colorC";
        }
        if ($display["In_PM_2_5"] >= $PM2_5["level_1"]) {
            $arr["pm25_4"] = "colorB";
        }
        if ($display["In_PM_2_5"] >= $PM2_5["level_0"]) {
            $arr["pm25_5"] = "colorA";
        }

        //      PM10 5 indicators
        if ($display["In_PM_10"] >= $PM_10["level_4"]) {
            $arr["pm10_1"] = "colorE";
        }
        if ($display["In_PM_10"] >= $PM_10["level_3"]) {
            $arr["pm10_2"] = "colorD";
        }
        if ($display["In_PM_10"] >= $PM_10["level_2"]) {
            $arr["pm10_3"] = "colorC";
        }
        if ($display["In_PM_10"] >= $PM_10["level_1"]) {
            $arr["pm10_4"] = "colorB";
        }
        if ($display["In_PM_10"] >= $PM_10["level_0"]) {
            $arr["pm10_5"] = "colorA";
        }

        //      Selected Machine and Customer Name
        $arr["customer"] = $display["Customer Name"];
        $arr["machine"] = $machine["name"];
        $arr["date"] = $machine["inspectionDate"];
        // $arr["date"] = date("m-d-Y", strtotime($machine["inspectionDate"]));
    } else {
        echo "this is without any get pramater";
    }
}
print json_encode($arr);
?>


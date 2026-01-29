<?php
include "../headers.php";
include "../auth.php";
$arr = [];
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["api"])) {
        $api = $_GET["api"];
        $query1 = "SELECT * FROM `Display_Final` WHERE `Machine API Token` = '$api'";
        ($result1 = mysqli_query($dbCon, $query1)) or
            die("database error:" . mysqli_error($dbCon));
        $a = mysqli_fetch_assoc($result1);
        $aqi = $a["AQI"];
        $inTemp = $a["In_Temperature"];
        $inHum = $a["In_Humidity"];
        $inCo2 = $a["In_CO2"];
        $inVoc = $a["In_VOC"];
        $inPm25 = $a["In_PM_2_5"];
        $inPm10 = $a["In_PM_10"];
        $inCo = $a["In_CO"];
        $outTemp = $a["Out_Temperature"];
        $outHum = $a["Out_Humidity"];
        $outO3 = $a["Out_O3"];
        $outSo2 = $a["Out_SO2"];
        $outCo = $a["Out_CO"];
        $outCo2 = $a["Out_CO2"];
        $outNo2 = $a["Out_NO2"];
        $outPm25 = $a["Out_PM_2_5"];
        $outPm10 = $a["Out_PM_10"];
        $outRad = $a["Out_Radon_Spare"];

        // Fetching the ranges from ranges table
        $query2 = "SELECT * FROM `ranges` WHERE `machineToken`='$api'";
        ($result2 = mysqli_query($dbCon, $query2)) or
            die("database error:" . mysqli_error($dbCon));
        $range = mysqli_fetch_assoc($result2);

        $humidity["level_0"] = $range["humidity_green"];
        $humidity["level_1"] = $range["humidity_yellow"];
        $humidity["level_2"] = $range["humidity_orange"];
        $humidity["level_3"] = $range["humidity_darkOrange"];
        $humidity["level_4"] = $range["humidity_red"];

        $humidityHidden["level_4"] = $range["humidityHidden_yellow"]; //39
        $humidityHidden["level_3"] = $range["humidityHidden_orange"]; //35
        $humidityHidden["level_2"] = $range["humidityHidden_darkOrange"]; //20
        $humidityHidden["level_1"] = $range["humidityHidden_red"]; //15

        $AQI["level_4"] = $range["AQI_A"];
        $AQI["level_3"] = $range["AQI_B"];
        $AQI["level_2"] = $range["AQI_C"];
        $AQI["level_1"] = $range["AQI_D"];
        $AQI["level_0"] = $range["AQI_F"];

        $TVOC["level_0"] = $range["VOC_green"];
        $TVOC["level_1"] = $range["VOC_yellow"];
        $TVOC["level_2"] = $range["VOC_orange"];
        $TVOC["level_3"] = $range["VOC_darkOrange"];
        $TVOC["level_4"] = $range["VOC_red"];

        $CO2["level_0"] = $range["CO2_green"];
        $CO2["level_1"] = $range["CO2_yellow"];
        $CO2["level_2"] = $range["CO2_orange"];
        $CO2["level_3"] = $range["CO2_darkOrange"];
        $CO2["level_4"] = $range["CO2_red"];

        $PM2_5["level_0"] = $range["PM2_green"];
        $PM2_5["level_1"] = $range["PM2_yellow"];
        $PM2_5["level_2"] = $range["PM2_orange"];
        $PM2_5["level_3"] = $range["PM2_darkOrange"];
        $PM2_5["level_4"] = $range["PM2_red"];

        $PM_10["level_0"] = $range["PM10_green"];
        $PM_10["level_1"] = $range["PM10_yellow"];
        $PM_10["level_2"] = $range["PM10_orange"];
        $PM_10["level_3"] = $range["PM10_darkOrange"];
        $PM_10["level_4"] = $range["PM10_red"];

        $CO["level_0"] = 0;
        $CO["level_1"] = 4;
        $CO["level_2"] = 9;
        $CO["level_3"] = 14;
        $CO["level_4"] = 34;
        $CO["level_5"] = 200;

        
        // AQI circle color logic
        if ($aqi > $AQI["level_4"]) {
            $aqiColor = "19C424"; //green
        } elseif ($aqi > $AQI["level_3"] && $aqi <= $AQI["level_4"]) {
            $aqiColor = "FFE435"; //yellow
        } elseif ($aqi > $AQI["level_2"] && $aqi <= $AQI["level_3"]) {
            $aqiColor = "FFA100"; //orange
        } elseif ($aqi > $AQI["level_1"] && $aqi <= $AQI["level_2"]) {
            $aqiColor = "EF8625"; //dark orange
        } elseif ($aqi <= $AQI["level_1"]) {
            $aqiColor = "FF0000"; //red
        }
        // Finding circle fill percentage for AQI
        $aqiPercent = ceil(($aqi / 100) * 100);
        if ($aqiPercent > 100) {
            $aqiPercent = 100;
        }

        $arr['AQI']['value'] = $aqiPercent;
        $arr['AQI']['color'] = $aqiColor;




        // Indoor Temperature circle color logic
        if ($inTemp <= 68) {
            $inTempColor = "0548F9"; //blue
        } elseif ($inTemp < 87 && $inTemp > 68) {
            $inTempColor = "19C424"; //green
        } elseif ($inTemp >= 87 && $inTemp < 90) {
            $inTempColor = "FFE435"; //yellow
        } elseif ($inTemp >= 90 && $inTemp < 95) {
            $inTempColor = "FFA100"; //orange
        } elseif ($inTemp >= 95 && $inTemp < 100) {
            $inTempColor = "EF8625"; //dark orange
        } elseif ($inTemp >= 100) {
            $inTempColor = "FF0000"; //red
        }
        // Finding circle fill percentage for Indoor Temperature
        $inTempPercent = ceil(($inTemp / 110) * 100);
        if ($inTempPercent > 100) {
            $inTempPercent = 100;
        }

        $arr['In_Temperature']['value'] = $inTempPercent;
        $arr['In_Temperature']['color'] = $inTempColor;


        // Indoor Humidity circle color logic
        if ($inHum >= $humidity["level_0"] && $inHum < $humidity["level_1"]) {
            $inHumColor = "19C424"; //green
        } elseif (
            ($inHum >= $humidity["level_1"] && $inHum < $humidity["level_2"]) ||
            ($inHum <= $humidityHidden["level_4"] &&
                $inHum > $humidityHidden["level_3"])
        ) {
            $inHumColor = "FFE435"; //yellow
        } elseif (
            ($inHum >= $humidity["level_2"] && $inHum < $humidity["level_3"]) ||
            ($inHum <= $humidityHidden["level_3"] &&
                $inHum > $humidityHidden["level_2"])
        ) {
            $inHumColor = "FFA100"; //orange
        } elseif (
            ($inHum >= $humidity["level_3"] && $inHum < $humidity["level_4"]) ||
            ($inHum <= $humidityHidden["level_2"] &&
                $inHum > $humidityHidden["level_1"])
        ) {
            $inHumColor = "EF8625"; //dark orange
        } elseif (
            $inHum >= $humidity["level_4"] ||
            $inHum <= $humidityHidden["level_1"]
        ) {
            $inHumColor = "FF0000"; //red
        }
        // Finding circle fill percentage for Indoor Humidity
        $inHumPercent = ceil(($inHum / 100) * 100);
        if ($inHumPercent > 100) {
            $inHumPercent = 100;
        }

        $arr['In_Humidity']['value'] = $inHumPercent;
        $arr['In_Humidity']['color'] = $inHumColor;


        // Indoor VOC circle color logic
        if ($inVoc >= $TVOC["level_0"] && $inVoc < $TVOC["level_1"]) {
            $inVocColor = "19C424"; //green
        } elseif ($inVoc >= $TVOC["level_1"] && $inVoc < $TVOC["level_2"]) {
            $inVocColor = "FFE435"; //yellow
        } elseif ($inVoc >= $TVOC["level_2"] && $inVoc < $TVOC["level_3"]) {
            $inVocColor = "FFA100"; //orange
        } elseif ($inVoc >= $TVOC["level_3"] && $inVoc < $TVOC["level_4"]) {
            $inVocColor = "EF8625"; //dark orange
        } elseif ($inVoc >= $TVOC["level_4"]) {
            $inVocColor = "FF0000"; //red
        }
        // Finding circle fill percentage for Indoor VOC
        $inVocPercent = ceil(($inVoc / 60000) * 100);
        if ($inVocPercent > 100) {
            $inVocPercent = 100;
        }

        $arr['In_VOC']['value'] = $inVocPercent;
        $arr['In_VOC']['color'] = $inVocColor;
        

        // Indoor CO2 circle color logic
        if ($inCo2 >= $CO2["level_0"] && $inCo2 < $CO2["level_1"]) {
            $inCo2Color = "19C424"; //green
        } elseif ($inCo2 >= $CO2["level_1"] && $inCo2 < $CO2["level_2"]) {
            $inCo2Color = "FFE435"; //yellow
        } elseif ($inCo2 >= $CO2["level_2"] && $inCo2 < $CO2["level_3"]) {
            $inCo2Color = "FFA100"; //orange
        } elseif ($inCo2 >= $CO2["level_3"] && $inCo2 < $CO2["level_4"]) {
            $inCo2Color = "EF8625"; //dark orange
        } elseif ($inCo2 >= $CO2["level_4"]) {
            $inCo2Color = "FF0000"; //red
        }
        // Finding circle fill percentage for Indoor CO2
        $inCo2Percent = ceil(($inCo2 / 4000) * 100);
        if ($inCo2Percent > 100) {
            $inCo2Percent = 100;
        }

        $arr['In_CO2']['value'] = $inCo2Percent;
        $arr['In_CO2']['color'] = $inCo2Color;
        

        // Indoor CO circle color logic
        if ($inCo >= $CO["level_0"] && $inCo < $CO["level_1"]) {
            $inCoColor = "19C424"; //green
        } elseif ($inCo >= $CO["level_1"] && $inCo < $CO["level_2"]) {
            $inCoColor = "FFE435"; //yellow
        } elseif ($inCo >= $CO["level_2"] && $inCo < $CO["level_3"]) {
            $inCoColor = "FFA100"; //orange
        } elseif ($inCo >= $CO["level_3"] && $inCo < $CO["level_4"]) {
            $inCoColor = "EF8625"; //dark orange
        } elseif ($inCo >= $CO["level_4"]) {
            $inCoColor = "FF0000"; //red
        }
        // Finding circle fill percentage for Indoor CO
        $inCoPercent = ceil(($inCo / 200) * 100);
        if ($inCoPercent > 100) {
            $inCoPercent = 100;
        }

        $arr['In_CO']['value'] = $inCoPercent;
        $arr['In_CO']['color'] = $inCoColor;

        // Indoor PM 2.5 circle color logic
        if ($inPm25 >= $PM2_5["level_0"] && $inPm25 < $PM2_5["level_1"]) {
            $inPm25Color = "19C424"; //green
        } elseif ($inPm25 >= $PM2_5["level_1"] && $inPm25 < $PM2_5["level_2"]) {
            $inPm25Color = "FFE435"; //yellow
        } elseif ($inPm25 >= $PM2_5["level_2"] && $inPm25 < $PM2_5["level_3"]) {
            $inPm25Color = "FFA100"; //orange
        } elseif ($inPm25 >= $PM2_5["level_3"] && $inPm25 < $PM2_5["level_4"]) {
            $inPm25Color = "EF8625"; //dark orange
        } elseif ($inPm25 >= $PM2_5["level_4"]) {
            $inPm25Color = "FF0000"; //red
        }
        // Finding circle fill percentage for Indoor PM 2.5
        $inPm25Percent = ceil(($inPm25 / 300) * 100);
        if ($inPm25Percent > 100) {
            $inPm25Percent = 100;
        }


        $arr['In_PM_2.5']['value'] = $inPm25Percent;
        $arr['In_PM_2.5']['color'] = $inPm25Color;

        // Indoor PM 10 circle color logic
        if ($inPm10 >= $PM2_5["level_0"] && $inPm10 < $PM2_5["level_1"]) {
            $inPm10Color = "19C424"; //green
        } elseif ($inPm10 >= $PM2_5["level_1"] && $inPm10 < $PM2_5["level_2"]) {
            $inPm10Color = "FFE435"; //yellow
        } elseif ($inPm10 >= $PM2_5["level_2"] && $inPm10 < $PM2_5["level_3"]) {
            $inPm10Color = "FFA100"; //orange
        } elseif ($inPm10 >= $PM2_5["level_3"] && $inPm10 < $PM2_5["level_4"]) {
            $inPm10Color = "EF8625"; //dark orange
        } elseif ($inPm10 >= $PM2_5["level_4"]) {
            $inPm10Color = "FF0000"; //red
        }
        // Finding circle fill percentage for Indoor PM 10
        $inPm10Percent = ceil(($inPm10 / 500) * 100);
        if ($inPm10Percent > 100) {
            $inPm10Percent = 100;
        }

        $arr['In_PM_10']['value'] = $inPm10Percent;
        $arr['In_PM_10']['color'] = $inPm10Color;

        // Outdoor Temperature circle color logic
        if ($outTemp <= 68) {
            $outTempColor = "0548F9"; //blue
        } elseif ($outTemp < 87 && $outTemp > 68) {
            $outTempColor = "19C424"; //green
        } elseif ($outTemp >= 87 && $outTemp < 90) {
            $outTempColor = "FFE435"; //yellow
        } elseif ($outTemp >= 90 && $outTemp < 95) {
            $outTempColor = "FFA100"; //orange
        } elseif ($outTemp >= 95 && $outTemp < 100) {
            $inTempColor = "EF8625"; //dark orange
        } elseif ($outTemp >= 100) {
            $outTempColor = "FF0000"; //red
        }
        // Finding circle fill percentage for Outdoor Temperature
        $outTempPercent = ceil(($outTemp / 110) * 100);
        if ($outTempPercent > 100) {
            $outTempPercent = 100;
        }

        $arr['Out_Temperature']['value'] = $outTempPercent;
        $arr['Out_Temperature']['color'] = $outTempColor;

        // Outdoor Humidity circle color logic
        if ($outHum >= 0 && $outHum < 20) {
            $outHumColor = "19C424"; //green
        } elseif ($outHum >= 20 && $outHum < 40) {
            $outHumColor = "FFE435"; //yellow
        } elseif ($outHum >= 40 && $outHum < 60) {
            $outHumColor = "FFA100"; //orange
        } elseif ($outHum >= 60 && $outHum < 80) {
            $outHumColor = "EF8625"; //dark orange
        } elseif ($outHum >= 80) {
            $outHumColor = "FF0000"; //red
        }
        // Finding circle fill percentage for Outdoor Humidity
        $outHumPercent = ceil(($outHum / 100) * 100);
        if ($outHumPercent > 100) {
            $outHumPercent = 100;
        }


        $arr['Out_Humidity']['value'] = $outHumPercent;
        $arr['Out_Humidity']['color'] = $outHumColor;


        // Outdoor O3 circle color logic
        if ($outO3 >= 0 && $outO3 < 20) {
            $outO3Color = "19C424"; //green
        } elseif ($outO3 >= 20 && $outO3 < 40) {
            $outO3Color = "FFE435"; //yellow
        } elseif ($outO3 >= 40 && $outO3 < 60) {
            $outO3Color = "FFA100"; //orange
        } elseif ($outO3 >= 60 && $outO3 < 80) {
            $outO3Color = "EF8625"; //dark orange
        } elseif ($outO3 >= 80) {
            $outO3Color = "FF0000"; //red
        }
        // Finding circle fill percentage for Outdoor O3
        $outO3Percent = ceil(($outO3 / 100) * 100);
        if ($outO3Percent > 100) {
            $outO3Percent = 100;
        }

        $arr['Out_O3']['value'] = $outO3Percent;
        $arr['Out_O3']['color'] = $outO3Color;

        // Outdoor So2 circle color logic
        if ($outSo2 >= 0 && $outSo2 < 20) {
            $outSo2Color = "19C424"; //green
        } elseif ($outSo2 >= 20 && $outSo2 < 40) {
            $outSo2Color = "FFE435"; //yellow
        } elseif ($outSo2 >= 40 && $outSo2 < 60) {
            $outSo2Color = "FFA100"; //orange
        } elseif ($outSo2 >= 60 && $outSo2 < 80) {
            $outSo2Color = "EF8625"; //dark orange
        } elseif ($outSo2 >= 80) {
            $outSo2Color = "FF0000"; //red
        }
        // Finding circle fill percentage for Outdoor SO2
        $outSo2Percent = ceil(($outSo2 / 100) * 100);
        if ($outSo2Percent > 100) {
            $outSo2Percent = 100;
        }

        $arr['Out_SO2']['value'] = $outSo2Percent;
        $arr['Out_SO2']['color'] = $outSo2Color;

        // Outdoor NO2 circle color logic
        if ($outNo2 >= 0 && $outNo2 < 20) {
            $outNo2Color = "19C424"; //green
        } elseif ($outNo2 >= 20 && $outNo2 < 40) {
            $outNo2Color = "FFE435"; //yellow
        } elseif ($outNo2 >= 40 && $outNo2 < 60) {
            $outNo2Color = "FFA100"; //orange
        } elseif ($outNo2 >= 60 && $outNo2 < 80) {
            $outNo2Color = "EF8625"; //dark orange
        } elseif ($outNo2 >= 80) {
            $outNo2Color = "FF0000"; //red
        }
        // Finding circle fill percentage for Outdoor NO2
        $outNo2Percent = ceil(($outNo2 / 100) * 100);
        if ($outNo2Percent > 100) {
            $outNo2Percent = 100;
        }

        $arr['Out_NO2']['value'] = $outNo2Percent;
        $arr['Out_NO2']['color'] = $outNo2Color;

        // Outdoor CO2 circle color logic
        if ($outCo2 >= 0 && $outCo2 < 20) {
            $outCo2Color = "19C424"; //green
        } elseif ($outCo2 >= 20 && $outCo2 < 40) {
            $outCo2Color = "FFE435"; //yellow
        } elseif ($outCo2 >= 40 && $outCo2 < 60) {
            $outCo2Color = "FFA100"; //orange
        } elseif ($outCo2 >= 60 && $outCo2 < 80) {
            $outCo2Color = "EF8625"; //dark orange
        } elseif ($outCo2 >= 80) {
            $outCo2Color = "FF0000"; //red
        }
        // Finding circle fill percentage for Outdoor CO2
        $outCo2Percent = ceil(($outCo2 / 100) * 100);
        if ($outCo2Percent > 100) {
            $outCo2Percent = 100;
        }

        $arr['Out_CO2']['value'] = $outCo2Percent;
        $arr['Out_CO2']['color'] = $outCo2Color;

        // Outdoor CO circle color logic
        if ($outCo >= 0 && $outCo < 20) {
            $outCoColor = "19C424"; //green
        } elseif ($outCo >= 20 && $outCo < 40) {
            $outCoColor = "FFE435"; //yellow
        } elseif ($outCo >= 40 && $outCo < 60) {
            $outCoColor = "FFA100"; //orange
        } elseif ($outCo >= 60 && $outCo < 80) {
            $outCoColor = "EF8625"; //dark orange
        } elseif ($outCo >= 80) {
            $outCoColor = "FF0000"; //red
        }
        // Finding circle fill percentage for Outdoor CO
        $outCoPercent = ceil(($outCo / 100) * 100);
        if ($outCoPercent > 100) {
            $outCoPercent = 100;
        }

        $arr['Out_CO']['value'] = $outCoPercent;
        $arr['Out_CO']['color'] = $outCoColor;

        // Outdoor PM 2.5 circle Pm25lor logic
        if ($outPm25 >= 0 && $outPm25 < 20) {
            $outPm25Pm25lor = "19C424"; //green
        } elseif ($outPm25 >= 20 && $outPm25 < 40) {
            $outPm25Pm25lor = "FFE435"; //yellow
        } elseif ($outPm25 >= 40 && $outPm25 < 60) {
            $outPm25Pm25lor = "FFA100"; //orange
        } elseif ($outPm25 >= 60 && $outPm25 < 80) {
            $outPm25Pm25lor = "EF8625"; //dark orange
        } elseif ($outPm25 >= 80) {
            $outPm25Pm25lor = "FF0000"; //red
        }
        // Finding circle fill percentage for Outdoor PM 2.5
        $outPm25Percent = ceil(($outPm25 / 100) * 100);
        if ($outPm25Percent > 100) {
            $outPm25Percent = 100;
        }

        $arr['Out_PM_2_5']['value'] = $outPm25Percent;
        $arr['Out_PM_2_5']['color'] = $outPm25Pm25lor;

        // Outdoor PM 10 circle Pm10lor logic
        if ($outPm10 >= 0 && $outPm10 < 20) {
            $outPm10Color = "19C424"; //green
        } elseif ($outPm10 >= 20 && $outPm10 < 40) {
            $outPm10Color = "FFE435"; //yellow
        } elseif ($outPm10 >= 40 && $outPm10 < 60) {
            $outPm10Color = "FFA100"; //orange
        } elseif ($outPm10 >= 60 && $outPm10 < 80) {
            $outPm10Color = "EF8625"; //dark orange
        } elseif ($outPm10 >= 80) {
            $outPm10Color = "FF0000"; //red
        }
        // Finding circle fill percentage for Outdoor PM 10
        $outPm10Percent = ceil(($outPm10 / 100) * 100);
        if ($outPm10Percent > 100) {
            $outPm10Percent = 100;
        }

        $arr['Out_PM_10']['value'] = $outPm10Percent;
        $arr['Out_PM_10']['color'] = $outPm10Color;
    }
}
print json_encode($arr);
?>
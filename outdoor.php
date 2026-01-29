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

        $Outtemp["val"] = $control["Out_Temperature"];
        $Outhum["val"] = $control["Out_Humidity"];

        $O3["max"] = $range['Out_O3_max'];
        $O3["min"] = $range['Out_O3_min'];
        $OutO3["val"] = $control["Out_O3"];
        $OutO3["perc"] = (100 * ($OutO3["val"] - $O3["min"])) / $O3["max"];
        if($OutO3["perc"] > 100){
            $OutO3["perc"] = 100;
        } else if($OutO3["perc"] < 0){
            $OutO3["perc"] = 0;
        }

        $SO2["max"] = $range['Out_SO2_max'];
        $SO2["min"] = $range['Out_SO2_min'];
        $OutSO2["val"] = $control["Out_SO2"];
        $OutSO2["perc"] = (100 * ($OutSO2["val"] - $SO2["min"])) / $SO2["max"];
        if($OutSO2["perc"] > 100){
            $OutSO2["perc"] = 100;
        } else if($OutSO2["perc"] < 0){
            $OutSO2["perc"] = 0;
        }

        $CO2["max"] = $range['Out_CO2_max'];
        $CO2["min"] = $range['Out_CO2_min'];
        $OutCO2["val"] = $control["Out_CO2"];
        $OutCO2["perc"] = (100 * ($OutCO2["val"] - $CO2["min"])) / $CO2["max"];
        if($OutCO2["perc"] > 100){
            $OutCO2["perc"] = 100;
        } else if($OutCO2["perc"] < 0){
            $OutCO2["perc"] = 0;
        }

        $CO["max"] = $range['Out_CO_max'];
        $CO["min"] =$range['Out_CO_min'];
        $OutCO["val"] = $control["Out_CO"];
        $OutCO["perc"] = (100 * ($OutCO["val"] - $CO["min"])) / $CO["max"];
        if($OutCO["perc"] > 100){
            $OutCO["perc"] = 100;
        } else if($OutCO["perc"] < 0){
            $OutCO["perc"] = 0;
        }

        $PM25["max"] = $range['Out_PM2_max'];
        $PM25["min"] = $range['Out_PM2_min'];
        $OutPM25["val"] = $control["Out_PM_2_5"];
        $OutPM25["perc"] = (100 * ($OutPM25["val"] - $PM25["min"])) / $PM25["max"];
        if($OutPM25["perc"] > 100){
            $OutPM25["perc"] = 100;
        } else if($OutPM25["perc"] < 0){
            $OutPM25["perc"] = 0;
        }

        $PM10["max"] = $range['Out_PM10_max'];
        $PM10["min"] = $range['Out_PM10_min'];
        $OutPM10["val"] = $control["Out_PM_10"];
        $OutPM10["perc"] = (100 * ($OutPM10["val"] - $PM10["min"])) / $PM10["max"];
        if($OutPM10["perc"] > 100){
            $OutPM10["perc"] = 100;
        } else if($OutPM10["perc"] < 0){
            $OutPM10["perc"] = 0;
        }

        $NO2["max"] = $range['Out_NO2_max'];
        $NO2["min"] = $range['Out_NO2_min'];
        $OutNO2["val"] = $control["Out_NO2"];
        $OutNO2["perc"] = (100 * ($OutNO2["val"] - $NO2["min"])) / $NO2["max"];
        if($OutNO2["perc"] > 100){
            $OutNO2["perc"] = 100;
        } else if($OutNO2["perc"] < 0){
            $OutNO2["perc"] = 0;
        }

        // $Radon["max"] = $range['_max'];
        // $Radon["min"] = 0;
        // $OutRadon["val"] = $control["Out_Radon_Spare"];
        // $OutRadon["perc"] =
        //     (100 * ($OutRadon["val"] - $Radon["min"])) / $Radon["max"];
        

            // Top Temp and Hum Values for outdoor
            $arr['temp'] = $Outtemp['val'];
            $arr['hum'] = $Outhum['val'];

            // O3 bar height and value
            $arr['o3Ht']=$OutO3['perc'];
            $arr['o3Vl']=$OutO3['val'];

            // SO2 bar height and value
            $arr['so2Ht']=$OutSO2['perc'];
            $arr['so2Vl']=$OutSO2['val'];

            // NO2 bar height and value
            $arr['no2Ht']=$OutNO2['perc'];
            $arr['no2Vl']=$OutNO2['val'];

            // CO2 bar height and value
            $arr['co2Ht']=$OutCO2['perc'];
            $arr['co2Vl']=$OutCO2['val'];

            // CO bar height and value
            $arr['coHt']=$OutCO['perc'];
            $arr['coVl']=$OutCO['val'];

            // PM25 bar height and value
            $arr['pm25Ht']=$OutPM25['perc'];
            $arr['pm25Vl']=$OutPM25['val'];

            // PM10 bar height and value
            $arr['pm10Ht']=$OutPM10['perc'];
            $arr['pm10Vl']=$OutPM10['val'];

    }
}

print json_encode($arr);
?>

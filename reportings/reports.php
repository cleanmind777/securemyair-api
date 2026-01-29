<?php
include('../headers.php');
include('../auth.php');
$arr = array([]);
$count = 0;
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET['api'])){
        $api = $_GET['api'];
        if (isset($_GET["fromDate"]) && isset($_GET["toDate"])) {
        $fromDate = date('m/d/Y',strtotime($_GET["fromDate"]));
        $toDate = date('m/d/Y',strtotime($_GET["toDate"]));
    }else {
        $fromDate = date("m/d/Y", strtotime("first day of this month"));
        $toDate = date("m/d/Y");
    }
    $query1 = "SELECT * FROM `display_Log` WHERE `Machine API Token` = '$api'";
    ($result1 = mysqli_query($dbCon, $query1)) or
        die("database error:" . mysqli_error($dbCon));
        while($a = mysqli_fetch_assoc($result1)){
            $datetime=new DateTime($a['timeStamp']);
            $dateinput = $datetime->format('m/d/Y');
            $date = $datetime->format('m/d/Y');
            $time = $datetime->format('H:i'); 
             if(($dateinput>=$fromDate)&&($dateinput<=$toDate)){
                 $arr[$count] = $a;
                 $arr[$count]['date'] = $date;
                 $arr[$count]['time'] = $time;
                 $count++;
                }
        }
        
    }
}
print json_encode($arr);
?>

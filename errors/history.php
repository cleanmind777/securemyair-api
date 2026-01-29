 <?php
include('../headers.php');
include('../auth.php');
$arr = [];
$count = 0;
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $cid = $_GET['cid'];
    if(isset($_GET['cid']) && isset($_GET['clear'])){
        $query = "DELETE FROM `Error_History` WHERE `Error_History`.`customer_id` = '$cid'";
        $result = mysqli_query($dbCon, $query) or die ("database error:".mysqli_error($dbCon));
        $arr['res'] = $result;
    } else if(isset($_GET['cid'])){
        $cid = $_GET['cid'];
        $query = "SELECT * FROM `Error_History` WHERE `customer_id` = '$cid'";
        $result = mysqli_query($dbCon, $query) or die ("database error:".mysqli_error($dbCon));
        if(mysqli_num_rows($result) > 0){
            while($h = mysqli_fetch_assoc($result)){
                $arr[$count]['name'] = $h['er_name'];
                $arr[$count]['time'] = $h['time'];
                $count++;
            }
        } else{
            $arr[$count]['res'] = 'clear';
        }
        $query2 = "SELECT * FROM `customers` WHERE `Id` = '$cid'";
$result2 = mysqli_query($dbCon, $query2) or die ("database error:".mysqli_error($dbCon));
$details = mysqli_fetch_assoc($result2);
$arr[$count]['email'] = $details['email'];
$arr[$count]['phone'] = $details['phone'];
    }
}
print(json_encode($arr));
?>


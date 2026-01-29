<?php
include('headers.php');
include('auth.php');
$arr = [[]];

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["cid"])) {
        if (isset($_GET["file"])) {
            $cid = $_GET["cid"];
            $file = $_GET["file"];
            $query = "SELECT * FROM `companyFileDetails` WHERE `customer_id` = '$cid' AND `fileName` = '$file' ORDER BY `id` DESC";
            ($result = mysqli_query($dbCon, $query)) or
                die("database error:" . mysqli_error($dbCon));
            if (mysqli_num_rows($result) > 0) {
                $count = 0;
                while ($data2 = mysqli_fetch_assoc($result)) {
                    $arr[$count]["detail"] = $data2["fileDetails"];
                    $arr[$count]["time"] = $data2["uploadTime"];
                    $count = $count + 1;
                }
            } else {
                $arr[0]["detail"] = "File not uploaded";
                $arr[1]["detail"] = "File not uploaded";
            }
        } else {
            $arr["res"] = "false";
        }
    } else {
        $arr["res"] = "false";
    }
}
print json_encode($arr);
?>

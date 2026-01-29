<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: access');
    header('Access-Control-Allow-Headers: Authorization');
    header('Access-Control-Allow-Methods: POST, GET');
    include('mydbCon.php');
    include('auth.php');
    $arr = array([]);
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $count =0;
        $query = "SELECT * FROM  customers";
        $result = mysqli_query($dbCon, $query) or die("database error:" . mysqli_error($dbCon));
        while ($thisCustomer = mysqli_fetch_assoc($result)) { 
            $arr[$count]['id']= $thisCustomer['Id'];
            $arr[$count]['name']= $thisCustomer['FullName']; 
            $arr[$count]['email']= $thisCustomer['email']; 
            $arr[$count]['phone']= $thisCustomer['phone']; 
            $arr[$count]['cName']= $thisCustomer['company_name']; 
            $arr[$count]['cId']= $thisCustomer['company_id']; 
            $arr[$count]['cEmail']= $thisCustomer['company_email']; 
            $count++;
        } 
    }
    print(json_encode($arr));
?>


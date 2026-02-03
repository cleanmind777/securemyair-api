<?php
include "../headers.php";
use PHPMailer\PHPMailer\PHPMailer;

require_once "../_components/phpmailer/Exception.php";
require_once "../_components/phpmailer/PHPMailer.php";
require_once "../_components/phpmailer/SMTP.php";

$mail = new PHPMailer(true);
include ('../jwt.php');
$arr = [];
$SECRET_KEY = getenv('JWT_SECRET') ?: 'e%^)urD$RS7QxcsP]p4zm42A7!i[x35YJ](gJKz9qRaMk#B&hH';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $emailInput = $_POST['email'];
        $query = "SELECT * FROM customers WHERE `email`='$emailInput'";
        $result = mysqli_query($dbCon, $query) or die("database error:" . mysqli_error($dbCon));
        $userCheck = mysqli_num_rows($result);
        if ($userCheck) {
            $passwordInput = $_POST['password'];
            $passcheck = mysqli_fetch_assoc($result);
            if ($passwordInput == $passcheck['password']) {
                $email = $passcheck['email'];
                // ALWAYS bypass 2FA for client; return token directly
                $payload = [
                    'iat' => time(),
                    'iss' => 'client-securemyair',
                    'exp' => time() + (10*60*60), // 10 hrs
                    'userID' => $passcheck['Id']
                ];
                $token = JWT::encode($payload, $SECRET_KEY);
                $arr["res"] = "true";
                $arr['name'] = isset($passcheck['name']) ? $passcheck['name'] : (isset($passcheck['FullName']) ? $passcheck['FullName'] : $email);
                $arr['id'] = $passcheck['Id'];
                $arr['token'] = $token;
            } else { 
                $arr['res'] = 'Password Incorrent';
            }
        } else { 
            $arr['res'] = 'Email Does not Exist';
        }
    } 
     else if (isset($_POST['code']))
    {
        $code = $_POST["code"];
        $email = $_POST["codeEmail"];
        $query3 = "SELECT * FROM `customers` WHERE `email`='$email'";
        ($result3 = mysqli_query($dbCon, $query3)) or die("database error:" . mysqli_error($dbCon));
        $data = mysqli_fetch_assoc($result3);
        if ($data["fa_code"] == $code)
        {
            $query4 = "UPDATE `customers` SET `fa_code` = NULL WHERE `email`='$email'";
            ($result4 = mysqli_query($dbCon, $query4)) or
            die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $result4;
             $payload = [
                    'iat' => time(),
                    'iss' => 'localhost:3000',
                    'exp' => time() + (360*24*60*60), // 10 hrs
                    'userID' => $data['Id']
                ];
                $token = JWT::encode($payload,$SECRET_KEY);
                $arr['email'] = $data['email'];
                $arr['id'] = $data['Id'];
                $arr['token'] = $token;
        }
        else
        {
            $arr["res"] = false;
        }
    }
}
print(json_encode($arr));
?>


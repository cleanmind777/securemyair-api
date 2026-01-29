<?php
include "../headers.php";
use PHPMailer\PHPMailer\PHPMailer; 

require_once "../_components/phpmailer/Exception.php"; 
require_once "../_components/phpmailer/PHPMailer.php";
require_once "../_components/phpmailer/SMTP.php";

$mail = new PHPMailer(true);
$arr = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["email"])) {
        $email = $_POST["email"];
        $query = "SELECT * FROM `inspectors` WHERE `email`='$email'";
        ($result = mysqli_query($dbCon, $query)) or
            die("database error:" . mysqli_error($dbCon));
        $userCheck = mysqli_num_rows($result);
        if ($userCheck > 0) {
            $code = rand(999999, 111111);
            $query2 = "UPDATE `inspectors` SET `rst_pass_code` = '$code' WHERE `email`= '$email'";
            ($result2 = mysqli_query($dbCon, $query2)) or
                die("database error:" . mysqli_error($dbCon));
            if ($result2) {
                try {
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = "info.iamredapple@gmail.com"; //Gmail adress which you want to use as SMTP server
                    $mail->Password = "tumzauxxcmihiazd"; //Password of above gmail adress
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = "587";

                    $mail->setFrom("info.iamredapple@gmail.com"); //Gmail adress which you want to use as SMTP server
                    $mail->addAddress($email); //Adress at which you want to recieve the data filled in the form

                    $mail->isHTML(true);
                    $mail->Subject = "Code for Reseting Password";
                    // $mail->Body = "Enter this code to reset your password Password: <br> <h1 style='text-align:center'> $code </h1>";
                    $mail->Body = "<pre style='font-size:15px'>
                    
Need to reset your password?

Use this virification code!

<h1>$code</h1>

If you did not forget your password, you can ignore this email.

                    </pre>";

                    $mail->send();
                    $arr["res"] = "true";
                } catch (Exception $e) {
                    $arr["res"] = $e;
                }
            }
        } else {
            $arr["res"] = "Email dos not exist";
        }
    } elseif (isset($_POST["code"])) {
        $code = $_POST["code"];
        $email = $_POST["codeEmail"];
        $query3 = "SELECT * FROM `inspectors` WHERE `email`='$email'";
        ($result3 = mysqli_query($dbCon, $query3)) or
            die("database error:" . mysqli_error($dbCon));
        $data = mysqli_fetch_assoc($result3);
        if ($data["rst_pass_code"] == $code) {
            $arr["res"] = true;
        } else {
            $arr["res"] = false;
        }
    } elseif (isset($_POST["password"])) {
        $pass = $_POST["password"];
        $email = $_POST["passEmail"];
        $query4 = "UPDATE `inspectors` SET `rst_pass_code` = NULL,`password` = '$pass' WHERE `email`= '$email'";
        ($result4 = mysqli_query($dbCon, $query4)) or
            die("database error:" . mysqli_error($dbCon));
        if ($result4) {
            $arr["res"] = $result4;
            $query5 = "SELECT * FROM `inspectors` WHERE `email`='$email'";
            ($result5 = mysqli_query($dbCon, $query5)) or
                die("database error:" . mysqli_error($dbCon));
            $user = mysqli_fetch_assoc($result5);
            $arr["id"] = $user["id"];
            $arr["username"] = $user["user_name"];
        }
    }
}
print json_encode($arr);
?>

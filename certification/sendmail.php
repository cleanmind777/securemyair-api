<?php
include('../headers.php');
use PHPMailer\PHPMailer\PHPMailer;

require_once '../_components/phpmailer/Exception.php';
require_once '../_components/phpmailer/PHPMailer.php';
require_once '../_components/phpmailer/SMTP.php';

$mail = new PHPMailer(true);

$arr = [];
if(isset($_POST['submit'])){
    $apName = $_POST['name'];
    $cName = $_POST['company'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $buildingType = $_POST['buildings'];
    $businessType = $_POST['business'];
    $owner = $_POST['owner'];
    $contractor = $_POST['contractor'];
    $manager = $_POST['manager'];

    try{
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'info.iamredapple@gmail.com'; //Gmail adress which you want to use as SMTP server
        $mail->Password = 'tumzauxxcmihiazd'; //Password of above gmail adress
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = '587';

        $mail->setFrom('info.iamredapple@gmail.com'); //Gmail adress which you want to use as SMTP server
        $mail->addAddress('info.iamredapple@gmail.com'); //Adress at which you want to recieve the data filled in the form
        // $mail->addAddress('grace.emp02@gmail.com');
        $mail->isHTML(true);
        $mail->Subject = 'Certification Application';
        $mail->Body = "
        <table style='border: 1px solid black; border-radius: 20px; padding:10px'>
        <tr>
            <td><strong>Field</strong></td>
            <td><strong>Value</strong></td>
        </tr>
        <tr>
            <td>Applicant Name: </td>
            <td>$apName</td>
        </tr>
        <tr>
            <td>Company Name: </td>
            <td>$cName</td>
        </tr>
        <tr>
            <td>Email: </td>
            <td>$email</td>
        </tr>
        <tr>
            <td>Contact No: </td>
            <td>$contact</td>
        </tr>
        <tr>
            <td>Address: </td>
            <td>$address</td>
        </tr>
        <tr>
            <td>Type of Buildings: </td>
            <td>$buildingType</td>
        </tr>
        <tr>
            <td>Type of Business: </td>
            <td>$businessType</td>
        </tr>
        <tr>
            <td>Property Owner: </td>
            <td>$owner</td>
        </tr>
        <tr>
            <td>General Contractor: </td>
            <td>$contractor</td>
        </tr>
        <tr>
            <td>Company Project Manager: </td>
            <td>$manager</td>
        </tr>
    </table>
        ";
        $mail->send();
        $arr['res'] = 'Your application is sent successfully.';
    } catch (Exception $e){
      $arr['res'] = false;
      $arr['msg'] = "Sorry! ".$e->getMessage();
    }
}
print (json_encode($arr));
?>

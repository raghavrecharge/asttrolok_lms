<?php include('templates/header.php');?>
<section class="showcase">
   <div class="container">
    <div class="text-center">
      <h1 class="display-3">Thank You!</h1>
      <p class="lead text-danger">Your transaction has been declined.</p>
      <hr>
      <p>
        Having trouble? <a href="mailto:astrolok.vedic@gmail.com">Contact us</a>
      </p>
      <p class="lead">
        <a class="btn btn-primary btn-sm" href="https://www.asttrolok.com/" role="button">Continue to homepage</a>
      </p>
    </div>
    </div>
</section>
<br><br><br><br><br><br>
<?php include('templates/footer.php');?>
<?php
session_start();
if (!isset($_SESSION['$razorpay_payment_id'])){
     echo "<script>window.location.href = 'https://www.asttrolok.com/razorpay/';</script>";
}
ini_set('session.gc_maxlifetime', 315360000);

// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(315360000);
$time = $_SERVER['REQUEST_TIME'];

/**
* for a 30 minute timeout, specified in seconds
*/
$timeout_duration = 315360000;
error_reporting(0);
$con = mysqli_connect("localhost","rechargestudio","rechargestudio","astrolok");

if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  date_default_timezone_set('Asia/Kolkata');
 $email= $_SESSION['email'];
$contact=$_SESSION['contact'];
$card_holder_name_id = $_SESSION['card_holder_name_id'];
$merchant_amount = $_SESSION['merchant_amount'];
$merchant_order_id =  $_SESSION['merchant_order_id'];
$razorpay_payment_id = $_SESSION['razorpay_payment_id'];
 $date=date("d/m/Y");
  $time = date('h:i A');
  $created=$date.' '.$time;
  $success='failed';
  $sql = "INSERT INTO payment(`paymentid`,`orderid`,`amount`,`email`,`contact`,`created`,`status`,`name`)
  VALUES ('$razorpay_payment_id','$merchant_order_id','$merchant_amount','$email','$contact','$created','$success','$card_holder_name_id')";
$con->query($sql);
//$last_id = $con->insert_id;
$receiver ='astrolok.vedic@gmail.com';
      $to = $receiver;
      $subject = "Invoice Details of your Order";

       $htmlContent = '<div class="gmail_quote"><div style="width:100%!important;height:100%;margin:0"><div style="padding:20px;height:100%;background:#e5e5e5"><div style="padding:30px;display:block!important;max-width:600px!important;margin:0 auto!important;clear:both!important;background:#fff;border-radius:10px;border:1px solid #aaa"> <p style="text-align:right">
          <img style="max-width:300px;max-height:150px" src="https://ci3.googleusercontent.com/proxy/BYUZm8eO4PkRYeSQx8lFkevEUq-Dnl0uK7chgHKBrJP9lhEXuzP36OIMHUJeTiCH3SvSTRWCyWk=s0-d-e1-ft#https://asttrolok.spayee.com/logo.png" alt="">
        </p><h3>Thank you for using <span style="color:#1a4a70">Asttrolok</span></h3><br><div><div style="padding:10px;background:#1a4a70;color:#fff">ORDER DETAILS</div><br><br><div><div><table style="width:100%" cellpadding="5"><tbody><tr><td>Name</td><td>: '.$card_holder_name_id.'</td></tr><tr><td>Email Id</td><td>: <a href="mailto:'.$email.'" target="_blank" data-mt-detrack-inspected="true">'.$email.'</a> </td> </tr> <tr><td>Mobile No.</td><td>: '.$contact.'</td></tr><tr><td>Order Id</td><td>: '.$merchant_order_id.'</td></tr><tr><td>Transaction Id</td><td>: '.$razorpay_payment_id.'</td></tr><tr><td>Order Date</td><td>: '.$created.'</td></tr></tbody></table></div><br><br><div><table style="width:100%" cellpadding="5"><thead><tr><th style="text-align:left;border-bottom:1px solid #000" colspan="2">Item Details</th><th style="text-align:right;border-bottom:1px solid #000">Price</th></tr></thead><tbody><tr><td style="width:80px"></td><td>Astro-mani <div style="color:orange;text-transform:uppercase">course</div></td><td style="text-align:right">₹'.$merchant_amount.'</td></tr></tbody></table> <h4 style="text-align:right">Total Amount: ₹'.$merchant_amount.'</h4></div></div></div><br>
        <p>If you have any doubts, do not hesitate we are there. Write to us on -
          <a href="mailto:pratul@rechargestudio.com" target="_blank" data-mt-detrack-inspected="true">pratul@rechargestudio.com</a>
        </p>
        <p>Thank you for Ordering, we hope you have a happy time learning!</p>
        <br>
        <br>
        <br>
        
      </div>
    </div>
    
  </div>
</div>';
       $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

      // More headers
      $headers .= 'From:' .$email . "\r\n";
      //$headers .= 'From: <pratul.udainiya@gmail.com>' . "\r\n";

      mail($to,$subject,$htmlContent,$headers);
      mail($email,$subject,$htmlContent,$headers);
        session_destroy ();
?>

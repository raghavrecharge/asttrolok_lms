<?php 
define('RAZOR_KEY_ID', 'rzp_live_80LvVdqLPUaiKR');
define('RAZOR_KEY_SECRET', 'FyiZ6gn5TDRQjzCWYAPhCbao');
include('templates/header.php');
?>

<div class="ast_mapnform_wrapper ast_toppadder70">

	<div class="container">
		<div class="row">
			<div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 col-lg-offset-2 col-md-offset-2 col-sm-offset-1 col-xs-offset-0">
				<div class="ast_heading">
					<h2>User <span> Details  </span></h2>
				</div>
			</div>
		</div>
	</div>
	</div>
<div class="ast_journal_wrapper  ast_bottompadder70">
	<div class="ast_contact_map">
		<div class="col-lg-6 col-md-8 col-sm-8 col-xs-12 col-lg-offset-3 col-md-offset-2 col-sm-offset-2 col-xs-offset-0">
			<div class="ast_contact_form">
				<form name="razorpay_frm_payment" class="razorpay-frm-payment form-horizontal" id="razorpay-frm-payment" method="post">
<input type="hidden" name="merchant_order_id" id="merchant_order_id" value="Numero2020"> 
<input type="hidden" name="language" value="EN"> 
<input type="hidden" name="currency" id="currency" value="INR"> 
<input type="hidden" name="surl" id="surl" value="https://www.asttrolok.com/razorpay/success.php"> 
<input type="hidden" name="furl" id="furl" value="https://www.asttrolok.com/razorpay/failed.php"> 

   
   
       <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >
        <label for="inputEmail4">Amount</label>
        <input type="number"  id="amount" class="require" name="amount" placeholder="amount" value="" required>
      </div>
      
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <label for="inputEmail4">Full Name</label>
        <input type="text" name="billing_name" class="require"  id="billing-name"  Placeholder="Name" required> 
      </div>
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <label for="inputEmail4">Email</label>
        <input type="email" name="billing_email" class="require" id="billing-email" Placeholder="Email" required>
      </div>
 
       
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <label for="inputEmail4">Phone</label>
        <input type="text" name="billing_phone" class="require"  id="billing-phone" Placeholder="Phone" required>
      </div>
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <label for="inputEmail4">Address</label>
         <input type="text" name="billing_address" class="require"  Placeholder="Address">
      </div>
  
  

    <div class="row">
      <div class="col">
        <button type="button" class="ast_btn pull-right " id="razor-pay-now"><i class="fa fa-credit-card" aria-hidden="true"></i> Pay</button>
      </div>
    </div>

</form>
			</div>
		</div>
			</div>
</div>








<?php include('templates/footer.php');?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script type="text/javascript">
  jQuery(document).on('click', '#razor-pay-now', function (e) {
    var total = (jQuery('form#razorpay-frm-payment').find('input#amount').val() * 100);
    var merchant_order_id = jQuery('form#razorpay-frm-payment').find('input#merchant_order_id').val();
    var merchant_surl_id = jQuery('form#razorpay-frm-payment').find('input#surl').val();
    var merchant_furl_id = jQuery('form#razorpay-frm-payment').find('input#furl').val();
    var card_holder_name_id = jQuery('form#razorpay-frm-payment').find('input#billing-name').val();
    var merchant_total = total;
    var merchant_amount = jQuery('form#razorpay-frm-payment').find('input#amount').val();
    var currency_code_id = jQuery('form#razorpay-frm-payment').find('input#currency').val();
    var key_id = "<?php echo RAZOR_KEY_ID; ?>";
    var store_name = 'Asttrolok';
    var store_description = 'Numeromani Course Payment';
    var store_logo = 'https://www.asttrolok.com/assets/images/header/logos.png';
    var email = jQuery('form#razorpay-frm-payment').find('input#billing-email').val();
    var phone = jQuery('form#razorpay-frm-payment').find('input#billing-phone').val();
    
    jQuery('.text-danger').remove();

    if(card_holder_name_id=="") {
      jQuery('input#billing-name').after('<small class="text-danger">Please enter full mame.</small>');
      return false;
    }
    if(email=="") {
      jQuery('input#billing-email').after('<small class="text-danger">Please enter valid email.</small>');
      return false;
    }
    if(phone=="") {
      jQuery('input#billing-phone').after('<small class="text-danger">Please enter valid phone.</small>');
      return false;
    }
    
    var razorpay_options = {
        key: key_id,
        amount: merchant_total,
        name: store_name,
        description: store_description,
        image: store_logo,
        netbanking: true,
        currency: currency_code_id,
        prefill: {
            name: card_holder_name_id,
            email: email,
            contact: phone
        },
        notes: {
            soolegal_order_id: merchant_order_id,
        },
        handler: function (transaction) {
            jQuery.ajax({
                url:'callback.php',
                type: 'post',
                data: {razorpay_payment_id: transaction.razorpay_payment_id, merchant_order_id: merchant_order_id, merchant_surl_id: merchant_surl_id, merchant_furl_id: merchant_furl_id, card_holder_name_id: card_holder_name_id, merchant_total: merchant_total, merchant_amount: merchant_amount, currency_code_id: currency_code_id,contact: phone,email: email}, 
                dataType: 'json',
                success: function (res) {
                    if(res.msg){
                        alert(res.msg);
                        return false;
                    } 
                    window.location = res.redirectURL;
                }
            });
        },
        "modal": {
            "ondismiss": function () {
                // code here
            }
        }
    };
    // obj        
    var objrzpv1 = new Razorpay(razorpay_options);
    objrzpv1.open();
        e.preventDefault();
            
});
</script>
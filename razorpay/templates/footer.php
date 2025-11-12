<!-- Footer -->

  <!-- Bootstrap core JavaScript -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

            
            <div class="ast_footer_wrapper ast_toppadder70 ast_bottompadder20">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			    
			       
   
				<div class="ast_footer_info">
					<img src="https://www.asttrolok.com/backup/assets/images/header/logos.png" alt="Logo">
				
					
				</div>
			</div>
			 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
			<script>
			    
			    function newsletter(){
			        var mailnews=$('#newsletter').val();
			       // alert(mailnews);
			       if(mailnews== ''){
           $("#newsmsg1").html('Please Enter Email-id');
            setTimeout(function() {
    $('#newsmsg1').fadeOut('fast');
}, 5000);
          return false;
        }else{
        if(IsEmail(mailnews)==false){
          $("#newsmsg1").html('Email-id is invalid');
           setTimeout(function() {
    $('#newsmsg1').fadeOut('fast');
}, 5000);
          return false;
        }else{
			          $.ajax({
   type:"post",
  url:"https://www.asttrolok.com/welcome/newsletter",
    data:"mailid="+mailnews,
    success:function(data){
		//alert(data);
         // $('#mod').click();
    $("#newsmsg").html('Thank You For Joining Our Newsletter');
 $('#newsletter').val('');
 setTimeout(function() {
    $('#newsmsg').fadeOut('fast');
}, 5000);
  }
                             
                          });
        }
        }
                          }
			    function IsEmail(email) {
  var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  if(!regex.test(email)) {
    return false;
  }else{
    return true;
  }
}
			</script>
		<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
				<div class="widget text-widget">
				    
					<h4 class="widget-title">our newsletter</h4>
					<div class="ast_newsletter">
						<p>Subscribe to our newsletter to receive update for upcoming courses &amp; exclusive offers on our products and services.</p>
						<div class="ast_newsletter_box">
							<input id="newsletter" type="text" placeholder="Email" required="">
							<button type="submit" onclick="newsletter();"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
							<span id="newsmsg" style="color: green;"></span>
							<span id="newsmsg1" style="color: Red;"></span>
						</div>
					</div>				
				</div>			
			</div>
							
							
	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
				<div class="widget text-widget">
					<div class="ast_servicelink">
						<ul>
							<li><a href="https://www.asttrolok.com/astro_mani">Astro Mani (Basic course of Astrology)</a></li>
							<li><a href="https://www.asttrolok.com/astro_shiromani">Astro Shiromani (Advanced course of Astrology)</a></li>
							<li><a href="https://www.asttrolok.com/hastrekha_mani">Hastrekha Mani (Basic course of Astrology)</a></li>
							<li><a href="https://www.asttrolok.com/numero_mani">Numero Shiromani (Advanced course of Astrology)</a></li>
							<li><a href="https://www.asttrolok.com/vastu_mani">Vastu Mani (Basic course of Astrology)</a></li>
							<li><a href="https://www.asttrolok.com/vastu_shiromani">Vastu Shiromani (Advanced course of Astrology)</a></li>
							
						</ul>
					</div>				
				</div>			
			</div>		
							
		<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
				<div class="widget text-widget">
					<h4 class="widget-title">get in touch</h4>
					<div class="ast_gettouch">
						<ul>
							<li><i class="fa fa-home" aria-hidden="true"></i> <p>312, 3rd Floor, Vikram Urbane, 25-A Mechanic Nagar Extn., Sch# 54, Indore(MP) 452010</p></li>
							<li><i class="fa fa-at" aria-hidden="true"></i> <a href="#">astrolok.vedic@gmail.com</a></li>
							<li><i class="fa fa-phone" aria-hidden="true"></i> <p>+(91) 9174822333 </p></li>
							<li><i class="fa fa-phone" aria-hidden="true"></i> <p>+(91) 7000106621</p></li>
						</ul>
					</div>				
				</div>			
			</div>		
		
			
			
			
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="ast_copyright_wrapper">
					<p>© Copyright 2019, All Rights Reserved, <a rel="alternate" hreflang="en" href="#">Asttrolok </a></p>				
				</div>			
			</div>	
		</div>	
	</div>
</div>
<script type="text/javascript" src="https://www.asttrolok.com/backup/assets/js/jquery.js"></script> 
<script type="text/javascript" src="https://www.asttrolok.com/backup/assets/js/bootstrap.js"></script>
<script type="text/javascript" src="https://www.asttrolok.com/backup/assets/js/jquery.magnific-popup.js"></script>
<script type="text/javascript" src="https://www.asttrolok.com/backup/assets/js/owl.carousel.js"></script>
<script type="text/javascript" src="https://www.asttrolok.com/backup/assets/js/jquery.countTo.js"></script>
<script type="text/javascript" src="https://www.asttrolok.com/backup/assets/js/jquery.appear.js"></script>
<script type="text/javascript" src="https://www.asttrolok.com/backup/assets/js/custom.js"></script>
  <script type="text/javascript" src="https://www.asttrolok.com/backup/assets/js/bootstrap-timepicker.min.js"></script>
  <script type="text/javascript" src="https://www.asttrolok.com/backup/assets/js/bootstrap-datetimepicker.min.js"></script>
   <script type="text/javascript" src="https://www.asttrolok.com/backup/assets/js/bootstrap.min.js"></script>      

 
   

    <link hreflang="en" type="text/css" rel="Stylesheet" href="https://www.asttrolok.com/backup/assets/css/jquery-ui.css" />

<style>



.email-section, .email-section * {
  box-sizing: border-box;
  
}

.email-section, .email-section div {
  transition-duration: .6s;
}

.email-section {
position: fixed;
bottom: 0px;
width: 70px;
right: 0px;
  margin-bottom: 40px;
  display: inline-block;
  padding: .375em .375em 0;
  min-height: 2.5em;
  background: #A9ADB6;
  border-radius: .25em;
  perspective: 300;
  box-shadow: 0 -1px 2px #fff, inset 0 1px 2px rgba(0, 0, 0, 0.2), inset 0 0.25em 1em rgba(0, 0, 0, 0.1);
}

.email-button {
  text-align: center;
  transition-timing-function: ease;
  opacity: 0;
}
.email-button a {
  text-decoration: none;
  font-weight: bold;
  color: #009e30;
}

.email-cover {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  transform-origin: left bottom;
  transform-style: preserve-3d;
  font: 1.25em / 2 "icon";
  color: white;
  text-align: center;
  pointer-events: none;
  z-index: 100;
}

.email-inner, .email-outer {
  position: absolute;
  width: 100%;
  height: 100%;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  border-radius: .25em;
  background-image: -webkit-linear-gradient(top, transparent 0%, rgba(0, 0, 0, 0.1) 100%);
}


.email-outer {
  background-color: #00aa34;
  transform: translateZ(0.25em);
}

.email-inner {
  background-color: #00aa34;
}

.email-section:hover {
  background: #EBEFF2;
}
.email-section:hover .email-button {
  opacity: 1;
}
.email-section:hover .email-cover {
  transform: rotateY(-120deg);
}
.email-section:hover .email-inner {
  background-color: #00aa34;
}
.email-section:hover .email-outer {
  background-color: #00aa34;
}
.email-section:hover .email-cover, .email-section:hover .email-inner, .email-section:hover .email-outer {
  transition-timing-function: cubic-bezier(0.2, 0.7, 0.1, 1.1);
}
.learn1{
    color: #fff;
    
}
.learn1:hover {

    color:  #ff6f00;

}
</style>
<style>
.main {
 text-shadow: 0 1px 1px #b50236;
    -moz-transform: rotateX(0deg) translateZ(2.25em);
    -ms-transform: rotateX(0deg) translateZ(2.25em);
    -webkit-transform: rotateX(0deg) translateZ(2.25em);
    transform: rotateX(0deg) translateZ(2.25em);
}
.main1{
    display:none;
}
</style>

<style>.fragment {
    font-size: 12px;
    font-family: tahoma;
    height: 140px;
    border: 1px solid #ccc;
    color: #555;
    display: block;
    padding: 10px;
    box-sizing: border-box;
    text-decoration: none;
}

.fragment:hover {
    box-shadow: 2px 2px 5px rgba(0,0,0,.2);

}

.fragment img { 
    float: left;
    margin-right: 10px;
}


.fragment h3 {
    padding: 0;
    margin: 0;
    color: #369;
}
.fragment h4 {
    padding: 0;
    margin: 0;
    color: #000;
}
#close {
    float:right;
    display:inline-block;
    padding:2px 5px;
    background:#ccc;
}

#close:hover {
        float:right;
        display:inline-block;
        padding:2px 5px;
        background:#ccc;
    color:#fff;
    }
</style>
<script>window.onload = function(){
    document.getElementById('close').onclick = function(){
        this.parentNode.parentNode.parentNode
        .removeChild(this.parentNode.parentNode);
        return false;
    };
};</script>
<style>
    .ast_heading h2, .ast_timer_wrapper .ast_heading p {
    color: #333333;
}
.ast_heading h2 {
    float: left;
    width: 100%;
    margin: 0px 0px 10px 0px;
    text-transform: capitalize;
}
.ast_heading h2 span {
    color: #ff6f00;
}

</style>






</body>
</html>

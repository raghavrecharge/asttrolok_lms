@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <style>
.loader {
  //border: 16px solid #f3f3f3;
  //border-radius: 50%;
  //border-top: 16px solid #3498db;
  /*width: 120px;*/
  height: 120px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
/*@-webkit-keyframes spin {*/
/*  0% { -webkit-transform: rotate(0deg); }*/
/*  100% { -webkit-transform: rotate(360deg); }*/
/*}*/

/*@keyframes spin {*/
/*  0% { transform: rotate(0deg); }*/
/*  100% { transform: rotate(360deg); }*/
/*}*/
#loader {
    position: fixed;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    display: none;
}

 /*//Disable page */
.disabled-page {
    pointer-events: none;
    opacity: 0.5;
}
.error-message {
    color: red;
    font-size: 12px;
    display: none;
  }
</style>
@endpush

@section('content')
    <section class="container mt-45" style="background-color: whitesmoke;">
        <div class="row"> 
          <div class="col-12"> 
        <h2 class="section-title">Please Fill The Full Access Form</h2>
        <br><br>
        <form id="paymentForm">
              <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" class="form-control" >
                <small id="nameError" class="error-message"></small>
              </div>
              <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" >
                <small id="emailError" class="error-message"></small>
              </div>
              <div class="form-group">
                <label for="mobile">Mobile:</label>
                <input type="text" id="mobile" name="mobile" class="form-control" >
                <small id="mobileError" class="error-message"></small>
              </div>
              <div class="form-group">
                 <label for="course">Select course:</label>
                <select id="course" name="course"  class="form-control" >
                  <option value="">Select a course</option>
                   @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                  @endforeach
                </select>
                 <small id="courseError" class="error-message"></small>
              </div>
              <div class="form-group">
                <label for="amount">Course purchasing amount:</label>
                <input type="number" id="amount" name="amount" class="form-control" >
                <small id="amountError" class="error-message"></small>
              </div>
              <div class="form-group">
                <label for="paid_amount">Total Paid amount till now:</label> 
                <input type="number" id="paid_amount" name="paid_amount" class="form-control">
                <small id="paidAmountError" class="error-message"></small>
              </div>
               <center><div class="loader mt-50" id="loader" style="dispay:none ">
            <img width= '80px' height= '80px' src="{{ config('app.js_css_url') }}/assets/default/img/loading.gif">
            <br>
            <h3>Please do not refresh or close the page request is processing...</h3>
            </div></center>
              <div class="form-group">
                <label for="receive_amount">Total remaining amount:</label>
                <input type="number" id="receive_amount" name="receive_amount" class="form-control" >
                <small id="receiveAmountError" class="error-message"></small>
              </div>
              <div class="col-8 col-lg-3">
            </div> 
             <div class="col-4 col-lg-3 ">
               <div class="d-flex align-items-right justify-content-between mt-45">
                <button class="form-control btn btn-primary rounded-pill" type="submit">Submit</button>
            </div> 
            </div> 
            
        </form>
</div>
</div>
    </section>

@endsection

@push('scripts_bottom')
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   
<script>
$(document).ready(function() {
    $("#loader").css("display", "none");
     
});
document.getElementById('paymentForm').addEventListener('submit', function(event) {
  event.preventDefault(); 
  clearErrors();
  let isValid = validateForm();
  if (isValid) {
    submitForm();
  }
});

function validateForm() {
  let isValid = true;

  let name = document.getElementById('name').value;
  if (name.trim() === '') {
    showError('nameError', 'Name is required.');
    isValid = false;
  }

  let email = document.getElementById('email').value;
  if (email.trim() === '') {
    showError('emailError', 'Email is required.');
    isValid = false;
  } else if (!validateEmail(email)) {
    showError('emailError', 'Please enter a valid email address.');
    isValid = false;
  }

  let mobile = document.getElementById('mobile').value;
  if (mobile.trim() === '') {
    showError('mobileError', 'Mobile number is required.');
    isValid = false;
  } 
//   else if (!/^\d{14}$/.test(mobile)) {
//     showError('mobileError', 'Please enter a valid 10-digit mobile number.');
//     isValid = false;
//   }

  let course = document.getElementById('course').value;
  if (course.trim() === '') {
    showError('courseError', 'Please select a course.');
    isValid = false;
  }

  let amount = document.getElementById('amount').value;
  if (amount.trim() === '') {
    showError('amountError', 'Amount is required.');
    isValid = false;
  } 
//   else if (parseFloat(amount) <= 0) {
//     showError('amountError', 'Amount must be greater than 0.');
//     isValid = false;
//   }
  
  let paidAmount = document.getElementById('paid_amount').value;
  if (paidAmount.trim() === '') {
    showError('paidAmountError', 'Paid amount is required.');
    isValid = false;
  }
//   else if (parseFloat(paidAmount) < 0) {
//     showError('paidAmountError', 'Paid amount cannot be negative.');
//     isValid = false;
//   }

  let receiveAmount = document.getElementById('receive_amount').value;
  if (receiveAmount.trim() === '') {
    showError('receiveAmountError', 'Receive amount is required.');
    isValid = false;
  }
//   else if (parseFloat(receiveAmount) < 0) {
//     showError('receiveAmountError', 'Receive amount cannot be negative.');
//     isValid = false;
//   }

  return isValid;
}

function showError(elementId, message) {
  const errorElement = document.getElementById(elementId);
  errorElement.innerText = message;
  errorElement.style.display = 'block';
}

function clearErrors() {
  const errorMessages = document.querySelectorAll('.error-message');
  errorMessages.forEach(function(error) {
    error.innerText = '';
    error.style.display = 'none';
  });
}

function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function submitForm() {
   document.body.classList.add('disabled-page');
   document.getElementById('loader').style.display = 'block';
        
    // Collect form data
    var formData = {
      name: $('#name').val(),
      email: $('#email').val(),
      mobile: $('#mobile').val(),
      amount: $('#amount').val(),
      paid_amount: $('#paid_amount').val(),
      pending_amount: $('#receive_amount').val(),
      course: $('#course').val(),
      _token: '{{ csrf_token() }}'
    };
var   url="{{ url('/fullaccess')}}";
    // AJAX request
    $.ajax({
      url: url, 
      type: 'POST',
      data: formData,
      success: function(response) {
        // Handle success response
        document.body.classList.remove('disabled-page');
        document.getElementById('loader').style.display = 'none';
        // document.documentElement.style.overflow = 'none';
       if (response.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Data received successfully!',
          }).then(() => {
            
            $('#paymentForm')[0].reset();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.message,
          });
        }
        console.log(response);
      },
      error: function(xhr, status, error) {
        // Handle error response
        // alert('An error occurred. Please try again.');
        document.body.classList.remove('disabled-page');
        document.getElementById('loader').style.display = 'none';
        console.log(error);
      }
    });
}
</script>

<script>
     
//   $("#loader").css("display", "none");
  
    $(document).ready(function(){
        
         $('body').on('click', '#fullaccesss', function (e) {
            addscript();
         });
       
       $('body').on('change paste keyup', '#customer_name', function (e) {
        e.preventDefault();
        document.getElementById("user_name").value = $(this).val();
    }); 
    
    $('body').on('change paste keyup', '#customer_email', function (e) {
        e.preventDefault();
        document.getElementById("user_email").value = $(this).val();
        
    });   
    
    $('body').on('change', '#customer_number', function (e) {
        e.preventDefault();
        document.getElementById("user_number").value = $(this).val();
       
    });   
  
});

$(document).ready(function() {
$('#customer_number').on('keypress', function(e) {
 var $this = $(this);
 var regex = new RegExp("^[0-9\b]+$");
 var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
 // for 10 digit number only
 if ($this.val().length > 14) {
    e.preventDefault();
    return false;
  }
 
  if (e.charCode < 54 && e.charCode > 47) {
      if ($this.val().length == 0) {
        e.preventDefault();
        return false;
      } else {
        return true;
      }
  }
  if (regex.test(str)) {
    return true;
  }
  e.preventDefault();
  return false;
  });
});
</script>
@endpush


var payment_in_progress = false;
$(document).ready(function () {
    $(".thumbnail").on("click", function (e) {
        e.preventDefault();
        $(this).parent().find("img").addClass("d-none");
        $(this).parent().find("iframe").removeClass("d-none");
    });
    $(".testinomial-content-more").hide();
    $(".show_hide").on("click", function () {
        var txt = $(".testinomial-content-more").is(":visible") ? "Read More" : "";
        $(this).parent().find(".show_hide").text(txt);
        $(this).parent().find(".show_hide").hide();
        $(this).next(".testinomial-content-more").slideToggle(200);
    });
    $(".accordion-item").slice(0, 9).show();
    if ($(".accordion-item:hidden").length != 0) {
        $("#load").show();
    }
    $("#load").on("click", function (e) {
        e.preventDefault();
        $(".accordion-item:hidden").slice(0, 9).slideDown();
        if ($(".accordion-item:hidden").length == 0) {
            $("#load").text("No More to view").fadeOut("slow");
        }
    });
    $(".testimonial_video").slice(0, 1).show();
    if ($(".testimonial_video:hidden").length != 0) {
        $("#see_more").show();
    }
    $("#see_more").on("click", function (e) {
        e.preventDefault();
        $(".testimonial_video:hidden").slice(0, 1).slideDown();
        if ($(".testimonial_video:hidden").length == 0) {
            $("#see_more").text("No More to view").fadeOut("slow");
        }
    });
    $("#join_referral_code").on("keydown", function (e) {
        $("#join_referral_code_error").html("");
        $("#ref_code").show();
        if (e.keyCode == 8) {
            $("#join_referral_code_error").html("");
            $("#ref_code").show();
        }
    });
    $("#pay_button").click(function (e) {
        if (payment_in_progress) {
            return false;
        }
        e.preventDefault();
        $("#terms_error").html("");
        $(".input-focus").removeClass("input-focus");
        if ($("#name").val() == "") {
            $("#name").addClass("input-focus");
            $("#name").focus();
            $("#name_error").html("Please enter name");
            return false;
        } else {
            var checkString = $("#name").val();
            if (!/^[a-zA-Z\s]*$/.test(checkString)) {
                $("#name").addClass("input-focus");
                $("#name").focus();
                $("#name_error").html("Please enter valid name");
                return false;
            }
            $("#name_error").html("");
        }
        if ($("#email").val() == "") {
            $("#email").addClass("input-focus");
            $("#email").focus();
            $("#email_error").html("Please enter email");
            return false;
        } else {
            var check_email = $("#email").val();
            if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(check_email)) {
                $("#email").addClass("input-focus");
                $("#email").focus();
                $("#email_error").html("Please enter valid email");
                return false;
            }
            $("#email_error").html("");
        }
        if ($("#phone").val() == "") {
            $("#phone").addClass("input-focus");
            $("#phone").focus();
            $("#mob_error").html("Please enter phone number");
            return false;
        } else {
            var check_num = $("#phone").val();
            if (!/^[0-9]+$/.test(check_num)) {
                $("#phone").addClass("input-focus");
                $("#phone").focus();
                $("#mob_error").html("Please enter valid phone number");
                return false;
            }
            $("#mob_error").html("");
        }
        var country_code = $("#country_code").val();
        if (country_code == "+91") {
            var check_num = $("#phone").val();
            if (check_num.length == 10) {
                $("#mob_error").html("");
            } else {
                $("#phone").addClass("input-focus");
                $("#phone").focus();
                $("#mob_error").html("Please enter 10 digit phone number");
                return false;
            }
        }
        if ($("#city").val() == "") {
            $("#city").addClass("input-focus");
            $("#city").focus();
            $("#city_error").html("Please enter city");
            return false;
        } else {
            $("#city_error").html("");
        }
        var isValidate = true;
        if ($("#live_attend").prop("checked")) {
            $("#terms_error").html("");
            $("#live_attend").removeClass("border-blue");
        } else {
            $("#live_attend").addClass("input-focus");
            $("#live_attend").focus();
            $("#live_attend").addClass("border-blue");
            $("#terms_error").html(" Please select all three boxes above to proceed");
            isValidate = false;
        }
        if ($("#pre_recorded").prop("checked")) {
            $("#terms_error").html("");
            $("#pre_recorded").removeClass("border-blue");
        } else {
            $("#pre_recorded").addClass("input-focus");
            $("#pre_recorded").focus();
            $("#pre_recorded").addClass("border-blue");
            $("#terms_error").html(" Please select all three boxes above to proceed");
            isValidate = false;
        }
        if ($("#terms").prop("checked")) {
            $("#terms_error").html("");
            $("#terms").removeClass("border-blue");
        } else {
            $("#terms").addClass("input-focus");
            $("#terms").focus();
            $("#terms").addClass("border-blue");
            $("#terms_error").html(" Please select all three boxes above to proceed");
            isValidate = false;
        }
        if (!isValidate) {
            return false;
        }
        $("#pay_button").html("Loading...");
        $("#pay_button").prop("disabled", true);
        payment_in_progress = true;
        var name = $("#name").val();
        var amount = $("#amount").val();
        var city = $("#city").val();
        var email = $("#email").val();
        var phone = $("#country_code").val() + "" + $("#phone").val();
        var utm_source = $("#utm_source").val();
        var utm_medium = $("#utm_medium").val();
        var utm_campaign = $("#utm_campaign").val();
        var utm_term = $("#utm_term").val();
        var http_refer_id = $("#http_refer_id").val();
        var live_attend = $("#live_attend").is(":checked");
        var pre_recorded = $("#pre_recorded").is(":checked");
        var terms = $("#terms").is(":checked");
        var current_url = window.location.href;
        var ajax_page_url = page_url + "ajax/checkout/";
        $.ajax({
            url: ajax_page_url,
            type: "POST",
            data: {
                "reg-name": name,
                "reg-city": city,
                "reg-email": email,
                "reg-mob": phone,
                "reg-amount": amount,
                utm_source: utm_source,
                utm_medium: utm_medium,
                utm_campaign: utm_campaign,
                utm_term: utm_term,
                http_refer_id: http_refer_id,
                current_url: current_url,
                "workshop-date": workshop_date,
                live_attend: live_attend,
                pre_recorded: pre_recorded,
                terms: terms,
                sbmt_ajax: "submit_register_form",
            },
            success: function (data) {
                var options = jQuery.parseJSON(data);
                $("#razorpay_order_id").val(options.order_id);
                options.theme.image_padding = false;
                options.handler = function (payment) {
                    $("#razorpay_payment_id").val(payment.razorpay_payment_id);
                    $("#razorpay_signature").val(payment.razorpay_signature);
                    var form_data = $("#rxp_frm").serializeArray();
                    $.ajax({
                        url: ajax_page_url,
                        data: { razorpay_payment_id: $("#razorpay_payment_id").val(), razorpay_signature: $("#razorpay_signature").val(), razorpay_order_id: $("#razorpay_order_id").val(), sbmt_ajax: "payment_success" },
                        type: "POST",
                        success: function (response) {
                            payment_in_progress = false;
                            $("#pay_button").html("Pay Now");
                            var result = jQuery.parseJSON(response);
                            if (result.check == "success") {
                                var c_url = window.location.href;
                                var param = c_url.slice(page_url.length, c_url.length);
                                window.location = page_url + "thankyou?ref=" + param;
                            } else {
                                $("#success_msg").html("Payment Verification in progress...");
                            }
                        },
                        error: function (error) {
                            $("#success_msg").html("Payment Verification in progress...");
                        },
                    });
                };
                options.modal = {
                    ondismiss: function () {
                        payment_in_progress = false;
                        $("#pay_button").html("Pay Now");
                        $("#pay_button").removeAttr("disabled");
                        console.log("This code runs when the popup is closed");
                    },
                    escape: true,
                    backdropclose: false,
                };
                var rzp = new Razorpay(options);
                rzp.open();
            },
            error: function (xhr, status, error) {
                $("#success_msg").html("Payment Verification in progress...");
            },
        });
    });
    $("#submit_button").click(function (e) {
        e.preventDefault();
        $(".input-focus").removeClass("input-focus");
        if ($("#name").val() == "") {
            $("#name").addClass("input-focus");
            $("#name").focus();
            $("#name_error").html("Please enter name");
            return false;
        } else {
            var checkString = $("#name").val();
            if (!/^[a-zA-Z\s]*$/.test(checkString)) {
                $("#name").addClass("input-focus");
                $("#name").focus();
                $("#name_error").html("Please enter valid name");
                return false;
            }
            $("#name_error").html("");
        }
        if ($("#email").val() == "") {
            $("#email").addClass("input-focus");
            $("#email").focus();
            $("#email_error").html("Please enter email");
            return false;
        } else {
            var check_email = $("#email").val();
            if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(check_email)) {
                $("#email").addClass("input-focus");
                $("#email").focus();
                $("#email_error").html("Please enter valid email");
                return false;
            }
            $("#email_error").html("");
        }
        if ($("#phone").val() == "") {
            $("#phone").addClass("input-focus");
            $("#phone").focus();
            $("#phone_error").html("Please enter phone number");
            return false;
        } else {
            var check_num = $("#phone").val();
            if (!/^[0-9]+$/.test(check_num)) {
                $("#phone").addClass("input-focus");
                $("#phone").focus();
                $("#phone_error").html("Please enter valid phone number");
                return false;
            }
            $("#phone_error").html("");
        }
        var country_code = $("#country_code").val();
        if (country_code == "+91") {
            var check_num = $("#phone").val();
            if (check_num.length == 10) {
                $("#phone_error").html("");
            } else {
                $("#phone").addClass("input-focus");
                $("#phone").focus();
                $("#phone_error").html("Please enter 10 digit phone number");
                return false;
            }
        }
        if ($("#city").val() == "") {
            $("#city").addClass("input-focus");
            $("#city").focus();
            $("#city_error").html("Please enter city");
            return false;
        } else {
            $("#city_error").html("");
        }
        $("#submit_button").prop("disabled", true);
        var name = $("#name").val();
        var city = $("#city").val();
        var email = $("#email").val();
        var phone = country_code + $("#phone").val();
        var ajax_page_url = page_url + "ajax/checkout/";
        $.ajax({
            url: ajax_page_url,
            type: "POST",
            data: { name: name, city: city, email: email, phone: phone, sbmt_ajax: "submit_newsletter_form" },
            success: function (data) {
                var msg = jQuery.parseJSON(data);
                window.location = page_url + "subscription_successful";
            },
            error: function (xhr, status, error) {},
        });
    });
});
function openTestinomial(page) {
    var i;
    var x = document.getElementsByClassName("testimonial");
    for (i = 0; i < x.length; i++) {
        x[i].style.display = "none";
    }
    document.getElementById(page).style.display = "block";
}

let page_btn = document.querySelectorAll(".page-link");
let testinomial_content = document.querySelectorAll(".testimonial");
for (let i = 0; i < page_btn.length; i++) {
    page_btn[i].addEventListener("click", () => btnClick(i));
}
function btnClick(currentTab1) {
    removebtnActive();
    page_btn[currentTab1].classList.add("active");
    testinomial_content[currentTab1].classList.add("active");
}
function removebtnActive() {
    for (let i = 0; i < page_btn.length; i++) {
        page_btn[i].classList.remove("active");
        testinomial_content[i].classList.remove("active");
    }
}
function verifyReferralCode() {
    var join_referral_code = $("#join_referral_code").val();
    var ajax_page_url = page_url + "ajax/checkout/";
    $.ajax({
        url: ajax_page_url,
        type: "POST",
        data: { join_referral_code: join_referral_code, sbmt_ajax: "verify_referral_code" },
        success: function (data) {
            var msg = jQuery.parseJSON(data);
            $("#ref_code").hide();
            $("#join_referral_code_error").html(msg.message);
            if (msg.check == "failed") {
                $("#join_referral_code").val("");
                $("#join_referral_code").attr("readonly", false);
            }
        },
        error: function (xhr, status, error) {},
    });
}
$(document).ready(function () {
    if ($("#join_referral_code").val() != "") {
        $("#join_referral_code").attr("readonly", true);
        verifyReferralCode();
    }
    $("#join_referral_code").on("blur", function () {
        verifyReferralCode();
    });
});
$(document).ready(function () {
    $("#register_now_btn").hover(
        function () {
            $("#register-arrow").show(500);
        },
        function () {
            $("#register-arrow").hide(500);
        }
    );
    $("#register_right_button").hover(
        function () {
            $("#right_register_arrow").show(500);
        },
        function () {
            $("#right_register_arrow").hide(500);
        }
    );
});

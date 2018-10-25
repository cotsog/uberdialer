//This function for forgot popup
$(function () {
    
    // Set Remember me Variable  
    $('input[type="hidden"]').remove();
    $(".hidden").append("&lt;input name='RememberMe' type='hidden' value='false'&gt;");

    if ('Index' == "ForgotPassword") {
        $("#forgotPassword").slideToggle();
    }
    $('.forgot-password-popup').click(function () {
        $("#forgotPassword").slideToggle();
    });

// Set css For Menu  
    $("#user_lists").addClass("active open");
    $("#changePass").addClass("active");
});

// Set Validation 
$("#form").validate({
    rules: {
        old_password:"required",
        password: {
            minlength: 5
        },
        passconf: {
            minlength: 5,
            equalTo: "#password"
        }
    },
    messages: {
        old_password:"",
        password: {
            required: "",
            minlength: "Your password must be at least 5 characters long"
        },
        passconf: {
            required: "",
            minlength: "Your password must be at least 5 characters long",
            equalTo: "Please enter the same password as above"
    }
    }
});

// Add Css For cross button of  Error Message DIV
$("#divErrorMsg").click(function () {
    $("#divErrorMsg").hide();
});

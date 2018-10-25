$("#create_contact_btnSave").click(function () {

    $('#create_contact').validate({

        submitHandler: function (form) {
                form.submit();
        }
    });

    $("#first_name").rules("add", {
        required: true,
        maxlength: 100,
        messages: {
            required: ""
        }
    });
    $("#last_name").rules("add", {
        required: true,
        maxlength: 100,
        messages: {
            required: ""
        }
    });
    $("#phone").rules("add", {
        required: true,
        maxlength: 20,
        messages: {
            required: ""
        }
    });
    $("#email").rules("add", {
        required: true,
        email:true,
        maxlength: 100,
        messages: {
            required: ""
        }
    });
    $("#country").rules("add", {
        required: true,
        messages: {
            required: ""
        }
    });
    $("#eg_contact_id").rules("add",{
        required: true,
        messages: {
            required: ""
        },
        number: true

    });
});
$(document).ready(function () {
    NumericTextOnlyAllowed('eg_contact_id');
    NumericTextOnlyAllowed('priority');
});
$("#email").focusout(function () {
    var img_loader = '<img class="loader" alt="Processing.." src="https://s3.amazonaws.com/enterprise-guide/images/loading.gif">';
    $("#check_email").html(img_loader);
    var newEmailPostData = "email="+$("#email").val()+"&campaign_id="+$("#contact_campaign_id").val();
    $.ajax({
        type: "POST",
        data: newEmailPostData,
        url: "/dialer/contacts/contact_email_exist"
    }).success(function (result) {
        var response = JSON.parse(result);
        if(response.contact_id){
            window.location = "/dialer/contacts/create/"+$("#contact_campaign_id").val()+"/"+$("#list_id").val()+"?cid="+response.contact_id;
        }
        $("#check_email").html("");
    });
});
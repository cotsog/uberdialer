$('#editform').validate({
    rules: {
        //call_disposition: "required",
        first_name:"required",
        last_name: "required",
        phone:"required",
        country:"required",
        email: {
            required: true,
            email: true
        }        
    },
    messages: {
        //call_disposition: "Please Select Call Disposition Status",
        first_name:"",
        last_name: "",
        phone:"",
        country:"",
        email: {
            required: "",
            email: "Please enter a valid email address"
        }       
    }
});
    $(".btnCancel-view-list").click(function () {
        var edit_lead_status = '0';
        var call_list_id = $("#id").val();
        var campaign_id = $("#campaign_id").val();
        var list_id = $("#list_id").val();
         $.ajax({
            type: "POST",
            url: "/appt/contacts/lockEditContact/"+edit_lead_status+"/" + call_list_id,
            cache: false,
            async: false
        }).success(function (data1) {
            window.location.href = '/dialer/contacts/index/'+campaign_id+"/"+list_id;
        });                
    });

// Add as a new contact check email on focus out
$("#email").focusout(function () {
    var newEmailPostData = "email="+$("#email").val()+"&contact_id="+$("#id").val();
    $.ajax({
        type: "POST",
        data: newEmailPostData,
        url: "/dialer/contacts/contacts_email_exists"
    }).success(function (result) {
        var response = JSON.parse(result);
        if (!response.status) {
            $('#email').val('');
            ShowAlertMessage(response.message);
        }
    });
});

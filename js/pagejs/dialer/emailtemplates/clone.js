/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(function () {
    CKEDITOR.replace("body",{toolbar: 'Basic'});
});
$('#campaign_id').focus(function () {
}).change(function () {

    $("#siteName").val("");  
    $(".resourcedropdown").html("");
    unsetElements();       

    $(this).blur();
    var campaign = $(this).val();
    if(campaign !=''){
        var campaignurl = 'dialer/emailtemplates/getCampaignResources/';
        var postData = "campaignID="+campaign;
        AjaxCall(campaignurl, postData, "post", "json").done(function (response) {
            if (response.status == false) {
               $('#campaign_id').val(campaign);                    
               ShowAlertMessage(response.message);
            }else{
                $("#siteName").val(response.sitename);
                company_name = response.compnayname;

                var j =response.resources;
                resource_array = response.resources;

                var options = '<option role="option" value=""> ---SELECT ONE---</option>';
                for (var i = 0; i < j.length; i++) {
                    options += '<option role="option"  value="' + j[i].id + '">' + j[i].name + '</option>';                                       
                }
                $(".resourcedropdown").html(options);                      
            } 
        });
    }else{
        $("#siteName").val("");
        $(".resourcedropdown").html("");
        unsetElements();            
    }
});  

$('#resource_id').focus(function () {

}).change(function () {
    unsetElements();   
    $(this).blur();
    var resource = $(this).val();
    if(resource !="")
    {
        var result = $.grep(resource_array, function(e){ return e.id == resource ; });
        $("#resource_name").val(result[0].name);
        var resourceurl = 'dialer/emailtemplates/getBodyByResourceObject/';
        var postData = 'array='+JSON.stringify(result[0]);
        AjaxCall(resourceurl, postData, "post", "json").done(function (response) {
            if (response.status == false) {
                $('#resource_id').val("");                    
                ShowAlertMessage(response.message);
            }else{
                $("#subject_line").val(response.Temp.SubjectLine);
                CKEDITOR.instances.body.setData(response.Temp.Body);
                $('#signature_line').val(response.Temp.SignatureLine);                
            }
        });
    }else{
        unsetElements();
    }
});
function unsetElements(){
    $("#subject_line").val("");
    CKEDITOR.instances.body.setData("");
    $('#signature_line').val("");
}
//checkCampaignValue = function (){
//    campaignValue = $('#campaign_id').val();    
//    if (campaignValue != null && campaignValue >0) {
//        return true;
//    } else {
//        return false;
//    }
//};
$('#form').validate({
      ignore: [],
//  debug: true,
    rules: {
        campaign_id: "required",
        resource_id: {
            required: true
        },
        subject_line:"required",
        body: {
            required: function() 
            {
             CKEDITOR.instances.body.updateElement();
            }
        },
        signature_line:"required"     
    },
    messages: {
        campaign_id: "",
        resource_id: "",
        body: "",
        subject_line: "", 
        signature_line:""
    },
    errorPlacement: function(error, element) 
    {
        if (element.attr("name") == "body") 
       {
          element.next().css('border', '1px solid red');
          //error.insertBefore("textarea#body");
        } else {
            error.insertBefore(element);
        }
    }
});
$("#template_lists").addClass("active open");
$("#template_create").addClass("active");
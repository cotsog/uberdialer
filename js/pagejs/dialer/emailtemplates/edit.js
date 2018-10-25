/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(function () {
    CKEDITOR.replace("body",{toolbar: 'Basic'});
});

checkCampaignValue = function (){
    campaignValue = $('#campaign_id').val();    
    if (campaignValue != null && campaignValue >0) {
        return true;
    } else {
        return false;
    }
};
$('#form').validate({
    ignore: [],
    rules: {
        campaign_id: "required",
        resource_id: {
            required: checkCampaignValue
        },
        subject_line:"required",
        signature_line:"required",
        body: {
            required: function() 
            {
             CKEDITOR.instances.body.updateElement();
            }
        }
        
    },
    messages: {
        campaign_id: "",
        resource_id: "",
        subject_line: "",  
        signature_line: "",  
        body: "Template body required"
                
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
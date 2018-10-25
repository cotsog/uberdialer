/*** -Existing list or create new list- ****/

$(function () {
    //$("#newlist").prop('checked',true);
    $("#addlist").prop('required',true);
    if($('#newlist').length > 0){
        $("#selectlist").hide();
        $("#list_name").prop('required',true);
        $("#select_list_name").prop('required',false);
    }else{
        $("#addlist").show();
        $("#select_list_name").prop('required',true);
        $("#list_name").prop('required',false);
    }
});
$("#newlist").click(function () {
    $("#assign_list_section").hide();
    $('#campaign_name').val('');
    $("#addlist").show();
    $("#select_list_name").prop('required',false);
    $("#list_name").prop('required',true);
    $("#selectlist").hide();
    $("#file_mand").show();
});
$("#existinglist").click(function () {
    $("#assign_list_section").hide();
    $('#campaign_name').val('');
    $("#select_list_name").prop('required',true);
    $("#list_name").prop('required',false);
    $("#addlist").hide();
    $("#file_mand").hide();
});
/*** -Existing list or create new list- ****/

$("#campaign_item").addClass("active open");
$("#lists").addClass("active");
$(".fa-times-circle").css('cursor', 'pointer');
$(".divErrorMsg").css('cursor', 'pointer');
$(".fa-times-circle").click(function () {
    $("#divErrorMsg").hide();
});
$("#divErrorMsg").click(function () {
    $("#divErrorMsg").hide();
});

//Get agent list on change campaign
 function get_selected_campaign_value(selected_campaign_id){
     var campaignId = selected_campaign_id;
     var checked_id = $('input[type=radio][name=list_type]:checked').attr('id');

     if(checked_id == 'existinglist') {
         var get_list_by_campaign_url = 'dialer/campaigns/get_list_by_campaign';
         var postData = "campaignId=" + campaignId + "&list_type="+checked_id;
         AjaxCall(get_list_by_campaign_url, postData, "post", "json").done(function (response) {

            if(response.get_list_by_campaign != undefined){
                 if(checked_id != undefined && checked_id == 'existinglist'){
                     $('#selectlist').show();
                     $('#hidden_campaign_agents').val(response.agent_ids);
                 }

                 //contacts list based on selected campaign
                 var k = response.get_list_by_campaign;
                 var list_options = '<option role="option" value=""> ---SELECT ONE---</option>';
                 for (var l = 0; l < k.length; l++) {
                     list_options += '<option role="option"  value="' + k[l].id + '">' + k[l].list_name + '</option>';
                 }
                 $("#select_list_name").html(list_options);
                 if(list_id_segment != undefined && list_id_segment != ''){
                     $('#campaign_name').attr('disabled',true);
                     $('#select_list_name').attr('disabled',true);
                     $('#select_list_name').val(list_id_segment);
                     $('#newlist').attr('disabled',true);
                     $('#existinglist').attr('disabled',true);
                 }
            }else{
                         $("#select_list_name").html("");
            }     
        }); 
    }
 }

//Get agent list on change list
 function get_selected_list_value(selected_list_id){
     var list_id = selected_list_id;
     if(list_id != ''){
         var campaignId = $('#campaign_name').val();
         var get_agent_by_selected_list_url = 'dialer/campaigns/get_agent_by_selected_list';
         var postData = "list_id=" + list_id + '&campaignId='+campaignId;
         AjaxCall(get_agent_by_selected_list_url, postData, "post", "json").done(function (response) {
             if (response.status == false) {
                 $('#select_list_name').val('');
                 $('#assign_list_section').hide();
                 ShowAlertMessage(response.message);
                 $("#assign_agent_list").html("");
             } else {
                 $("#assign_agent_list").html("");
                 if(response.data != undefined){
                     $('#assign_list_section').show();
                     $('#status').val(response.data.status);
                     //Agent list based on selected campaign
                     var j = response.agent_list; // response.data;

                     var options = '<option role="option" value=""> ---SELECT ONE---</option>';
                     for (var i = 0; i < j.length; i++) {
                         if (jQuery.inArray( j[i].agent_id, response.data.agent_id) != -1) {
                             options += '<option role="option" selected="selected" value="' + j[i].agent_id + '">' + j[i].agent_name + '</option>';
                         }else if (jQuery.inArray( j[i].agent_id, response.data.agent_id) == -1){
                             options += '<option role="option"  value="' + j[i].agent_id + '">' + j[i].agent_name + '</option>';
                         };
                     }
                     $("#assign_agent_list").html(options);

                 }else{
                     $("#assign_agent_list").html("");
                 }
             }
         });
     }
 }

//$('#create_contatsform').validate({
//    //debug: true,
//    rules: {
//        campaign_name: "required",
//        userfile: {
//            //required: true,
//            required:'#newlist:checked',
//            extension: "csv"
//        }
//    },
//    messages: {
//        campaign_name: "",
//        userfile: {
//            required: "",
//            extension:"Invalid file selected, please select '.csv' file."
//        }
//    }    
//});

$('#campaign_btnSave').click(function(e){
    $('#create_contatsform').validate({
        submitHandler: function (form) {
            form.submit();
        }
    });
    if($("#status").val() == 'InActive'){
        $("#userfile").rules("add", {
                required: false,
                messages: {
                    required: ""
                }
            });
    }
    
    var file = $("#userfile").val();
      var exts = ['zip'];
      // first check if file field has any value
      if ( file ) {
        // split file name at dot
        var get_ext = file.split('.');
        // reverse name to check extension
        get_ext = get_ext.reverse();
        // check file type is valid as given in 'exts' array
        if ( $.inArray ( get_ext[0].toLowerCase(), exts ) > -1 ){
          $("#userfile_error").html("");
            $("#userfile_error").hide();
          $('#create_contatsform').submit();
        } else {
            $("#userfile_error").html("File must be in ZIP format.");
            $("#userfile_error").show();
            e.preventDefault();
        }
      }
});

$(document).ready(function(){
    var campaign_name = $("#campaign_name").val();
    if(campaign_name != "" && campaign_name != undefined){
        get_selected_campaign_value(campaign_name);
    }    
    if(list_id_segment != undefined && list_id_segment != '' && campaign_id_segment != undefined && campaign_id_segment != ''){
        $("#file_mand").hide();
        //get_selected_campaign_value(campaign_id_segment);
        $("#addlist").hide();
        $('#selectlist').show();
        $("#edit_mode").val("on");
    }
});


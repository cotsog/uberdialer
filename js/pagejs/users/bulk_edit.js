// Set Jquery Validation 
$('#form').validate({
    rules: {
        tmOfficeFrom: "required",
        tmOfficeTo:"required",
        teamlead:"required"
    },
    messages: {
        tmOfficeFrom: "",
        tmOfficeTo:"",
        teamlead:""
    }
});

$('#tmOfficeTo').focus(function () {
    prev_val = $(this).val();
}).change(function () {
    $(this).blur();
    var module_value = [];
    $.each($("#module option:selected"), function () {
        module_value.push($(this).val());
    });

    usertype = $("#user_type1 option:selected").text().toLowerCase();
        var tm_offices = [];
        $.each($("#tmOfficeTo option:selected"), function () {
            tm_offices.push($(this).val());
        });

        var get_tl_office_url = 'dialer/campaigns/get_tl_user_list/';
        var postData = "tm_offices=" + tm_offices + "&module_value=" + module_value;
        AjaxCall(get_tl_office_url, postData, "post", "json").done(function (response) {
            if (response.status == false) {
                $(this).val(prev_val);
                ShowAlertMessage(response.message);
            } else {
                if(response.data != undefined){
                    var j = response.data;
                    var options = '<option role="option" value=""> ---SELECT ONE---</option>';
                    for (var i = 0; i < j.length; i++) {
                        options += '<option role="option"  value="' + j[i].id + '">' + j[i].first_name + '</option>';
                    }
                    $("#teamlead").html(options);
                }else{
                    $("#teamlead").html('');
                }
            }
        });
});

$('#teamlead').change(function(){
    var teamLeadVal = $('#teamlead').val();
    if($('#tmOfficeFrom').val() == $('#tmOfficeTo').val() && teamLeadVal != ''){
        assignAgentOfficeTl($('#tmOfficeFrom'), teamLeadVal);
    }
});

// Get agent campaign wise 
function assignAgentOfficeTl(office, tmlead){
    var tmOfficeFrom = $(office).val();
    
    if (tmlead === undefined) {
          tmlead = 0;
    } 
   
    if(tmOfficeFrom){
       var url = 'users/getSiteAgents/';
       var tmLeadval = '';
       if(tmlead != 0){
           tmLeadval = '&teamLead='+tmlead;
       }    
        var postData = "tmOffice="+tmOfficeFrom+tmLeadval;
        AjaxCall(url, postData, "post", "json").done(function (response) {      
            if (response.status) {   
                var j =response.data.agentList;
                var options = '';
                var select = "<select multiple='multiple'  size = '20' name = 'agentlistbox[]'>";
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].id + '">' + j[i].agent_name + '</option>';
                }
                select +=  options;
                select +=" </select>";
                $("#selectdiv").html(select);

                var demo1 = $('select[name="agentlistbox[]"]').bootstrapDualListbox({
                    nonSelectedListLabel: 'Non-Selected Agent: ',
                    selectedListLabel: 'Selected Agent: ',
                    preserveSelectionOnMove: 'moved',                
                    moveOnSelect: false
                });
                $("#submit_group").show();
            }
            else {
                ShowAlertMessage(response.message);
            }
        }); 
    }else{
        $("#submit_group").hide();
        $("#selectdiv").html("");
    }   
}

$("#form").submit(function(e) {
    var dd = $('[name="agentlistbox[]"]').val();
    if(dd == null){
        ShowAlertMessage("Please select at least one agent to move");
        e.preventDefault();
    }
    $('#newselectedagents').val(dd);
    
});
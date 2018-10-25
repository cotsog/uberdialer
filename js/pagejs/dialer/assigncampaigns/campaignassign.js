// Set css For Menu  
$("#campaign_item").addClass("active open");
$("#campaign_assign").addClass("active");

$(".fa-times-circle").click(function () {
    $("#divErrorMsg").hide();
});
// Add Css For cross button of  Error Message DIV
$("#divErrorMsg").click(function () {
    $("#divErrorMsg").hide();
});

// Set Jquery Validation 
$('#form').validate({
    rules: {
        campaign_name: "required",
        team_leader:"required"
    },
    messages: {
        campaign_name: "",
        team_leader:""
    }
});

// get Team Leader Wise Campaigns
$('#team_leader').focus(function () {
    $(".tldiv").html("");  
    prev_tl_val = $(this).val();
        //old_status_val = $('#temp_status').val();
     
    }).change(function () {
    $("#selectdiv").html("");
    $("#managercampaign").html("");
    $(".managercampaigndiv").hide();  
        $(this).blur();
        var tl = $(this).val();
        if(tl !=''){
            var url = 'dialer/assigncampaigns/getTLCampaign/'+tl;
            var postData = "teamID="+tl;
            AjaxCall(url, postData, "post", "json").done(function (response) { 
                if (response.status == false) {
                   $('#team_leader').val(tl);                    
                   ShowAlertMessage(response.message);
                }else{
                    $(".managercampaigndiv").show();
                    $(".campaign_id_search").show();
                    var j =response.data;
                  
                    var select = '<select name="campaign_name" id="campaign_name" required="required" onchange="assignagentlist(this);">';
                    var options = '<option role="option" value=""> ---SELECT ONE---</option>';
                    for (var i = 0; i < j.length; i++) {
                        options += '<option role="option"  value="' + j[i].id + '">' + j[i].name + '</option>';                                       
                    }
                    select +=  options;
                    select +=" </select>";
                    $(".managercampaign").html(select);                      
                }
            }); 
        }
    });
    
 $(function () {  
    if(logedInUser){
        $(".managercampaigndiv").html("");
        $(".tldiv").show();  
        $(".campaign_id_search").show();
        $(".managerdiv").html("");  
        
    }else{
       $(".managerdiv").show();  
       $(".tldiv").html("");  
    }
 });
 
 $("#campaign_search").click(function(){
     assignagentlist($("#campaign_id"));
 });
 
 // Get agent campaign wise 
function assignagentlist(campaign_value){
    var campaignId = $(campaign_value).val();
    if(campaignId){
        var team_leader = $("#team_leader").val();
        var searchByEgCampaignId = 0;
        if($(campaign_value).attr("name") == 'campaign_id'){
            searchByEgCampaignId = 1;
        }
       var url = 'dialer/assigncampaigns/getCampaignAgent/'+$(campaign_value).val();
        var postData = "campaignID=" + $(campaign_value).val()+"&tl="+team_leader+"&searchByEgCampaignId="+searchByEgCampaignId;
        AjaxCall(url, postData, "post", "json").done(function (response) {      
            if (response.status) {   
                var selectedAgents = 0;
                var j =response.data.campaignList;
                var options = '';
                selectedAgents = response.data.agentAssign.agent_ids;
                $('#oldselectedagents').val(selectedAgents);
                var select = "<select multiple='multiple'  size = "+j.length+" name = 'agentlistbox[]'>";
                for (var i = 0; i < j.length; i++) {
                    if(selectedAgents){                    
                        if(selectedAgents.split(',').indexOf(j[i].id) > -1 ) {
                             options += '<option selected="selected"  value="' + j[i].id + '">' + j[i].agent_name + '</option>';
                        }else{
                            options += '<option value="' + j[i].id + '">' + j[i].agent_name + '</option>';
                        } 
                    }else{
                        options += '<option value="' + j[i].id + '">' + j[i].agent_name + '</option>';
                    }
                    
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
                $("#campaign_id").val(response.eg_campaign_id);
                $("#campaign_name").val(response.campaign_id);
            }
            else {
                ShowAlertMessage(response.message);
            }
        }); 
    }else{
        $("#selectdiv").html("");
    }   
}

$("#form").submit(function() {
    if($('#team_leader').val()!='' && $('#campaign_name').val()!=''){
        var dd = $('[name="agentlistbox[]"]').val();
        $('#newselectedagents').val(dd);
    }
});
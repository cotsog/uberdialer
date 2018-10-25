$('.pagination li a').click(function() {
$('#save_filter').val('');
$('#callcontacts_searchform').attr('action', $(this).attr('href'));
$('#callcontacts_searchform').submit();
 return false;
});

$('#sort_column a').click(function() {
$('#save_filter').val('');
$('#callcontacts_searchform').attr('action', $(this).attr('href'));
$('#callcontacts_searchform').submit();
 
 return false;
});

function checkAll() {
    var checkboxes = document.getElementsByTagName('input');
	 var val = null;
    if(checkboxes.length>0){
       for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].type == 'checkbox') {
                if (val === null) val = checkboxes[i].checked;
                checkboxes[i].checked = val;
            }
        }
    }  
 }

$("#delete_contact").click(function () { 
    $('#save_filter').val('');
    var theCheckboxes = $("input[type='checkbox']");
    
    if (theCheckboxes.filter(":checked").length < 1) {
        ShowAlertMessage("Please select at least one Contact for delete.");
        return false;
    }
    // selected checkox Array //
    var checkedIdArray = $('input:checkbox:checked').map(function () {
        return this.id.replace('jqg_list_', '');
    });
    
    // unchecked checkbox array 
    var unCheckedIdArray = $('input:checkbox:not(:checked)').map(function () {
        return this.id.replace('jqg_list_', '');
    });
    
    // get ids from Checkbox Array 
    selected_contact_id = ($.makeArray(checkedIdArray).join(','));
    contact_id = selected_contact_id.replace('jqg_list_', '');
        
    unchk_id = ($.makeArray(unCheckedIdArray).join(','));
    unchecked_contact_id = unchk_id.replace('jqg_list_', '');
   
    var msg = '';
    
// to check select all records or not   
// checkedflage = to check unchecked id is passed or checked 

    if($('#jqg_list_29').not(':checked').length){
        checkedflage = 1;
        contacts = "IDs="+contact_id;
        msg = 'Do you want to delete selected Contact(s) ?';
    }else{     
        checkedflage = 0;
        contacts = "IDs="+unchecked_contact_id;
        msg = 'Do you want to delete ALL sub paginatd Contact(s) ?';
    }

    ShowConfirm(msg, function () {
        
        var deleteUserURL = "dialer/contacts/delete_selected_contacts/"; 
        
        // Pass flag & ids 
        contactData = contacts + "&checkedflage=" + checkedflage;
       
       // Pass Form Data ( Filters) 
        formData = $('#callcontacts_searchform').serialize();   //+ "&statusValue=" + postStatusValue
        
        postData = contactData +"&" +formData;
        
        AjaxCall(deleteUserURL,postData, "post", "json").done(function (response) {
            if (response.status) {
                //location.reload();
                location.reload(true);
            }
            else {
                ShowAlertMessage(response.message);
                $(":checkbox:checked").prop('checked', false);
                return false;
            }
            });
        }
        , function () {
            ShowAlertMessage(response.message);
            $(":checkbox:checked").prop('checked', false);
            return false;
        },
        'Remove Contacts'
        );
}); 

$('#save_list_contact').click(function(){
    var job_function = $('#job_function').val();
    var job_level = $('#job_level').val();
    var company_size = $('#company_size').val();
    var industry = $('#industry').val();
    var country = $('#country').val();

    $('#save_filter').val('save_filter');

    if(job_function == null && job_level == null && company_size == null && industry == null && country == null){
        var msg = 'Do you want to clear filter ?';
        ShowConfirm(msg, function () {
                $('#callcontacts_searchform').submit();
        }
        , function () {
                return false;
            },
            'Update Filter','Confirm'
        );
        return false;
    }else{
        $('#callcontacts_searchform').submit();
    }
});

// clear button event
function clear_filter(campaign_id,list_id){
    if(campaign_id != undefined){
         window.location.href = '/dialer/contacts/edit_campaign_contacts/'+campaign_id+'/'+list_id+'/clear';
    }else{
        ShowAlertMessage("Campaign Id missing.");
        return false;
    }
}

$("#divErrorMsg").click(function () {
    $("#divErrorMsg").hide();
});

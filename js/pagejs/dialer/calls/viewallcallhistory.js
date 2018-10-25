function showHide(shID) {

    if (document.getElementById(shID)) {
        if (document.getElementById(shID+'-show').style.display != 'none') {
            document.getElementById(shID+'-show').style.display = 'none';
            document.getElementById(shID).style.display = 'block';
        }
        else {
            document.getElementById(shID+'-show').style.display = 'inline';
            document.getElementById(shID).style.display = 'none';
        }
    }
}

function retrieve_recording(call_uuid, plivo_id, confSid){
    if(call_uuid != "" && plivo_id != ""){
        $.ajax({ url: "/utilities/retrieve_call_recording/"+call_uuid+"/"+plivo_id+"/"+confSid,
                data: {},
                dataType: "json",
                type: "POST",
                success: function(data)
                {
                    console.log(" == "+ call_uuid +" == "+ plivo_id);
                    if(data.recording)
                    {
                        $("#rec_link_"+plivo_id).html('<a href="'+data.recording+'" target="_blank">Rec Retrieved</a>');
                        $("#message_"+plivo_id).html('');
                    }
                    else
                    {
                        $("#message_"+plivo_id).html('No recording found.');
                    }
                    
                },
                error: function(error){
                    console.log(error);
                },
                beforeSend:function()
                {
                    $("#message_"+plivo_id).html('<img src="https://s3.amazonaws.com/enterprise-guide/images/ajax-loader.gif" alt="Processing.." />');
                }
            });
    }else{
        alert('No recording found.');
    }
}
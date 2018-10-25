$("#campaign_item").addClass("active open");
$("#campaign_lists").addClass("active");
$(".delete_list").click(function () {
        var id = $(this).attr('id');
        ShowConfirm('Do you want to delete this list ?', function () {
            var ids = id.split("_");
            var campaign_id = ids[0];
            var list_id = ids[1];
            var postData = "campaign_id="+campaign_id+"&list_id="+list_id;
            $.ajax({
                type: "POST",
                url:  "/dialer/lists/deletelist",
                data: postData
            }).success(function (result){
                    window.location.reload();
            });
        });
    });
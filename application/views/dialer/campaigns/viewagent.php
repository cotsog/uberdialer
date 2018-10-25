
<section class="section-content-main-area">
    <div class="content-main-area">

        <div class="col-lg-6  pad-15-b" >
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                <div class="column-header">
                    <p><?= $campaignDetail->name." ".$campaignDetail->type?></p>
                </div>
            </div>
            <div class="pad-15-t pad-15-l row-left-pad">
                <table class="table table-bordered row vertical-tbl">
                    <thead>
                        <tr>
                            <th class="aligncenter">Agent Name</th>
                            <th class="aligncenter">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($campaignAssign as $assignArray){?>
                        <tr>
                            <td><?php echo $assignArray->first_name." ".$assignArray->last_name?></td>
                            <td align="center"><?php echo $assignArray->status?></td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</section>
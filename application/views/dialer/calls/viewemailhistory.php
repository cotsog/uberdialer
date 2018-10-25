<section class="section-content-main-area">

    <div class="content-main-area">

        <div class="pad-15-b" >
            <div class="pad-15-t pad-15-l  call-row-title">
                <div class="column-header">
                    <p>View Email History</p>
                </div>
            </div>

            <div class="pad-15-t pad-15-lr ">
                <table class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Resource Name</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($contact_email_history_list)) {

                        foreach ($contact_email_history_list as $key => $email_history_list) {
                                echo '<tr>';
                                ?>
                                <td><?php echo $email_history_list->agent_name; ?></td>
                                <td><?php echo $email_history_list->resource_name; ?></td>
                                <?php
                                echo '</tr>';
                            }
                    } ?>

                    </tbody>
                </table><br/><br/>

            </div>
        </div>
    </div>
<div class="clearfix"></div>
</section>

<style>
   td {
    word-break: break-word;
    word-wrap: break-word;
}
</style>
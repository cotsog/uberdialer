<section class="section-content-main-area">

    <div class="content-main-area">

        <div class="pad-15-b" >
            <div class="pad-15-t pad-15-l  call-row-title">
                <div class="column-header">
                    <p>View All Other Contacts</p>
                </div>
            </div>

            <div class="pad-15-t pad-15-lr ">
                <table class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                        <tr>
                            <th class="aligncenter" width="30%">Name of Prospect</th>
                            <th class="aligncenter" width="10%">Job Title</th>
                            <th class="aligncenter" width="60%">URL to contact details</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($viewAllOtherContacts)) {
                             foreach ($viewAllOtherContacts as $key => $contactDetails) {
                                echo '<tr>';
                                ?>
                                <td><?php echo $contactDetails['prospect']; ?></td>
                                <td><?php echo $contactDetails['job_title']; ?></td>
                                <td><?php echo $contactDetails['url']; ?></td>
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
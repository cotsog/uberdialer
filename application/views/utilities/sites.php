<section class="section-content-main-area">
    <div class="content-main-area">
        <?php
            
        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        } ?>
        <div class="column-header query-list">
            <div class="alignleft">
                <span class="column-title">TM Sites</span>
            </div>
            <div class="icons">
                    <a href="/utilities/createsite" class="add-icon"><i class="fa add-tooltip"></i></a>
            </div>
            <div class="pad-15-t pad-15-lr ">
                <table id="sites-lists" class="table table-bordered row vertical-tbl sort-th lead_table" style="width: 100%;table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="width:88%;" class="" id="sort_column">Name</th>
                            <th style="width:6%;" class="" id="sort_column"></th>
                            <th style="width:6%;" class="" id="sort_column"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(!empty($tm_offices)){
                            foreach($tm_offices as $office){ ?>
                                <tr  style="word-break: break-all">
                                    <td><?php echo $office['name']; ?></td>
                                    <td class="aligncenter"><a href="/utilities/editsite/<?=$office['id']?>" class="grid-link"><i class="fa fa-edit grid-icon list-edit-font"></i></td>
                                    <td class="aligncenter"><a href="/utilities/removesite/<?=$office['id']?>" class="grid-link"><i class="fa fa-trash grid-icon list-trash-font"></i></td>
                                </tr>
                                <?php
                                if(!empty($subOffices[$office['id']])){
                                    $subOfficesArray = $subOffices[$office['id']];
                                    foreach($subOfficesArray as $sub){
                                    ?>
                                <tr style="word-break: break-all">
                                    <td>&nbsp;&nbsp;&nbsp; <?php echo $sub['name']; ?></td>
                                    <td class="aligncenter"><a href="/utilities/editsite/<?=$sub['id']?>" class="grid-link"><i class="fa fa-edit grid-icon list-edit-font"></i></td>
                                    <td class="aligncenter"><a href="/utilities/removesite/<?=$sub['id']?>" class="grid-link"><i class="fa fa-trash grid-icon list-trash-font"></i></td>
                                </tr>
                                    <?php
                                    }
                                }
                                ?>
                        <?php } }else{ ?>
                            <tr>
                                <td colspan='3'><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

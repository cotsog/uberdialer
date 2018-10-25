<?php
//------------------------------>
// Error Message if any
//------------------------------>

if (isset($msg)) {
    echo '<h2 class="warning">' . $msg . '</h2>';
}
?>

<section class="section-content-main-area">
    <div class="content-main-area">
        <?php
        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good')
                $class = "class= 'error-msg good'";
            else
                $class = "class='error-msg  bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo (' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        }
        ?>
        <div id="ajax-content-container"></div>
        <div class="column-header query-list">
            <div class="alignleft">
                <span class="column-title">Data Research Component</span>
            </div>

        </div>

    </div>
</section>
<script type="text/javascript">
    $("#data_research_team").addClass("active");
</script>



<section class="section-content-main-area">
    <div class="content-main-area">
        <form class="popup-form account-detail-dialog campaign-form">
        <div class="form-section-title">
            <p>TEMPLATE VIEW</p>
            <span></span>
        </div>
        <div class="form-row">
            <div class="dialog-form ">
                <label>Campaign Name:</label>
                <div class="form-view">
                    <label class="view-text-field"><?php if (!empty($template->name)) { echo $template->name; } ?></label></div>
            </div>
            <div class="dialog-form ">
                <label>TM Brand:</label>
                <div class="form-view">
                    <label class="view-text-field"><?php if (!empty($template->site_name)) {
                            echo $template->site_name;
                        }else{echo ' - ';} ?></label></div>
            </div>
            <div class="dialog-form ">
                <label>Status:</label>
                <div class="form-view">
                    <label class="view-text-field"><?php if (!empty($template->status)) {
                            echo ucfirst($template->status);
                        } ?></label></div>
            </div>
            <div class="dialog-form ">
                <label>Resource Name:</label>
                <div class="form-view">
                    <label class="view-text-field"><?php if (!empty($template->resource_name)) {
                            echo $template->resource_name;
                        } ?></label></div>
            </div>
            <div class="dialog-form ">
                <label>Subject Line:</label>
                <div class="form-view">
                    <label class="view-text-field"><?php if (!empty($template->subject_line)) {
                            echo $template->subject_line;
                        } ?></label></div>
            </div>
            <div class="dialog-form ">
                <label class="vertical-top">Template Body :</label>
                <div class="form-view">
                    <label class="view-text-field view-template-body"><?php if (!empty($template->body)) {
                            echo $template->body;
                        }else{echo ' - ';} ?></label></div>
            </div>
            <div class="dialog-form ">
                <label> Signature Line:</label>
                <div class="form-view">
                    <label class="view-text-field"><?php if (!empty($template->signature_line)) {
                            echo $template->signature_line;
                        } ?></label>
                </div>
            </div>
        </div>
        </form>
    </div>
    <div class="clearfix"></div>
</section>




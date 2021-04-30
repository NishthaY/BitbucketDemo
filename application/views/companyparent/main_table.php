<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($table_title) ) $table_title = "Parents";
    if ( ! isset($table_description) ) $table_description = "";
?>
<div class="card-box table-responsive hidden">
    <h4 class="m-t-0 header-title"><b><?=$table_title?></b></h4>
    <p class="text-muted font-13 m-b-30">
        <?=$table_description?>
    </p>
    <table id="parents_table" class="table table-striped hidden" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Parent Name</th>
                <th>Address</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
             <?php
                if ( !empty($data) ) {
                    foreach($data as $item) {
                        $item = array_change_key_case ( $item, CASE_LOWER );
                        RenderViewSTDOUT("companyparent/main_table_row", $item);
                    }
                }
            ?>
        </tbody>
    </table>
</div>

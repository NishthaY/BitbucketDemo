<?php
    if ( ! isset($upload_plan_types) ) $upload_plan_types = array();
    if ( ! isset($all_plan_types) ) $all_plan_types = array();
    if ( ! isset($external_value) ) $external_value = "";
    if ( ! isset($errors) ) $errors = array();
?>
<div id="wizard_errors" class="alert alert-danger hidden" role="alert">
    <span class="alert-message">
        <div>
            <h4 class="page-title">Please Correct the Following Errors:</h4>
            <div class="row">
                <div class="col-sm-12">
                    <p>
                        <ul id="wizard_error_list">
                            <?php
                                foreach($errors as $error)
                                {
                                    print "<li>{$error}</li>";
                                }
                            ?>
                        </ul>
                    </p>
                </div>
            </div>
        </div>
    </span>
</div>
<div class="panel panel-color panel-primary" >
    <div id="sample_data_container" class="panel-body">
        <div class="row">
            <div class='col-sm-3'><strong>Found In File</strong></div>
            <div class='col-sm-9  p-b-10'><strong>Assigned Plan Type</strong></div>
            <?php
            $count = 0;
            foreach($upload_plan_types as $plan_type) {


                $external_value = getArrayStringValue("display", $plan_type);
                $external_key = getArrayStringValue("name", $plan_type);

                $view_array = array();
                $view_array = array_merge($view_array, array("index" => $count));
                $view_array = array_merge($view_array, array("external_value" => $external_value));
                $view_array = array_merge($view_array, array("dropdown_items" => $all_plan_types));
                $view_array = array_merge($view_array, array("unselected_display" => "Assign Plan Type"));
                $view_array = array_merge($view_array, array("remove_map_display" => "Unassign Plan Type"));
                $view_array = array_merge($view_array, array("selected_value" => UserDefaultPlanType($external_key)));

                $count++;

                ?>
                <div class='col-sm-3'><?=$external_value?></div>
                <div class='col-sm-9  p-b-10'><?=RenderViewAsString("templates/form/wizard/plan_type_mapping_dropdown", $view_array);?></div>
                <?php
            }
            ?>
        </div>
    </div>
</div>

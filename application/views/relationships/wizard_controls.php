<?php
    if ( ! isset($page_header) ) $page_header = "";
    if ( ! isset($relationship_form) ) $relationship_form = "";
    if ( ! isset($data) ) $data = array();
    if ( ! isset($dropdown) ) $dropdown = array();
    if ( ! isset($pricing_model) ) $pricing_model = "individual";
    if ( ! isset($pref_url) ) $pref_url = "";
    if ( ! isset($pref_group) ) $pref_group = "";
    if ( ! isset($pref_groupcode) ) $pref_groupcode = "";


    $individual_checked = "";
    $grouped_checked = "";
    switch(strtoupper($pricing_model) )
    {
        case "GROUPED":
            $grouped_checked = "checked";
            break;
        default:
            $individual_checked = "checked";
            break;
    }


?>
<div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
<?=$page_header?>
<div class="row">
    <div class="col-sm-12">
        <p class="text-muted page-title-alt">
            Following are the relationships detected from your data.  Review the relationships and map any missing types if needed.
        </p>
    </div>
</div>
<?=$relationship_form?>
<!--
<div class="row">
    <div class="col-md-7">
        <div class="panel panel-color panel-primary" >
            <div id="relationships_review_table" class="panel-body">
                <div class="row header">
                    <div class="col-xs-3 header"><h4><strong>Relationship</strong></h4></div>
                    <div class="col-xs-9 header"><h4><strong>Relationship Type</strong></h4></div>
                </div>
                <?php
                foreach($data as $item)
                {
                    // Collect the information about this relationship mapping.
                    $id = getArrayStringValue("CompanyRelationshipId", $item);
                    $relationship = getArrayStringValue("UserDescription", $item);
                    $relationship_code = getArrayStringValue("RelationshipCode", $item);
                    $relationship_desc = getArrayStringValue("RelationshipDescription", $item);

                    // Best guess.
                    // If we don't have a relationship assigned, use the user description to pull
                    // a best guess if we can.
                    if ($relationship_code == "" )
                    {
                        $best_guess = $this->Relationship_model->select_relationship_best_guess($relationship);
                        if ( ! empty($best_guess) )
                        {
                            $relationship_code = getArrayStringValue("RelationshipCode", $best_guess);
                            $relationship_desc = getArrayStringValue("RelationshipDescription", $best_guess);
                        }
                    }

                    // Unkown Relationship.
                    // If we did not have or could not guess the relationship, update the UI to reflect this
                    // by setting the description to unassigned and show the question indicator.
                    if ( $relationship_desc == "" ) $relationship_desc = "Unassigned";
                    $indicator = "question_indicator";
                    if ( $relationship_code != "" ) $indicator = "no_indicator";

                    // Build the view array needed to generate the dropdown.
                    $dropdown_array = array();
                    $dropdown_array = array_merge($dropdown_array, array("company_relationship_id" => $id));
                    $dropdown_array = array_merge($dropdown_array, array("button_label" => $relationship_desc));
                    $dropdown_array = array_merge($dropdown_array, array("button_value" => $relationship_code));
                    $dropdown_array = array_merge($dropdown_array, array("dropdown" => $dropdown));
                    $dropdown_array = array_merge($dropdown_array, array("href" => base_url("relationships/save")));
                    ?>
                    <div class="row body">
                        <div class="col-xs-3 col line-right dimished-line " data-relationship="<?=$relationship?>"><div><?=$relationship?></div></div>
                        <div class="col-xs-9 col dimished-line " data-company-relationship-id="<?=$id?>"><?=RenderViewAsString("relationships/relationship_dropdown", $dropdown_array);?></div>
                    </div>
                    <?php
                }
                ?>

            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="widget-bg-color-icon card-box">
            Dependents may have different pricing models.  Please select the model that matches how the data is represented in your file.
            <div class="radio radio-primary p-t-20">
                <input data-href='<?=$pref_url?>' data-group='<?=$pref_group?>' data-groupcode='<?=$pref_groupcode?>' class='preference-item' type="radio" name="dependent_pricing_model" id="individual" value="individual" <?=$individual_checked?>>
                <label for="individual">
                    <strong>Individual Pricing</strong> - Each dependent record reflects the cost for that specific individual.
                </label>
            </div>
            <div class="radio radio-primary p-t-10">
                <input data-href='<?=$pref_url?>' data-group='<?=$pref_group?>' data-groupcode='<?=$pref_groupcode?>' class='preference-item' type="radio" name="dependent_pricing_model" id="grouped" value="grouped" <?=$grouped_checked?>>
                <label for="grouped">
                    <strong>Grouped Pricing</strong> - Each dependent records reflects the total cost for all dependets associated with the policy holder.
                </label>
            </div>
        </div>
    </div>
</div>
</form>
-->

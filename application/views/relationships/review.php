<?php
    if ( ! isset($data) ) $data = array();
    if ( ! isset($dropdown) ) $dropdown = array();
    if ( ! isset($pricing_model) ) $pricing_model = "individual";
    if ( ! isset($pref_url) ) $pref_url = "";
    if ( ! isset($pref_group) ) $pref_group = "";
    if ( ! isset($pref_groupcode) ) $pref_groupcode = "";


    $individual_checked = "";
    $grouped_checked = "";
    $grouped_family_checked = "";
    switch(strtoupper($pricing_model) )
    {
        case "GROUPED":
            $grouped_checked = "checked";
            break;
        case "GROUPED_FAMILY":
            $grouped_family_checked = "checked";
            break;
        default:
            $individual_checked = "checked";
            break;
    }


?>
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
            <p>
                Cost and volume data may show up differently per-family member or per-dependent based on how your benefits administration system exports the data.  Please select the model that matches how the data is represented in your file so our system can compensate for it.
            </p>
            <div class="radio radio-primary p-t-20">
                <input data-href='<?=$pref_url?>' data-group='<?=$pref_group?>' data-groupcode='<?=$pref_groupcode?>' class='preference-item' type="radio" name="dependent_pricing_model" id="individual" value="individual" <?=$individual_checked?>>
                <label for="individual">
                    <strong>Individual Pricing</strong> - Each record reflects the cost and volume for that specific individual, or the record for the employee reflects the total cost for the family and the other family members do not have cost and volume reflected in the file.
                </label>
            </div>
            <div class="radio radio-primary p-t-10">
                <input data-href='<?=$pref_url?>' data-group='<?=$pref_group?>' data-groupcode='<?=$pref_groupcode?>' class='preference-item' type="radio" name="dependent_pricing_model" id="grouped_family" value="grouped_family" <?=$grouped_family_checked?>>
                <label for="grouped_family">
                    <strong>Grouped Family Pricing</strong> - The benefit cost and volume on the employee reflects the total cost for the entire family, and these values are duplicated on the spouse and dependent records.
                </label>
            </div>
            <div class="radio radio-primary p-t-10">
                <input data-href='<?=$pref_url?>' data-group='<?=$pref_group?>' data-groupcode='<?=$pref_groupcode?>' class='preference-item' type="radio" name="dependent_pricing_model" id="grouped" value="grouped" <?=$grouped_checked?>>
                <label for="grouped">
                    <strong>Grouped Dependent Pricing</strong> - The benefit cost and volume on each dependent record reflects the total cost for ALL dependents associated with the employee and are duplicated per-dependent.  The employee and spouse records are treated individually.
                </label>
            </div>
        </div>
    </div>
</div>

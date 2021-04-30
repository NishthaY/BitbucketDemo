<?php
    if ( ! isset($parents) ) $parents = array();
    if ( ! isset($lookup) ) $lookup = array();
    if ( ! isset($has_ssn) ) $has_ssn = "f";

?>
<?php
foreach( $parents as $parent )
{
    $parent_id = getArrayStringValue("LifeId", $parent);
    $employee_id = getArrayStringValue("EmployeeId", $parent);
    $parent['has_ssn'] = $has_ssn;

    print RenderViewAsString("lives/widget_start", $parent);
    if ( isset($lookup[$employee_id]))
    {
        // This is the list of options we pulled from the database.
        $options = $lookup[$employee_id];

        // Organize that list so that items that match the parents name
        // pop to the top.
        $options = SpecialSortUpdateRecords($options, $parent);

        // Render the options.
        foreach( $options as $child )
        {

            $child['parent_id'] = $parent_id;
            $child['parent_row'] = $parent;
            $child['has_ssn'] = $has_ssn;
            print RenderViewAsString("lives/widget_row", $child);
        }
    }
    print RenderViewAsString("lives/widget_end");
}

?>

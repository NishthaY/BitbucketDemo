<?php

    // Upload Widget
    $import_data_widget = new UIWidget("parent_import_data_widget");
    $import_data_widget->setBody( ParentImportDataWidget() );
    $import_data_widget->setHref(base_url("widgettask/parent/import_data_widget"));
    $import_data_widget = $import_data_widget->render();


?>
<?=$import_data_widget?>
<!-- <button id="sample2_btn" name="sample2_btn" class="pull-right btn w-lg btn-lg waves-effect waves-light btn-default" type="button" data-href="" formnovalidate >Not Started</button> -->
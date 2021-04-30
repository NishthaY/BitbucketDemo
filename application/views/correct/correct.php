<?php
    if ( ! isset($page_header) ) $page_header = "";
    if ( ! isset($form) ) $form = "";
    if ( ! isset( $headers ) ) $headers = array();
    if ( ! isset( $errors ) ) $errors = array();
    if ( ! isset( $widget ) ) $widget = "";
    if ( ! isset( $href ) ) $href = "";
    if ( ! isset( $identifier) ) $identifier = "";
    if ( ! isset( $identifier_type) ) $identifier_type = "";
?>

<div class="alert alert-success hidden" role="alert"><span class="alert-message"></span></div>
<?=$page_header?>
<div class="row">
    <div class="col-sm-12">
        <p class="text-muted page-title-alt">
            The file uploaded contains errors that need to be corrected before you may continue.<br>
            Please review the information below, take corrective action in your file and then upload the corrected file.<br>
            Click <a href="<?=base_url("download/errors/{$identifier_type}/{$identifier}")?>">here</a> to download information about the errors.
        </p>
    </div>
</div>
<?=$form?>
<div class="card-box table-responsive hidden" data-href="<?=$href?>" >
    <table id="corrections_table" class="table table-striped table-bordered hidden" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <?php
                if ( !empty($headers) )
                {
                    foreach($headers as $column_no=>$header)
                    {
                        ?><th><?=getArrayStringValue("column_display", $header);?></th><?php
                    }
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( !empty($errors) )
            {
                $keys = array_keys($errors);
                foreach($keys as $key)
                {
                    // Grab the error data.
                    $error = $errors[$key];

                    // Extract the data from this error.
                    if ( ! empty( $error["data"]) ) $data = $error["data"];
                    unset($error["data"]);

                    $invalid_columns = array_keys($error);

                    print "<tr><td class='sample-data-header'>row #{$key}</td>\n";
                    if ( ! empty($data) )
                    {
                        for($i=0;$i<count($data); $i++)
                        {
                            $cell_class = "";
                            $cell_icon = "";
                            $current_column = array();
                            if ( isset($headers[$i]) ) $current_column = $headers[$i];
                            if ( isset($headers[$i]) ) $current_column = $headers[$i];
                            $current_column = getArrayStringValue("column_name", $current_column);
                            if ( isset($error[$current_column]) ) {
                                $cell_class = "danger-cell";
                                $cell_icon = "<i class='danger-cell-icon ion-alert-circled'></i>";

                            }
                            if ( $current_column != "" ) {
                                $user_value = getArrayStringValue("{$i}", $data);
                                $user_value = MaskCustomerData($user_value);
                                ?><td data-row="<?=$key?>" data-column-name="<?=$current_column?>" class="<?=$cell_class?>"><?=$cell_icon?><?=$user_value?></td><?php
                            }
                        }
                    }
                    print "</tr>\n";
                }
            }
            ?>
        </tbody>
    </table>
</div>
<?=$widget?>

<?php
if ( ! isset($header_html) ) $header_html = "";
if ( ! isset($assignment_widget) ) $assignment_widget = "";
if ( ! isset($user) ) $user = array();

$first_name = GetArrayStringValue("first_name", $user);
$last_name = GetArrayStringValue("last_name", $user);
$company_name = GetArrayStringValue("company_parent_name", $user);
$email_address = GetArrayStringValue("email_address", $user);

?>
<?=$header_html?>
<div class="row">
    <div class="col-sm-9">
        <?=$assignment_widget?>
        <br>
    </div>
    <div class="col-sm-3">
        <?php
        if ( ! empty($user) )
        {
            ?>
            <div class="card-box table-responsive">
                <h4 class="m-t-0 header-title"><b><?=$first_name?> <?=$last_name?></b></h4>
                <p>
                    <?=$company_name?><br>
                    <?=$email_address?>
                </p>
            </div>
            <?php
        }
        ?>
    </div>
</div>

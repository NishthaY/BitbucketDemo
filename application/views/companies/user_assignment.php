<?php
    if ( ! isset($header_html) ) $header_html = "";
    if ( ! isset($inherited_users_html) ) $inherited_users_html = "";
    if ( ! isset($assignment_widget) ) $assignment_widget = "";
    if ( ! isset($all_users_html) ) $all_users_html = "";
    if ( ! isset($company_name) ) $company_name = "";
    if ( ! isset($address_line1) ) $company_name = "";
    if ( ! isset($city) ) $city = "";
    if ( ! isset($state) ) $state = "";
    if ( ! isset($zip) ) $zip = "";

?>
<?=$header_html?>
<div class="row">
    <div class="col-sm-9">
        <?=$assignment_widget?>
        <br>
        <?=$inherited_users_html?>
        <br>
        <?=$all_users_html?>

    </div>
    <div class="col-sm-3">
        <div class="card-box table-responsive">
            <h4 class="m-t-0 header-title"><b><?=$company_name?></b></h4>
            <p>
                <?=$address_line1?><br>
                <?=$city?> <?=$state?> <?=$zip?><br>
            </p>
        </div>
    </div>
</div>

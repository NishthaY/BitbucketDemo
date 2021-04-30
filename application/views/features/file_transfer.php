<?php
    if ( ! isset($code) ) $code = "";
    if ( ! isset($enabled) ) $enabled = false;
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($companyparent_id) ) $companyparent_id = "";
    if ( ! isset($parent_override) ) $parent_override = false;
    if ( ! isset($type) ) $type = "";


?>
<?php
    if ( $type === 'companyparent' )
    {
        ?>Transfer copies of finalized reports, for associated child companies, via SFTP to an <a data-widget-name="file_transfer_widget" data-form-name="file_transfer_form" class="feature-configuration-link">off-site location</a>.<?php
    }
    else
    {
        ?>Transfer copies of finalized reports via SFTP to an <a data-widget-name="file_transfer_widget" data-form-name="file_transfer_form" class="feature-configuration-link">off-site location</a>.<?php
    }
?>


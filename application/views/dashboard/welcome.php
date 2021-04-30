<?php
if ( ! isset($data) ) $data = array();

$this->load->helper("wizard");
$this->load->helper("dashboard");

$render_widget = true;
if ( IsAuthenticated() ) $render_widget = false;
if ( IsAuthenticated("parent_company_read") ) $render_widget = false;
if ( IsAuthenticated("parent_company_write") ) $render_widget = false;
if ( HasExistingReportData() ) $render_widget = false;

$step1_class = "hidden";
$step2_class = "hidden";
$step3_class = "hidden";
$step4_class = "hidden";
$step5_class = "hidden";

$step = "";
if ( empty($data) ) $step = "STEP1";
if ( $step == "" && getArrayStringValue("StartupComplete", $data) == "f" ) $step = "STEP1";
if ( $step == "" && getArrayStringValue("UploadComplete", $data) == "f" ) $step = "STEP2";
if ( $step == "" && getArrayStringValue("ParsingComplete", $data) == "f" ) $step = "STEP3";
if ( $step == "" && getArrayStringValue("MatchComplete", $data) == "f" ) $step = "STEP3";
if ( $step == "" && getArrayStringValue("ValidationComplete", $data) == "f" ) $step = "STEP3";
if ( $step == "" && getArrayStringValue("CorrectionComplete", $data) == "f" ) $step = "STEP3";
if ( $step == "" && getArrayStringValue("SavingComplete", $data) == "f" ) $step = "STEP3";
if ( $step == "" && getArrayStringValue("RelationshipComplete", $data) == "f" ) $step = "STEP4";
if ( $step == "" && getArrayStringValue("LivesComplete", $data) == "f" ) $step = "STEP4";
if ( $step == "" && getArrayStringValue("PlanReviewComplete", $data) == "f" ) $step = "STEP4";
if ( $step == "" && getArrayStringValue("AdjustmentsComplete", $data) == "f" ) $step = "STEP4";
if ( $step == "" && getArrayStringValue("ReportGenerationComplete", $data) == "f" ) $step = "STEP5";
if ( $step == "" && getArrayStringValue("Finalizing", $data) == "f" ) $redner_widget = false;


if ( $step == "STEP1" ) $step1_class = "";
if ( $step == "STEP2" ) $step2_class = "";
if ( $step == "STEP3" ) $step3_class = "";
if ( $step == "STEP4" ) $step4_class = "";
if ( $step == "STEP5" ) $step5_class = "";

?>
<?php
if ( $render_widget ) {
?>
    <div class="row">
        <div class="col-md-12 <?=$step1_class?>">
            <div class="widget-bg-color-icon card-box fadeInDown animated">
                <img class="img-responsive" src="<?=base_url();?>assets/custom/images/A2P-5-Step-Process-Step-1.jpg">
            </div>
        </div>
        <div class="col-md-12 <?=$step2_class?>">
            <div class="widget-bg-color-icon card-box fadeInDown animated">
                <img class="img-responsive" src="<?=base_url();?>assets/custom/images/A2P-5-Step-Process-Step-2.jpg">
            </div>
        </div>
        <div class="col-md-12 <?=$step3_class?>">
            <div class="widget-bg-color-icon card-box fadeInDown animated">
                <img class="img-responsive" src="<?=base_url();?>assets/custom/images/A2P-5-Step-Process-Step-3.jpg">
            </div>
        </div>
        <div class="col-md-12 <?=$step4_class?>">
            <div class="widget-bg-color-icon card-box fadeInDown animated">
                <img class="img-responsive" src="<?=base_url();?>assets/custom/images/A2P-5-Step-Process-Step-4.jpg">
            </div>
        </div>
        <div class="col-md-12 <?=$step5_class?>">
            <div class="widget-bg-color-icon card-box fadeInDown animated">
                <img class="img-responsive" src="<?=base_url();?>assets/custom/images/A2P-5-Step-Process-Step-5.jpg">
            </div>
        </div>
    </div>

<?php
}
?>

<?php
    if ( ! isset($company_id) ) $company_id = "";
    if ( ! isset($carrier_id) ) $carrier_id = "";
    if ( ! isset($carrier_display) ) $carrier_display = GetCompanyCarrierDescription($company_id, $carrier_id);
    if ( ! isset($amount_due) ) $amount_due = "";
    if ( ! isset($data) ) $data = array();
    if ( ! isset($pdf) ) $pdf = false;

?>
<html>
<head>
  <style>
@import 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700';
@import 'https://fonts.googleapis.com/css?family=Fira+Sans:400,700';
@import 'https://fonts.googleapis.com/css?family=Montserrat:400,700';
body {
  font-family: 'Tahoma', sans-serif;
}
td {
  font-size: <?=( !$pdf ? "12" : "7" )?>px;
  vertical-align: middle;
  padding: 3px;
}
table.report {
  border: 2px solid black;
  border-collapse: collapse;
  width: 100%;
}
.title {
  background-color: #e6e6e6;
  border-bottom: 1px solid black;
  font-size: <?=( !$pdf ? "18" : "10" )?>px;
  text-align: center;
  font-weight: bold;
}
tr.grouptitle {
  border-top: 1px solid black;
  font-weight: bold;
}
.grouptitle {
  background-color: #e6e6e6;
  text-align: center;
}
.grouptitle .benefits {
  border-right: 1px solid black;
  border-left: 2px solid black; /*bah*/
  font-size: <?=( !$pdf ? "14" : "8" )?>px;
  width: 31%;
}
.grouptitle .currentmonth {
  border-right: 1px solid black;
  font-size: <?=( !$pdf ? "14" : "8" )?>px;
  width: 23%;
}
.grouptitle .adjustments {
  border-right: 1px solid black;
  font-size: <?=( !$pdf ? "14" : "8" )?>px;
  width: 23%;
}
.grouptitle .adjustedtotal {
  font-size: <?=( !$pdf ? "14" : "8" )?>px;
  width: 23%;
}
.columntitle {
  background-color: #e6e6e6;
  text-align: left;
  font-weight: bold;
}
.columntitle td {

}
.strong-left-border {
  border-left: 2px solid black; /*bah*/
}
.strong-right-border {
  border-right: 2px solid black; /*bah*/
}
.strong-bottom-border {
  border-bottom: 2px solid black; /*bah*/
}
.strong-top-border {
  border-top: 2px solid black; /*bah*/
}
.left-border {
  border-left: 1px solid black; /*bah*/
}
.right-border {
  border-right: 1px solid black; /*bah*/
}
.bottom-border {
  border-bottom: 1px solid black; /*bah*/
}
.top-border {
  border-top: 1px solid black; /*bah*/
}
tr.columntitle {
  border-bottom: 2px solid black;
}
.attributes {
  border-right: 1px solid black;
}
.premium {
  border-right: 1px solid black;
}
.tiertotal {
  font-weight: bold;
}
.plantotal {
  font-weight: bold;
}
.plantypetotal {
  font-weight: bold;
  border-bottom: <?=( !$pdf ? "3px double" : "1px solid" )?> black;
}
.plantype {
  font-weight: bold;
}
.plantype, .plan, .tier {
  vertical-align: top;
}
.tiertotal .attributes, .tiertotal .lives, .tiertotal .volume, .tiertotal .premium  {
  border-bottom: 1px solid black;
}
.plantotal .attributes, .plantotal .lives, .plantotal .volume, .plantotal .premium  {
  border-bottom: 1px solid black;
}
.lives { width: 5%; }                                   /* bah: make count column a little smaller than the two money columns */
.volume { width: 9%; }                                  /* bah: make count column a little smaller than the two money columns */
.premium { width: 9%; border-right: 1px solid black;}   /* bah: make count column a little smaller than the two money columns */
.carriertotal {
  background-color: #e6e6e6;
}
.carriertotal td {
  font-size: <?=( !$pdf ? "14" : "8" )?>px;
  font-weight: bold;
}
.summarydata td {
  font-size: <?=( !$pdf ? "16" : "9" )?>px;
}

.carriertitle, .billingperiodtitle, .totalamountduetitle, .customertitle, .datepreparedtitle, .totalamountdue {
  font-weight: <?=( !$pdf ? "700" : "bold" )?>;
}
.totalamountdue, .totalamountduetitle {
  text-align: right;
  font-weight: <?=( !$pdf ? "700" : "bold" )?>;
}
.redBorder {
    border: 1px solid blue;
}
  </style>
</head>
<body>
    <table class="report" cellspacing="0" cellpadding="3">
        <tbody>
            <?=RenderViewAsString("reports/summary_report_title", array("company_id" => $company_id, "pagebreak" => false) );?>
            <?php
                $coveragetier_totals        = InitSummaryReportTotalsArray();
                $plan_totals                = InitSummaryReportTotalsArray();
                $plantype_totals            = InitSummaryReportTotalsArray();
                $final_totals               = InitSummaryReportTotalsArray();
                if ( ! empty($data) ) {

                    foreach($data as $section)
                    {
                        $previous_plantype          = "";
                        $previous_plan              = "";
                        $previous_coveragetier      = "";
                        foreach($section as $item ) {

                            $pagebreak = false;

                            // If the row has been marked as "hidden", just skip it.
                            if ( getArrayStringValue("HideRow", $item) == "t" ) continue;
                            $plantype = GetPlanTypeDescription($item);
                            $plan = getArrayStringValue("PlanDescription", $item);
                            $coveragetier = getArrayStringValue("CoverageTierDescription", $item);
                            $lives = getArrayStringValue("Lives", $item);
                            $volume = getArrayStringValue("Volume", $item);
                            $premium = getArrayStringValue("Premium", $item);
                            $adjusted_lives = getArrayStringValue("AdjustedLives", $item);
                            $adjusted_volume = getArrayStringValue("AdjustedVolume", $item);
                            $adjusted_premium = getArrayStringValue("AdjustedPremium", $item);
                            $total_lives = getArrayStringValue("TotalLives", $item);
                            $total_volume = getArrayStringValue("TotalVolume", $item);
                            $total_premium = getArrayStringValue("TotalPremium", $item);

                            // Draw our summary rows if needed.
                            $previous = $previous_plantype.$previous_plan.$previous_coveragetier;
                            $now = $plantype.$plan.$coveragetier;
                            if ( $previous_coveragetier != "" && $previous != "" && $previous != $now )
                            {
                                // Draw the coverage tier total row.
                                RenderViewSTDOUT( "reports/summary_report_coverage_tier_totals", $coveragetier_totals );
                                $coveragetier_totals = InitSummaryReportTotalsArray();
                                $previous_coveragetier = "";

                            }

                            $previous = $previous_plantype.$previous_plan;
                            $now = $plantype.$plan;
                            if ( $previous_plan != "" && $previous != "" && $previous != $now )
                            {
                                // Draw the plan total row.
                                RenderViewSTDOUT( "reports/summary_report_plan_totals", $plan_totals );
                                $plan_totals = InitSummaryReportTotalsArray();
                                $previous_plan = "";
                            }

                            $previous = $previous_plantype;
                            $now = $plantype;
                            if ( $previous_plantype != "" && $previous != "" && $previous != $now )
                            {
                                // Draw the plantype total row.
                                RenderViewSTDOUT( "reports/summary_report_plantype_totals", $plantype_totals );
                                $plantype_totals = InitSummaryReportTotalsArray();
                                $pagebreak = true;
                            }

                            $row = array();
                            $row['plantype'] = GetSummaryReportDescription($plantype, $previous_plantype);
                            $row['plan'] = GetSummaryReportDescription($plan, $previous_plan);
                            $row['tier'] = GetSummaryReportDescription($coveragetier, $previous_coveragetier);
                            $row['attributes'] = GetSummaryReportAttributeDescription($item);
                            $row['lives'] = GetReportNumberValue($lives);
                            $row['volume'] = GetReportMoneyValue($volume);
                            $row['premium'] = GetReportMoneyValue($premium);
                            $row['adjusted_lives'] = GetReportNumberValue($adjusted_lives);
                            $row['adjusted_volume'] = GetReportMoneyValue($adjusted_volume);
                            $row['adjusted_premium'] = GetReportMoneyValue($adjusted_premium);
                            //$row['total_lives'] = GetReportNumberValue($total_lives);
                            $row['total_lives'] = $row['lives'];
                            $row['total_volume'] = GetReportMoneyValue($total_volume);
                            $row['total_premium'] = GetReportMoneyValue($total_premium);
                            $row['page_break'] = $pagebreak;
                            $row['pdf'] = $pdf;
                            $row['company_id'] = $company_id;
                            RenderViewSTDOUT( "reports/summary_report_row", $row );

                            $coveragetier_totals['lives'] = $coveragetier_totals['lives'] + getIntValue($lives);
                            $coveragetier_totals['volume'] = $coveragetier_totals['volume'] + getFloatValue($volume);
                            $coveragetier_totals['premium'] = $coveragetier_totals['premium'] + getFloatValue($premium);
                            $coveragetier_totals['adjusted_lives'] = $coveragetier_totals['adjusted_lives'] + getIntValue($adjusted_lives);
                            $coveragetier_totals['adjusted_volume'] = $coveragetier_totals['adjusted_volume'] + getFloatValue($adjusted_volume);
                            $coveragetier_totals['adjusted_premium'] = $coveragetier_totals['adjusted_premium'] + getFloatValue($adjusted_premium);
                            $coveragetier_totals['total_lives'] = $coveragetier_totals['total_lives'] + getIntValue($lives);  // Note.  Not total_lives, but lives to make it match the first column.
                            $coveragetier_totals['total_volume'] = $coveragetier_totals['total_volume'] + getFloatValue($total_volume);
                            $coveragetier_totals['total_premium'] = $coveragetier_totals['total_premium'] + getFloatValue($total_premium);
                            if ( $previous_coveragetier == "" ) $coveragetier_totals['count'] = 1;
                            if ( $previous_coveragetier != "" ) $coveragetier_totals['count'] = $coveragetier_totals['count'] + 1;

                            $plan_totals['lives'] = $plan_totals['lives'] + getIntValue($lives);
                            $plan_totals['volume'] = $plan_totals['volume'] + getFloatValue($volume);
                            $plan_totals['premium'] = $plan_totals['premium'] + getFloatValue($premium);
                            $plan_totals['adjusted_lives'] = $plan_totals['adjusted_lives'] + getIntValue($adjusted_lives);
                            $plan_totals['adjusted_volume'] = $plan_totals['adjusted_volume'] + getFloatValue($adjusted_volume);
                            $plan_totals['adjusted_premium'] = $plan_totals['adjusted_premium'] + getFloatValue($adjusted_premium);
                            $plan_totals['total_lives'] = $plan_totals['total_lives'] + getIntValue($lives);  // Note.  Not total_lives, but lives to make it match the first column.
                            $plan_totals['total_volume'] = $plan_totals['total_volume'] + getFloatValue($total_volume);
                            $plan_totals['total_premium'] = $plan_totals['total_premium'] + getFloatValue($total_premium);

                            $plantype_totals['lives'] = $plantype_totals['lives'] + getIntValue($lives);
                            $plantype_totals['volume'] = $plantype_totals['volume'] + getFloatValue($volume);
                            $plantype_totals['premium'] = $plantype_totals['premium'] + getFloatValue($premium);
                            $plantype_totals['adjusted_lives'] = $plantype_totals['adjusted_lives'] + getIntValue($adjusted_lives);
                            $plantype_totals['adjusted_volume'] = $plantype_totals['adjusted_volume'] + getFloatValue($adjusted_volume);
                            $plantype_totals['adjusted_premium'] = $plantype_totals['adjusted_premium'] + getFloatValue($adjusted_premium);
                            $plantype_totals['total_lives'] = $plantype_totals['total_lives'] + getIntValue($lives);  // Note.  Not total_lives, but lives to make it match the first column.
                            $plantype_totals['total_volume'] = $plantype_totals['total_volume'] + getFloatValue($total_volume);
                            $plantype_totals['total_premium'] = $plantype_totals['total_premium'] + getFloatValue($total_premium);

                            $final_totals['lives'] = $final_totals['lives'] + getIntValue($lives);
                            $final_totals['volume'] = $final_totals['volume'] + getFloatValue($volume);
                            $final_totals['premium'] = $final_totals['premium'] + getFloatValue($premium);
                            $final_totals['adjusted_lives'] = $final_totals['adjusted_lives'] + getIntValue($adjusted_lives);
                            $final_totals['adjusted_volume'] = $final_totals['adjusted_volume'] + getFloatValue($adjusted_volume);
                            $final_totals['adjusted_premium'] = $final_totals['adjusted_premium'] + getFloatValue($adjusted_premium);
                            $final_totals['total_lives'] = $final_totals['total_lives'] + getIntValue($lives);  // Note.  Not total_lives, but lives to make it match the first column.
                            $final_totals['total_volume'] = $final_totals['total_volume'] + getFloatValue($total_volume);
                            $final_totals['total_premium'] = $final_totals['total_premium'] + getFloatValue($total_premium);


                            $previous_plantype = $plantype;
                            $previous_plan = $plan;
                            $previous_coveragetier = $coveragetier;

                            $previous_item = $item;
                        }
                    }
                }

                // Add final grouping totals.
                RenderViewSTDOUT( "reports/summary_report_coverage_tier_totals", $coveragetier_totals );
                RenderViewSTDOUT( "reports/summary_report_plan_totals", $plan_totals );
                RenderViewSTDOUT( "reports/summary_report_plantype_totals", $plantype_totals );

            ?>

            <tr class="carriertotal">
                <td class="plantype strong-left-border strong-bottom-border"></td>
                <td class="plan strong-bottom-border"></td>
                <td class="attributes strong-bottom-border" colspan="2">Carrier Total</td>
                <td class="lives strong-bottom-border"><?=GetReportNumberValue($final_totals['lives'])?></td>
                <td class="volume strong-bottom-border"><?=GetReportMoneyValue($final_totals['volume']);?></td>
                <td class="premium strong-bottom-border"><?=GetReportMoneyValue($final_totals['premium']);?></td>
                <td class="lives strong-bottom-border"><?=GetReportNumberValue($final_totals['adjusted_lives'])?></td>
                <td class="volume strong-bottom-border"><?=GetReportMoneyValue($final_totals['adjusted_volume']);?></td>
                <td class="premium strong-bottom-border"><?=GetReportMoneyValue($final_totals['adjusted_premium']);?></td>
                <td class="lives strong-bottom-border"><?=GetReportNumberValue($final_totals['total_lives'])?></td>
                <td class="volume strong-bottom-border"><?=GetReportMoneyValue($final_totals['total_volume']);?></td>
                <td class="premium strong-right-border strong-bottom-border"><?=GetReportMoneyValue($final_totals['total_premium']);?></td>
            </tr>
</tbody>
</table>
</body>
</html>

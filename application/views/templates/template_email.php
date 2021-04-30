<?php
    if ( !isset($email_images_url) ) $email_images_url = "https://www.advice2pay.com/assets/custom/images/email";
    if ( !isset($salutation) ) $salutation = "Hello,";
    if ( !isset($message) ) $message = "Advice2Pay thanks you for your business.";
    if ( !isset($title) ) $title = "[Advice2Pay] Email Notification";
    if ( !isset($hostname) ) $hostname = "www.advice2pay.com";
    if ( !isset($icon_image) ) $icon_image = "";
    if ( !isset($button_label) ) $button_label = "Return to Site";

    // Ug.  I have links that go to the hostname, but I don't know what the
    // protocol is.  Someday it will be https always, but until then I have to guess.
    $base_url = fLeft($email_images_url, "://") . "://" . $hostname;

    $icon_style = "";
    if ( getStringValue($icon_image) == "" ) $icon_style = " display:none; ";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><!--[if IE]><html xmlns="http://www.w3.org/1999/xhtml" class="ie-browser" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"><![endif]--><!--[if !IE]><!--><html style="margin: 0;padding: 0;" xmlns="http://www.w3.org/1999/xhtml"><!--<![endif]--><head>
    <!--[if gte mso 9]><xml>
     <o:OfficeDocumentSettings>
      <o:AllowPNG/>
      <o:PixelsPerInch>96</o:PixelsPerInch>
     </o:OfficeDocumentSettings>
    </xml><![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">
    <!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]-->
    <title><?=$title?></title>
    <!--[if !mso]><!-- -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet" type="text/css">
	<!--<![endif]-->

    <style type="text/css" id="media-query">
      body {
  margin: 0;
  padding: 0; }

table {
  border-collapse: collapse;
  table-layout: fixed; }

* {
  line-height: inherit; }

a[x-apple-data-detectors=true] {
  color: inherit !important;
  text-decoration: none !important; }

[owa] .img-container div, [owa] .img-container button {
  display: block !important; }

[owa] .fullwidth button {
  width: 100% !important; }

.ie-browser .col, [owa] .block-grid .col {
  display: table-cell;
  float: none !important;
  vertical-align: top; }

.ie-browser .num12, .ie-browser .block-grid, [owa] .num12, [owa] .block-grid {
  width: 500px !important; }

.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
  line-height: 100%; }

.ie-browser .mixed-two-up .num4, [owa] .mixed-two-up .num4 {
  width: 164px !important; }

.ie-browser .mixed-two-up .num8, [owa] .mixed-two-up .num8 {
  width: 328px !important; }

.ie-browser .block-grid.two-up .col, [owa] .block-grid.two-up .col {
  width: 250px !important; }

.ie-browser .block-grid.three-up .col, [owa] .block-grid.three-up .col {
  width: 166px !important; }

.ie-browser .block-grid.four-up .col, [owa] .block-grid.four-up .col {
  width: 125px !important; }

.ie-browser .block-grid.five-up .col, [owa] .block-grid.five-up .col {
  width: 100px !important; }

.ie-browser .block-grid.six-up .col, [owa] .block-grid.six-up .col {
  width: 83px !important; }

.ie-browser .block-grid.seven-up .col, [owa] .block-grid.seven-up .col {
  width: 71px !important; }

.ie-browser .block-grid.eight-up .col, [owa] .block-grid.eight-up .col {
  width: 62px !important; }

.ie-browser .block-grid.nine-up .col, [owa] .block-grid.nine-up .col {
  width: 55px !important; }

.ie-browser .block-grid.ten-up .col, [owa] .block-grid.ten-up .col {
  width: 50px !important; }

.ie-browser .block-grid.eleven-up .col, [owa] .block-grid.eleven-up .col {
  width: 45px !important; }

.ie-browser .block-grid.twelve-up .col, [owa] .block-grid.twelve-up .col {
  width: 41px !important; }

@media only screen and (min-width: 520px) {
  .block-grid {
    width: 500px !important; }
  .block-grid .col {
    display: table-cell;
    Float: none !important;
    vertical-align: top; }
    .block-grid .col.num12 {
      width: 500px !important; }
  .block-grid.mixed-two-up .col.num4 {
    width: 164px !important; }
  .block-grid.mixed-two-up .col.num8 {
    width: 328px !important; }
  .block-grid.two-up .col {
    width: 250px !important; }
  .block-grid.three-up .col {
    width: 166px !important; }
  .block-grid.four-up .col {
    width: 125px !important; }
  .block-grid.five-up .col {
    width: 100px !important; }
  .block-grid.six-up .col {
    width: 83px !important; }
  .block-grid.seven-up .col {
    width: 71px !important; }
  .block-grid.eight-up .col {
    width: 62px !important; }
  .block-grid.nine-up .col {
    width: 55px !important; }
  .block-grid.ten-up .col {
    width: 50px !important; }
  .block-grid.eleven-up .col {
    width: 45px !important; }
  .block-grid.twelve-up .col {
    width: 41px !important; } }

@media (max-width: 520px) {
  .block-grid, .col {
    min-width: 320px !important;
    max-width: 100% !important; }
  .block-grid {
    width: calc(100% - 40px) !important; }
  .col {
    width: 100% !important; }
    .col > div {
      margin: 0 auto; }
  img.fullwidth {
    max-width: 100% !important; } }

    </style>
</head>
<!--[if mso]>
<body class="mso-container" style="background-color:#FFFFFF;">
<![endif]-->
<!--[if !mso]><!-->
<body class="clean-body" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #FFFFFF">
<!--<![endif]-->
  <div class="nl-container" style="min-width: 320px;Margin: 0 auto;background-color: #FFFFFF">
    <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="background-color: #FFFFFF;"><![endif]-->

    <div style="background-color:transparent;">
      <div style="Margin: 0 auto;min-width: 320px;max-width: 500px;width: 500px;width: calc(19000% - 98300px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;" class="block-grid ">
        <div style="border-collapse: collapse;display: table;width: 100%;">
          <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="background-color:transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width: 500px;"><tr class="layout-full-width" style="background-color:transparent;"><![endif]-->

              <!--[if (mso)|(IE)]><td align="center" width="500" style=" width:500px; padding-right: 0px; padding-left: 0px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><![endif]-->
            <div class="col num12" style="min-width: 320px;max-width: 500px;width: 500px;width: calc(18000% - 89500px);background-color: transparent;">
               <!--[if (!mso)&(!IE)]><!--><div style="border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"><!--<![endif]-->
                <div style="background-color: transparent;">
                  <div style="line-height: 5px; font-size:1px">&nbsp;</div>


                  <div style="width: 100% !important;">
<div align="center" class="img-container center">
<!--[if !mso]><!--><div style="Margin-right: 0px;Margin-left: 0px;"><!--<![endif]-->
  <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right:  0px; padding-left: 0px;" align="center"><![endif]-->
  <a href="<?=$base_url?>" target="_blank">
    <img class="center" align="center" border="0" src="<?=$email_images_url?>/advice2pay-logo-sm.png" alt="Image" title="Image" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block;border: none;height: auto;float: none;width: 100%;max-width: 175px" width="175">
  </a>


  <!--[if mso]></td></tr></table><![endif]-->
<!--[if !mso]><!--></div><!--<![endif]-->
</div>
                  </div>

                                  <div style="line-height: 5px; font-size: 1px">&nbsp;</div>
              </div>
              <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
            </div>
          <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
        </div>
      </div>
    </div>    <div style="background-color:#5D9CEC;">
      <div style="Margin: 0 auto;min-width: 320px;max-width: 500px;width: 500px;width: calc(19000% - 98300px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;" class="block-grid ">
        <div style="border-collapse: collapse;display: table;width: 100%;">
          <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="background-color:#5D9CEC;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width: 500px;"><tr class="layout-full-width" style="background-color:transparent;"><![endif]-->

              <!--[if (mso)|(IE)]><td align="center" width="500" style=" width:500px; padding-right: 0px; padding-left: 0px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><![endif]-->
            <div class="col num12" style="min-width: 320px;max-width: 500px;width: 500px;width: calc(18000% - 89500px);background-color: transparent;">
               <!--[if (!mso)&(!IE)]><!--><div style="border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"><!--<![endif]-->
                <div style="background-color: transparent;">


                  <div style="width: 100% !important;">
<!--[if !mso]><!--><div align="center" style="Margin-right: 10px;Margin-left: 10px;"><!--<![endif]-->
  <div style="line-height: 10px; font-size:1px">&nbsp;</div>
  <!--[if (mso)|(IE)]><table width="100%" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px;padding-left: 10px;"><![endif]-->
  <div style="border-top: 10px solid transparent; width:100%; font-size:1px;">&nbsp;</div>
  <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
  <div style="line-height:10px; font-size:1px">&nbsp;</div>
<!--[if !mso]><!--></div><!--<![endif]-->
                  </div>


                  <div style="width: 100% !important;">
<!--[if !mso]><!--><div style="Margin-right: 0px; Margin-left: 0px;"><!--<![endif]-->
  <div style="line-height: 30px; font-size: 1px">&nbsp;</div>
  <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px;"><![endif]-->

	<div style="font-size:12px;line-height:14px;font-family:'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;color:#ffffff;text-align:left;"><p style="margin: 0;font-size: 14px;line-height: 17px;text-align: center"><strong><span style="font-size: 28px; line-height: 33px;"><?=replaceFor($title, "[Advice2Pay] ", "")?></span></strong></p></div>

  <!--[if mso]></td></tr></table><![endif]-->

  <div style="line-height: 30px; font-size: 1px">&nbsp;</div>
<!--[if !mso]><!--></div><!--<![endif]-->
                  </div>


                  <div style="width: 100% !important; <?=$icon_style?>">
<div align="center" class="img-container center">
<!--[if !mso]><!--><div style="Margin-right: 0px;Margin-left: 0px;"><!--<![endif]-->
  <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right:  0px; padding-left: 0px;" align="center"><![endif]-->
  <img class="center" align="center" border="0" src="<?=$email_images_url?>/<?=$icon_image?>" alt="Image" title="Image" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block;border: none;height: auto;float: none;width: 100%;max-width: 115px" width="115">


  <!--[if mso]></td></tr></table><![endif]-->
<!--[if !mso]><!--></div><!--<![endif]-->
</div>
                  </div>


                  <div style="width: 100% !important;">
<!--[if !mso]><!--><div align="center" style="Margin-right: 10px;Margin-left: 10px;"><!--<![endif]-->
  <div style="line-height: 10px; font-size:1px">&nbsp;</div>
  <!--[if (mso)|(IE)]><table width="100%" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px;padding-left: 10px;"><![endif]-->
  <div style="border-top: 10px solid transparent; width:100%; font-size:1px;">&nbsp;</div>
  <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
  <div style="line-height:10px; font-size:1px">&nbsp;</div>
<!--[if !mso]><!--></div><!--<![endif]-->
                  </div>


                  <div style="width: 100% !important;">
<!--[if !mso]><!--><div style="Margin-right: 10px; Margin-left: 10px;"><!--<![endif]-->
  <div style="line-height: 10px; font-size: 1px">&nbsp;</div>
  <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px;"><![endif]-->

	<div style="font-size:12px;line-height:14px;font-family:'Source Sans Pro', Tahoma, Verdana, Segoe, sans-serif;color:#FFF;text-align:left;"><p style="margin: 0;font-size: 12px;line-height: 14px"><span style="font-size: 18px; line-height: 21px;"><?=$salutation?>,</span></p></div>

  <!--[if mso]></td></tr></table><![endif]-->

  <div style="line-height: 10px; font-size: 1px">&nbsp;</div>
<!--[if !mso]><!--></div><!--<![endif]-->
                  </div>


                  <div style="width: 100% !important;">
<!--[if !mso]><!--><div style="Margin-right: 10px; Margin-left: 10px;"><!--<![endif]-->
  <div style="line-height: 10px; font-size: 1px">&nbsp;</div>
  <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px;"><![endif]-->

	<div style="font-size:12px;line-height:14px;font-family:'Source Sans Pro', Tahoma, Verdana, Segoe, sans-serif;color:#FFF;text-align:left;"><p style="margin: 0;font-size: 12px;line-height: 14px"><span style="font-size: 18px; line-height: 21px;"><?=$message?></span></p></div>

  <!--[if mso]></td></tr></table><![endif]-->

  <div style="line-height: 10px; font-size: 1px">&nbsp;</div>
<!--[if !mso]><!--></div><!--<![endif]-->
                  </div>


                  <div style="width: 100% !important;">

<div align="center" class="button-container center" style="Margin-right: 10px;Margin-left: 10px;">
    <div style="line-height:10px;font-size:1px">&nbsp;</div>
  <a href="<?=$base_url?>" target="_blank" style="color: #ffffff; text-decoration: none;">
    <!--[if mso]>
      <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="https://<?=$base_url?>" style="height:54px; v-text-anchor:middle; width:212px;" arcsize="8%" strokecolor="#23408F" fillcolor="#23408F" >
      <w:anchorlock/><center style="color:#ffffff; font-family:'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:22px;">
    <![endif]-->
    <!--[if !mso]><!--><div style="color: #ffffff; background-color: #23408F; border-radius: 4px; -webkit-border-radius: 4px; -moz-border-radius: 4px; max-width: 192px; width: auto; border-top: 0px solid transparent; border-right: 0px solid transparent; border-bottom: 0px solid transparent; border-left: 0px solid transparent; padding-top: 5px; padding-right: 20px; padding-bottom: 5px; padding-left: 20px; font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; text-align: center;"><!--<![endif]-->
      <span style="font-size:12px;line-height:24px;"><span style="font-size: 22px; line-height: 44px;" data-mce-style="font-size: 22px; line-height: 28px;"><strong><?=$button_label?></strong></span></span>
    <!--[if !mso]><!--></div><!--<![endif]-->
    <!--[if mso]>
          </center>
      </v:roundrect>
    <![endif]-->
  </a>
    <div style="line-height:10px;font-size:1px">&nbsp;</div>
</div>
                  </div>


                  <div style="width: 100% !important;">
<!--[if !mso]><!--><div style="Margin-right: 10px; Margin-left: 10px;"><!--<![endif]-->
  <div style="line-height: 10px; font-size: 1px">&nbsp;</div>
  <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px;"><![endif]-->

	<div style="font-size:12px;line-height:14px;font-family:'Source Sans Pro', Tahoma, Verdana, Segoe, sans-serif;color:#FFF;text-align:left;"><p style="margin: 0;font-size: 12px;line-height: 14px"><span style="font-size: 18px; line-height: 21px;">Thank you!</span></p></div>

  <!--[if mso]></td></tr></table><![endif]-->

  <div style="line-height: 10px; font-size: 1px">&nbsp;</div>
<!--[if !mso]><!--></div><!--<![endif]-->
                  </div>


                  <div style="width: 100% !important;">
<div align="center" class="img-container center">
<!--[if !mso]><!--><div style="Margin-right: 0px;Margin-left: 0px;"><!--<![endif]-->
  <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right:  0px; padding-left: 0px;" align="center"><![endif]-->

  <a href="<?=$base_url?>" target="_blank">
  <img class="center" align="center" border="0" src="<?=$email_images_url?>/advice2pay-logo-sm-white.png" alt="Image" title="Image" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block;border: 0;height: auto;float: none;width: 100%;max-width: 180px" width="180">
</a>
  <!--[if mso]></td></tr></table><![endif]-->
<!--[if !mso]><!--></div><!--<![endif]-->
</div>
                  </div>


                  <div style="width: 100% !important;">
<!--[if !mso]><!--><div align="center" style="Margin-right: 10px;Margin-left: 10px;"><!--<![endif]-->
  <div style="line-height: 10px; font-size:1px">&nbsp;</div>
  <!--[if (mso)|(IE)]><table width="100%" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px;padding-left: 10px;"><![endif]-->
  <div style="border-top: 10px solid transparent; width:100%; font-size:1px;">&nbsp;</div>
  <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
  <div style="line-height:10px; font-size:1px">&nbsp;</div>
<!--[if !mso]><!--></div><!--<![endif]-->
                  </div>

                              </div>
              <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
            </div>
          <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
        </div>
      </div>
    </div>   <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
  </div>


</body></html>

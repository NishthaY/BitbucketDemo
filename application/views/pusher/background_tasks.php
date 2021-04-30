<?php

// This view will write "embedded" pusher javascript code
// that will automatically subscribe the person rendering
// the page to their companies private channel.

// Furthermore, we will listen for any events that match the
// background tasks defined on the page being rendered.

// If we can't find a company_id, then they are not logged in
// and we will not embed any JS.
$company_id = GetSessionValue("company_id");
$companyparent_id = GetSessionValue("companyparent_id");

$enabled = true;
if ( GetPusherAPIKey() === '' ) $enabled = false;
if ( GetStringValue($company_id) === '' ) $enabled = false;

// Allow an app option to override our settings.  This way we can
// have pusher installed on an application, but turn it off without
// needing to remove configuration settings from the environment.
$app_option = strtoupper(GetAppOption("PUSHER_ENABLED"));
if ( $app_option === 'FALSE' ) $enabled = false;
if ( $app_option === 'TRUE' ) $enabled = true;

// Construct the channel name.  There are two possible channels.  The company channel or the
// companyparent channel.  When a company notification is set, it is set to the company and the parent.
// you only need to watch for the one that cooresponds to your session.
$channel_name = "private-".APP_NAME."-company-" . $company_id;
if ( $companyparent_id !== '' ) $channel_name = "private-".APP_NAME."-companyparent-" . $companyparent_id;

if ( $enabled )
{
    // If we are enabled, then we have a PUSHER app key and a
    // company id.  That is enough to use PUSHER to send messages
    // from the backend to the front end.  Use that.

    ?>
    <!-- Pusher JS Library -->
    <script type="text/javascript" src="<?=base_url()?>assets/custom/js/pusher<?=vPusher()?>/pusher.min.js<?=CachedQS()?>"></script>
    <script  type="text/javascript">
        jQuery(document).ready(function($)
        {
            var pusher = new Pusher('<?=GetPusherAPIKey()?>', {
                cluster: '<?=GetPusherAPICluster()?>',
                encrypted: true,
                authEndpoint: '/auth/pusher'
            });

            var channel_name = '<?=$channel_name?>';
            var channel = pusher.subscribe(channel_name);

            if ( debug_event ) console.log("Pusher: Subscribing to channel: " + channel_name);

            // Anytime we get a workflow_step_starting event, push the user to the dashboard,
            // if they are not already on it.
            if ( debug_event ) console.log("Pusher: Listening for [workflow_step_starting] events.");
            channel.bind('workflow_step_starting', function(data){
                workflow_step_starting(data);
            });

            // workflow_step_changed
            // Event is fired when a user starts the review process for a wizard step.
            if ( debug_event ) console.log("Pusher: Listening for [workflow_step_changed] events.");
            channel.bind('workflow_step_changed', function(data){
                workflow_step_changed(data);
            });

            // rollback_event
            // Event is fired when a user starts the review process for a wizard step.
            if ( debug_event ) console.log("Pusher: Listening for [workflow_step_changing] events.");
            channel.bind('workflow_step_changing', function(data){
                workflow_step_changing(data);
            });

            if ( debug_event ) console.log("Pusher: Listening for [workflow_complete] events.");
            channel.bind('workflow_complete', function(data){
                workflow_complete(data);
            });

            if ( debug_event ) console.log("Pusher: Listening for [workflow_widget_refresh] events.");
            channel.bind('workflow_widget_refresh', function(data){
                workflow_widget_refresh(data);
            });

            $(".background-task").each(function()
            {
                var task_name = $(this).attr("id");
                if ( debug_event ) console.log("Pusher: Listening for ["+task_name+"] events.");
                channel.bind(task_name, function (data)
                {
                    <!-- Register for refresh notifications for any background tasks on this page. -->
                    //startWidgets(task_name);
                    backgroundTaskNotificationHandler( task_name, data);
                });
                if ( debug_event ) console.log("Pusher: Listening for ["+task_name+"-update] events.");
                channel.bind(task_name + "-update", function (data)
                {
                    <!-- Register for update notifications for any background tasks on this page. -->
                    backgroundTaskUpdateNotificationHandler( task_name, data );
                });
            });
        });
    </script>


    <?php
}
else
{

    // Okay, we could not use PUSHER on this application, so let's fall
    // back to the polling background task method.

    ?>

    <script  type="text/javascript">
        jQuery(document).ready(function($)
        {
            // Start background tasks.
            var delay_ms = 1000 * 60;  // Wait 60 seconds before we start our background tasks.
            setTimeout(function () {
                executeFunctionByName("startBackgroundTasks", window);
            }, delay_ms);
        });
    </script>
    <?php
}
?>



<?php
    if ( ! isset($data) ) $data = array();
?>
<div class="card-box table-responsive hidden">
    <a id="refresh_dyno_list" href="http://dev.advice2pay.com/support/start/restore" class="btn btn-sm btn-white pull-right">Refresh</a>
    <h4 class="m-t-0 header-title"><b>Heroku Dynos</b></h4>
    <p class="text-muted">All dynos currently in operation on this heroku instance.</p>
    <table id="active_dynos" class="table table-hover m-0" >
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Revision</th>
                <th>Started</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if ( !empty($data) ) {

                    foreach($data as $item) {

                        if ( getArrayStringValue("Type", $item) == "web" || getArrayStringValue("Type", $item) == "worker" )
                        {
                            $confirm_btn = new UIConfirmButton();
                            $confirm_btn->setLabel("Restart Dyno");
                            $confirm_btn->setHref(base_url() . "support/dyno/reset/" . getArrayStringValue("Name", $item));
                            $confirm_btn->setCallback("RefreshDynoWidget");
                            $confirm_btn = $confirm_btn->render();
                        }
                        else
                        {
                            $confirm_btn = new UIConfirmButton();
                            $confirm_btn->setLabel("Kill Dyno");
                            $confirm_btn->setHref(base_url() . "support/dyno/stop/" . getArrayStringValue("Name", $item));
                            $confirm_btn->setCallback("RefreshDynoWidget");
                            $confirm_btn = $confirm_btn->render();

                        }

                        $name = getArrayStringValue("Name", $item);
                        if ( getArrayStringValue("Type", $item) == "run" ) {
                            $name = "<a class='dyno-details' href='".base_url() . "support/dyno/details/{$name}"."'>{$name}</a>";
                        }

                        $view_array = array();
                        $view_array = array_merge($view_array, array("name" => $name));
                        $view_array = array_merge($view_array, array("type" => getArrayStringValue("Type", $item)));
                        $view_array = array_merge($view_array, array("state" => getArrayStringValue("State", $item)));
                        $view_array = array_merge($view_array, array("size" => getArrayStringValue("Size", $item)));
                        $view_array = array_merge($view_array, array("revision" => getArrayStringValue("Revision", $item)));
                        $view_array = array_merge($view_array, array("updated" => getArrayStringValue("Updated", $item)));
                        $view_array = array_merge($view_array, array("confirm_btn" => $confirm_btn));
                        RenderViewSTDOUT("support/dynos_widget_row", $view_array);
                    }
                }
            ?>
        </tbody>
    </table>
</div>

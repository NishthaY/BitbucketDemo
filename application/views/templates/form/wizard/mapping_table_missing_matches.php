<?php
    if ( ! isset($required_list) ) $required_list = "";
    if ( ! isset($conditional_list) ) $conditional_list = "";
?>
<div id="missing_matches_container" class="alert alert-wizard hidden" role="alert">
    <span class="alert-message">
        <div>
            <span id="required_list" class="hidden"><?=$required_list?></span>
            <span id="conditional_list" class="hidden"><?=$conditional_list?></span>
            <h4 class="page-title">We Do Not See Matches For:</h4>
            <div class="row">
                <div class="col-sm-12">
                    <p class="">
                        The following columns must be matched before you can continue.
                        <ul>
                            <li id="missing_matches_error"></li>
                        </ul>
                    </p>
                </div>
            </div>
        </div>
    </span>
</div>

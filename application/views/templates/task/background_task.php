<?php
	if ( ! isset($name) ) $name = "";
	if ( ! isset($href) ) $href = "";
	if ( ! isset($refresh_minutes) ) $refresh_minutes = "";
	if ( ! isset($debug) ) $debug = 0;
	if ( ! isset($info) ) $info = 0;
?>
<div id="<?=$name?>" class='background-task hidden' data-href="<?=$href?>" data-refresh-minutes="<?=$refresh_minutes?>" data-debug=<?=$debug?> data-info=<?=$info?> ></div>

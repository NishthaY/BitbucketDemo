<?php
	if ( ! isset ( $name) ) $name = "";
	if ( ! isset ( $body) ) $body = "";
	if ( ! isset ( $href) ) $href = "";
	if ( ! isset ( $callback) ) $callback = "";
	if ( ! isset ( $starting) ) $starting = "";
	if ( ! isset ( $inline_flg ) ) $inline_flg = false;
	if ( ! isset ( $task_name) ) $task_name = "";

	// Sometimes we want a widget to float inline other times we want it
	// to fill it's content area.  Do this by making the widget out of div vs. span
	// tags.
	$tag = "div";
	if ( $inline_flg ) $tag = "span";

?>
<<?=$tag?> id="<?=$name?>" class="widget" data-href="<?=$href?>" data-callback="<?=$callback?>" data-starting="<?=$starting?>" data-background-task="<?=$task_name?>" data-uri="<?=uri_string()?>" >
	<<?=$tag?> id="<?=$name?>_widget_wrapper">
		<?=$body?>
	</<?=$tag?>>
</<?=$tag?>>

<?php
	/**
	 * Loop variables
	 */
	$keys = $admin->feed->get_fields();
?>
<div class="recright-feed-variables">
	<h4><?= __( 'Click on any of the following to insert variable to template:', $admin->feed->name ) ?></h4>
	<?php
	foreach ($keys as $key) {
		?><button type="button" data-name="<?= esc_attr($key) ?>"><?= esc_html($key) ?></button><?php
	}
	?>
</div>

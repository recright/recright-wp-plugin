<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.recright.com/
 * @since      1.0.0
 *
 * @package    Recright_Feed
 * @subpackage Recright_Feed/admin/partials
 */
?>
<div class="recright-feed-admin wrap" id="recright-feed-admin">
  <h2><?php _e( 'RecRight Jobs Settings', $admin->feed->name ); ?></h2>
  <?php 
  settings_errors();
  $active = isset($_GET['tab']) ? $_GET['tab'] : 'general';
  if ( ! in_array($active, ['general', 'advanced', 'logs']) ) {
    $active = 'general';
  }
  ?>
  <h2 class="nav-tab-wrapper">
    <a href="?page=recright_feed&tab=general" class="nav-tab <?php echo $active === 'general' ? 'nav-tab-active' : ''; ?>"><?php _e( 'General', $admin->feed->name ); ?></a>
    <a href="?page=recright_feed&tab=advanced" class="nav-tab <?php echo $active === 'advanced' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Advanced', $admin->feed->name ); ?></a>
    <a href="?page=recright_feed&tab=logs" class="nav-tab <?php echo $active === 'logs' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Logs', $admin->feed->name ); ?></a>
  </h2>
  <?php
  if ( $active === 'logs') {
  ?>
  <div class="recright-feed-log">
    <h2>Feed Logs <a href="?page=recright_feed&tab=logs&clear=feed">[clear]</a></h2>
    <textarea readonly><?= esc_html( $admin->feed->get_feed_logs() ) ?></textarea>
  </div>
  <div class="recright-feed-log">
    <h2>Error Logs <a href="?page=recright_feed&tab=logs&clear=error">[clear]</a></h2>
    <textarea readonly><?= esc_html( $admin->feed->get_error_logs() ) ?></textarea>
  </div>
  <?php
  } else {
  ?>
  <form method="post" action="options.php">
    <?php
    settings_fields( 'recright_feed_' . $active . '_settings' );
    do_settings_sections( 'recright_feed_' . $active . '_settings' );
    if ( $active === 'general' ) {
    ?>
    <div class="recright-feed-instructions">
      <h2>Usage</h2>
      <p>
        To display the feed on any post or page, simply insert the <code>[<?= $admin->feed->get_setting('shortcode_tag', 'advanced') ?>]</code> shortcode anywhere in any content.<br />
        You can also insert the shortcode in a Text or Custom HTML Widget. <a href="https://codex.wordpress.org/Shortcode" target="_blank">Click here</a> to learn more about shortcodes.
      </p>
    </div>
    <?php
    }
    submit_button();
    ?>
  </form>
  <?php
  }
  ?>
</div>
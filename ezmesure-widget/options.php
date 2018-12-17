<?php
add_action('admin_menu', 'ezMESURE_Menu');

function ezMESURE_Menu() {
  // Create entry in settings menu
  add_options_page('ezMESURE', 'ezMESURE', 'manage_options', 'ezmesure-settings', 'ezmesure_page_builder');
}

// Render the options page
function ezmesure_page_builder() {
  // Ensure the user has manage_options level access
  if (!current_user_can('manage_options')) {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }

  // Check for form posting and update options
  if (isset($_POST['ezmesure_token'])) {
    update_option('ezmesure_token', $_POST['ezmesure_token']);
    update_option('ezmesure_url', $_POST['ezmesure_url']);

    ?>
    <div id="message" class="updated fade">
      <p><strong><?php _e('Saved'); ?></strong></p>
    </div>
    <?php
   }

  ?>
  <div class="wrap">
    <h2><?php _e('ezMESURE authentication', 'ezmesure-widget'); ?></h2>

    <form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
      <table class="form-table">
        <tr>
          <th>
            <label for="ezmesure_token"><?php _e('Authentication token', 'ezmesure-widget'); ?></label>
          </th>
          <td>
            <input
              type="password"
              class="regular-text"
              placeholder="Token"
              name="ezmesure_token"
              id="ezmesure_token"
              value="<?php echo get_option('ezmesure_token'); ?>"
            />
            <p class="description"><?php _e('Get your token by accessing your space at <a href="https://ezmesure.couperin.org/myspace">ezMESURE</a>.', 'ezmesure-widget'); ?></p>
          </td>
        </tr>
        <tr>
          <th>
            <label for="ezmesure_url"><?php _e('API URL', 'ezmesure-widget'); ?></label>
          </th>
          <td>
            <input
              type="text"
              class="regular-text"
              placeholder="API URL"
              name="ezmesure_url"
              id="ezmesure_url"
              value="<?php echo get_option('ezmesure_url', 'https://ezmesure.couperin.org/api'); ?>"
            />
            <p class="description"><?php _e('ezMESURE API URL to use. Except in special cases, leave the default.', 'ezmesure-widget'); ?></p>
          </td>
        </tr>
      </table>

      <p class="submit">
        <input type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
      </p>
    </form>
  </div>
  <?php
}
?>

<?php
/*
Plugin Name: Mailalert - Email on Login and others 
Description: Sends an Email to others when someone logs into your wordpress
Version: 1.0
Author: guenther haslbeck 
*/


// Send email when user logs in
function send_email_on_user_login( $user_login, $user ) {
    $to = get_option('eol_email_address'); 
    if (empty($to)) return;

    $site_url = get_bloginfo('url');
	
    $subject = "Ein Benutzer hat sich eingeloggt auf $site_url !";
    $message = "Hallo,\n\nDer Benutzer $user_login hat sich gerade eingeloggt.";
    
    wp_mail( $to, $subject, $message );
}

function on_upload( $post_id ) {
    $to = get_option('eol_email_address'); 
    if (empty($to)) return;

    if (get_option('eol_send_email', '1') !== '1') return; // Wenn die Checkbox nicht angehakt ist, nicht senden

    $site_url = get_bloginfo('url');
	
    $subject = "Ein Benutzer hat etwas hochgeladen auf $site_url !";
    $message = "Hallo,\n\nUpload $post_id wurde gerade gespeichert.";
    
    wp_mail( $to, $subject, $message );
}



add_action('wp_login', 'send_email_on_user_login', 10, 2);
add_action('add_attachment', 'on_upload');

function send_custom_email($message) {
    $site_url = get_bloginfo('url');
    $subject = "Aktion auf Ihrer Webseite $site_url";
    $to = get_option('eol_email_address'); 
    if (empty($to)) return;
    wp_mail($to, $subject, $message);
}

function on_post_save($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    send_custom_email("Ein Post wurde bearbeitet und gespeichert. Post-ID: $post_id.");
}
add_action('save_post', 'on_post_save');




// Admin page functions
function eol_admin_menu() {
    add_options_page(
        'Email on Login Settings',
        'Email on Login',
        'manage_options',
        'eol-settings',
        'eol_admin_page'
    );
}

function eol_admin_page() {
    ?>
    <div class="wrap">
        <h2>Email on Login Einstellungen</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('eol_settings_group');
            do_settings_sections('eol-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function eol_settings_init() {
    register_setting('eol_settings_group', 'eol_email_address');
    register_setting('eol_settings_group', 'eol_send_email');

    add_settings_field(
        'eol_send_email_field',
        'E-Mail senden bei Login',
        'eol_send_email_field_render',
        'eol-settings',
        'eol_settings_section'
  );

    add_settings_section(
        'eol_settings_section',
        'Einstellungen',
        null,
        'eol-settings'
    );

    add_settings_field(
        'eol_email_field',
        'E-Mail Adresse',
        'eol_email_field_render',
        'eol-settings',
        'eol_settings_section'
    );
}

function eol_email_field_render() {
    $email = get_option('eol_email_address');
    echo "<input type='email' name='eol_email_address' value='{$email}' />";
}

function eol_send_email_field_render() {
    $checked = get_option('eol_send_email', '1') ? 'checked' : ''; // Default-Wert ist '1' (angehakt)
    echo "<input type='checkbox' name='eol_send_email' value='1' $checked />";
}

add_action('admin_menu', 'eol_admin_menu');
add_action('admin_init', 'eol_settings_init');

function eol_add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=eol-settings">' . __('Einstellungen') . '</a>';
    array_push($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'eol_add_settings_link');

?>

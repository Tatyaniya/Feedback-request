<?php
/**
 * Plugin Name: Feedback Request
 * Plugin URI: https://github.com/Tatyaniya/Feedback-request
 * Description: Feedback request plugin. The output of the submission form is carried out through a shortcode.
 * Version: 1.0
 * Requires PHP: 7.4
 * Author: Tatiana Melnichuk
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: feedback_request
 */

function fr_register_assets() {
    wp_register_style( 'fr_admin_styles', plugins_url( 'assets/css/admin.css', __FILE__ ) );
    wp_enqueue_style( 'fr_admin_styles' );
}

add_action( 'admin_enqueue_scripts', 'fr_register_assets' );

register_activation_hook( __FILE__, function () {
    global $wpdb;

    $sql = "CREATE TABLE {$wpdb->prefix}fr (
      id INT(6) UNSIGNED AUTO_INCREMENT,
      user_name VARCHAR(250) NOT NULL,
      user_email VARCHAR(100) NOT NULL,
      user_phone VARCHAR(13) NOT NULL, 
      created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
   );";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta( $sql );
} );

add_action( 'plugins_loaded', function () {

    global $wpdb;

    if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
        if ( isset( $_POST["fr_username"] ) ) {
            $name = trim( strip_tags( $_POST["fr_username"] ) );
        }
        if ( isset( $_POST["fr_usernumber"] ) ) {
            $number = trim( strip_tags( $_POST["fr_usernumber"] ) );
        }
        if ( isset( $_POST["fr_useremail"] ) ) {
            $email = trim( strip_tags( $_POST["fr_useremail"] ) );
        }

        $wpdb->insert( "{$wpdb->prefix}fr", array(
            'user_name'  => $name,
            'user_email' => $email,
            'user_phone' => $number,
            'created'    => current_time( 'mysql' ),
        ) );
    }

}, 100, 0 );

function feedback_request_show_nav_item() {
    $hook = add_menu_page(
        'Feedback Request',
        'Feedback request',
        'manage_options',
        'feedback_request',
        'feedback_request_show_content',
        'dashicons-editor-table',
        66
    );
    add_action( "load-$hook", 'feedback_request_table_load' );
}

add_action( 'admin_menu', 'feedback_request_show_nav_item' );



add_shortcode( 'fr-feedback-form', 'fr_feedback_form' );

function fr_feedback_form( $attr ) {
    global $wpdb;

    $params = shortcode_atts( [
        'class' => 'fr-feedback-form',
    ], $attr );

    return '
        <form class="' . $params['class'] . '" method="post">
        	<div class="' . $params['class'] . '-container"> 
        		<div>
        			<label>Name <span>*</span></label>
        			<input type="text" name="fr_username" required>
        		</div>
        		<div>
        			<label>Phone (with code) <span>*</span></label>
        			<input type="tel" name="fr_usernumber" required>
        		</div>
        		<div>
        			<label>Email</label>
        			<input type="email" name="fr_useremail" required>
        		</div>
        		<input class="submit-btn" type="submit" value="Send request">
        	</div>
        </form>';
}

function feedback_request_table_load() {
    require_once __DIR__ . '/classes/FrTable.php';

    $GLOBALS['FrTable'] = new FrTable();
}

function feedback_request_show_content() {
    ?>
    <div class="wrap">
        <h2><?php echo get_admin_page_title() ?></h2>
        <h3>Use this shortcode to embed on the page:</h3>
        <div class="fr_shortcode_wrapper">
            [fr-feedback-form class="fr-feedback-form"]
        </div>
        <small>*You can replace the class value, giving it your own styles</small>
        <?php
        echo '<form action="" method="POST">';
        $GLOBALS['FrTable']->display();
        echo '</form>';
        ?>
    </div>
    <?php
}

register_uninstall_hook( __FILE__, 'fr_uninstall' );

function fr_uninstall() {
    global $wpdb;

    $wpdb->query( sprintf(
        "DROP TABLE IF EXISTS %s",
        $wpdb->prefix . 'fr'
    ) );
}

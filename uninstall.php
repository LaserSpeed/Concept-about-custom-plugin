<?php
// check whether the page is access through the wordpress or not
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    header('Location: /');
    die;
}

// delete the table whenever user delete the plugin
global $wpdb, $table_prefix;
$table = $table_prefix.'emp';
$sql = "DROP TABLE `$table`";
$wpdb->query($sql);
?>
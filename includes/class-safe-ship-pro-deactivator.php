<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 */
class Safe_Ship_Pro_Deactivator {

    /**
     * Clean up when the plugin is deactivated.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // We don't delete tables or settings on deactivation
        // This allows users to reactivate without losing data
        // For full cleanup, users should use the uninstall.php file
    }
}
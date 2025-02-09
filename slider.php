<?php 

    /* Plugin Name: Slider
       Description: This is slider plugin for your website
       Author: Sushil
       Version: 1.0
    */

    if(!defined('ABSPATH')) exit;

    define('SLIDER_PLUGIN_DIR',plugin_dir_path(__FILE__));
    define('SLIDER_PLUGIN_URL',plugin_dir_url(__FILE__));

    require_once SLIDER_PLUGIN_DIR . 'includes/slider_core.php';
    require_once SLIDER_PLUGIN_DIR . 'includes/admin_pannel.php';
    function slider_plugin_init(){
        new Admin_pannel();
        new Slider();
    }

    add_action('plugins_loaded','slider_plugin_init');
  
    function add_img_table(){
        global $wpdb;

        $slider_img = $wpdb->prefix . 'slider_img';
        $charset_collate = $wpdb->get_charset_collate();
    
        $create_table = "CREATE TABLE $slider_img (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            carousel_id BIGINT(20) NOT NULL,
            images VARCHAR(255) DEFAULT NULL,
            create_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";
    
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($create_table);
    }
   

    register_activation_hook(__FILE__,'add_img_table');
    function remove_img_table(){
        global $wpdb;
        $slider_img = $wpdb->prefix . 'slider_img';
        $wpdb->query("DROP TABLE IF EXISTS $slider_img");
    }
    register_deactivation_hook(__FILE__, 'remove_img_table');
;?>
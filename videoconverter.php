<?php
/*
 * Plugin Name: Video Converter
 * Plugin URI: https://wordpress.org/plugins/video-converter/
 * Description: This Plugin adds a link under embedded video's that allows to download or convert the video to mp3.
 * Version: 2.0.2
 * Author: Vlademir Bergamini
 * Author URI: http://www.maluco-beleza.com/
 * Licence: GPL3
 * Text Domain: videoconverter
 * Domain Path: /langs
 */
 $videoconverter_minimalRequiredPhpVersion = '5.0';
 
  if ( ! defined( 'ABSPATH')) {
      exit;
 }
 
 // Check the PHP verrsion and return true or error
 function vid_c_PhpVersionWrong() {
      global $videoconverter_minimalRequiredPhpVersion;
      echo '<div class="updated fade">'
       . __('Error: plugin "Video Converter" requires a newer version of PHP to running.', 'videoconverter').'</br>' 
       . __('Minimal version of PHP required: ', 'videoconverter') . '<strong>' .  $videoconverter_minimalRequiredPhpVersion . '</strong>' . '</br>' 
       . __('Your server\'s PHP version: ', 'videoconverter') . '<strong>' . phpversion() . '</strong>' .
            '</div>';
  }

 function vid_c_PhpVersionCheck() {
      global $videoconverter_minimalRequiredPhpVersion;
      if (version_compare(phpversion(), $videoconverter_minimalRequiredPhpVersion) < 0) {
       add_action('admin_notices', 'vid_c_PhpVersionWrong');
       return false;
     }
      else {
       return true;
     }
 }
 
  // internationalization textdomain
 function videoconverter_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('videoconverter', false, $pluginDir . '/langs/');
}
 
 
 
 // Initialize internationalization
 videoconverter_i18n_init();
 
 //Next, run the version check. If is successful, continue with initialization for this plugin
 if (vid_c_PhpVersionCheck()) {
     //Only load and run the init function if we know PHP version can parse it
     include_once('videoconverter-Init.php');
     vid_c_init(__FILE__);
 }
<?php 
/*
Plugin Name: Ditto
Plugin URI: http://club.orbisius.com/products/wordpress-plugins/ditto/
Description: This plugin allows you copy a plugin and theme (TODO) to a test/sandbox WordPress installation.
Version: 1.0.1
Author: Orbisius.com
Author URI: http://orbisius.com
License: GPL2
*/

// use widgets_init action hook to execute custom function
add_action( 'init', 'orbisius_ditto_init' );

add_action( 'admin_init', 'orbisius_ditto_admin_init' );
add_action( 'admin_menu', 'orbisius_ditto_create_menu' );

/**
 * Setups loading of assets (css, js)
 * @return void
 */
function orbisius_ditto_init() {
    orbisius_ditto_load_assets();
}

/**
 * Setups some actions only needed for WP admin.
 * @return void
 */
function orbisius_ditto_admin_init() {
    register_setting('orbisius_ditto_settings', 'orbisius_ditto_settings', 'orbisius_ditto_validate_settings');
}

/**
 * This is called by WP after the user hits the submit button.
 * The variables are trimmed first and then passed to the who ever wantsto filter them.
 * @param array the entered data from the settings page.
 * @return array the modified input array
 */
function orbisius_ditto_validate_settings($input) { // whitelist options
    // let's do some cleanup
    foreach ($input as $key => $value) {
        $value = wp_kses($value, array());
        $value = trim($value);

        $input[$key] = $value;
    }

    // let extensions do their thing
    $input_filtered = apply_filters('orbisius_ditto_ext_filter_settings', $input);

    // did the extension break stuff?
    $input = is_array($input_filtered) ? $input_filtered : $input;

    return $input;
}

/**
 * Retrieves the plugin options. It inserts some defaults.
 * The saving is handled by the settings page. Basically, we submit to WP and it takes
 * care of the saving.
 *
 * @return array
 */
function orbisius_ditto_get_options() {
    $defaults = array(
        'status' => 1,
    );

    $opts = get_option('orbisius_ditto_settings');

    $opts = (array) $opts;
    $opts = array_merge($defaults, $opts);

    return $opts;
}

/**
 * Returns some plugin data such name and URL. This info is inserted as HTML
 * comment surrounding the embed code.
 * @return array
 */
function orbisius_ditto_get_plugin_data() {
    // pull only these vars
    $default_headers = array(
		'Name' => 'Plugin Name',
		'PluginURI' => 'Plugin URI',
		'Description' => 'Description',
	);

    $plugin_data = get_file_data(__FILE__, $default_headers, 'plugin');

    $url = $plugin_data['PluginURI'];
    $name = $plugin_data['Name'];

    $data['name'] = $name;
    $data['url'] = $url;

    $data = array_merge($data, $plugin_data);

    return $data;
}

/**
 * This functions returns .min suffix for live installations and none on dev machine.
 * The idea is to load different css/js files depending on the environment.
 * e.g. for live: use main.min.js and dev main.js.
 * Minified version should load faster.
 */
function orbisius_ditto_get_asset_suffix() {
    $dev = empty($_SERVER['DEV_ENV']) ? 0 : 1;
    $suffix = $dev ? '' : '.min';

    return $suffix;
}

/**
 * Schdules css, js for loading when WP is ready.
 */
function orbisius_ditto_load_assets() {
    $suffix = orbisius_ditto_get_asset_suffix();

    wp_register_style( 'orbisius_ditto_css', plugins_url("/assets/main{$suffix}.css", __FILE__) );
    wp_enqueue_style( 'orbisius_ditto_css' );

    if (!is_admin()) {
        //wp_enqueue_script('jquery');
        //wp_register_script( 'orbisius_ditto_js', plugins_url("/assets/main{$suffix}.js", __FILE__), array('jquery', ), '1.0', true);
        //wp_enqueue_script( 'orbisius_ditto_js' );
    }
}

/**
 * Adds the menu under Settings > Orbisius
 */
function orbisius_ditto_create_menu() {
    $plug_slug = 'ditto';
    
	//create a submenu under Settings
	//add_options_page( 'Ditto', 'Ditto', 'manage_options', __FILE__, 'orbisius_ditto_settings_page' );
    
	// when plugins are shown add a settings link near my plugin for a quick access to the settings page.
	add_filter('plugin_action_links', 'orbisius_ditto_add_plugin_settings_link', 10, 2);

    // Main page
    add_menu_page( 'Ditto', 'Ditto', 'manage_options', $plug_slug, 'orbisius_ditto_dashboard_page',
            plugins_url( '/assets/server.png', __FILE__ ) );

    // Sub Pages
    add_submenu_page($plug_slug, 'Dashboard', 'Dashboard', 'manage_options', $plug_slug, 'orbisius_ditto_dashboard_page');
    add_submenu_page($plug_slug, 'Deploy', 'Deploy', 'manage_options', $plug_slug . '-deploy', 'orbisius_ditto_deploy_page');
    //add_submenu_page($plug_slug, 'Targets', 'Targets', 'manage_options', $plug_slug . '-targets', 'orbisius_ditto_targets_page');
    add_submenu_page($plug_slug, 'Settings', 'Settings', 'manage_options', $plug_slug . '-settings', 'orbisius_ditto_settings_page');
    add_submenu_page($plug_slug, 'Help', 'Help', 'manage_options', $plug_slug . '-help', 'orbisius_ditto_help_page');
}

// Add the ? settings link in Plugins page very good
function orbisius_ditto_add_plugin_settings_link($links, $file) {
    if ($file == plugin_basename(__FILE__)) {
        $link = admin_url('options-general.php?page=' . plugin_basename(__FILE__));
        $dashboard_link = "<a href=\"{$link}\">Settings</a>";
        array_unshift($links, $dashboard_link);
    }

    return $links;
}

/**
 * Saving options. Options are passed in an array. They should have been
 * filtered and cleaned already.
 * 
 * @param array $opts
 * @return array
 */
function orbisius_ditto_set_options($opts) {
    // let's do some cleanup
    foreach ($opts as $key => $value) {
        $value = wp_kses($value, array());
        $value = trim($value);

        $opts[$key] = $value;
    }
    
    update_option('orbisius_ditto_settings', $opts);
    
    return $opts;
}

 // Generates Options for the plugin
function orbisius_ditto_settings_page() {
    $opts = orbisius_ditto_get_options();
    ?>

    <div class="wrap orbisius_ditto_container">
        <h2>Ditto: Settings</h2>
        
        <div class="updated"><p>
            No configuration options are available at the moment.
        </p></div>

        <?php if (0) : /* disabled for now until I come up with something :) */ ?>
        <form method="post" action="options.php">
            <?php settings_fields('orbisius_ditto_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Menu Selector (used by jQuery)</th>
                    <td>
                        <label for="orbisius_ditto_settings_menu_selector">
                            <input type="text" id="orbisius_ditto_settings_menu_selector" class=""
                                   name="orbisius_ditto_settings[menu_selector]" autocomplete="off"
                                value="<?php echo esc_attr($opts['menu_selector']); ?>" />

                            How to get the menu class or ID? (<a href="javascript:void(0);"
                               onclick="jQuery('.orbisius_ditto_demo_how_to_get_nav_class_or_id_video').toggle();">show/hide help video</a>)
                            
                            <p class="orbisius_ditto_demo_how_to_get_nav_class_or_id_video hide">
                                <iframe width="560" height="315" src="http://www.youtube.com/embed/bmeYKBTVdLo" frameborder="0" allowfullscreen></iframe>

                                <br/>Video Link: <a href="http://www.youtube.com/watch?v=bmeYKBTVdLo&feature=youtu.be"
                                                    target="_blank">http://www.youtube.com/watch?v=bmeYKBTVdLo</a>
                            </p>

                        </label>
                        <p>
                            You need to set this depending on the theme. If the menu is not showing then you're not targetting the correct element.<br/>
                            <br/>Example: <strong>#top-menu</strong> &larr; when targeting menu a in div / UL element with a given ID
                            <br/>Example: <strong>.nav-menu</strong> &larr; when targeting menu a in div / UL element with a given class
                            
                            <br/>The actual HTML code for the menu items should look like: 
                                &lt;div&gt; &lt;ul id="top-menu" class="nav&gt;...&lt;/ul&gt; &lt;/ul&gt;<br/>

                            <br/>Use <strong>.menu</strong> for WordPress 2010 theme
                            <br/>Use <strong>.menu</strong> for WordPress 2011 theme
                            <br/>Use <strong>.nav-menu</strong> for WordPress 2012 theme
                            <br/>Use <strong>.nav-menu</strong> for WordPress 2013 theme
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Scroll Threshold</th>
                    <td>
                        <label for="orbisius_ditto_settings_scroll_threshold">
                            <input type="text" id="orbisius_ditto_settings_scroll_threshold"
                                   name="orbisius_ditto_settings[scroll_threshold]" autocomplete="off"
                                value="<?php echo esc_attr($opts['scroll_threshold']); ?>" />
                        </label>
                         (How many pixels should the user scroll down that will trigger the menu to be shown at the top)
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Menu Custom CSS</th>
                    <td>
                        <label for="orbisius_ditto_settings_menu_custom_css">
                            <textarea id="orbisius_ditto_settings_menu_custom_css" rows="3" class="widefat"
                                      name="orbisius_ditto_settings[menu_custom_css]"
                                ><?php echo esc_attr($opts['menu_custom_css']); ?></textarea>
                        </label>
                        <br/>CSS applied when the menu is shown at the top.
                        Example: <br/>
                        <strong>.orbisius_ditto_fixed_custom { background:#eee; }</strong>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save') ?>" />
            </p>
        </form>

        <h2>Video Demo
            <!--<a href="javascript:void(0);" onclick="jQuery('.orbisius_ditto_demo_video').toggle();">(show/hide)</a>-->
        </h2>
        
        <p class="orbisius_ditto_demo_video hide0">
            <iframe width="560" height="315" src="http://www.youtube.com/embed/vuRFblOKD8c" frameborder="0" allowfullscreen></iframe>

            <br/>Video Link: <a href="http://www.youtube.com/watch?v=vuRFblOKD8c&feature=youtu.be"
                                target="_blank">http://www.youtube.com/watch?v=vuRFblOKD8c</a>
         </p>

        <?php
            $plugin_data = orbisius_ditto_get_plugin_data();

            $app_link = urlencode($plugin_data['PluginURI']);
            $app_title = urlencode($plugin_data['Name']);
            $app_descr = urlencode($plugin_data['Description']);
        ?>
        <h2>Share</h2>
        <p>
            <!-- AddThis Button BEGIN -->
            <div class="addthis_toolbox addthis_default_style addthis_32x32_style">
                <a class="addthis_button_facebook" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_twitter" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_google_plusone" g:plusone:count="false" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_linkedin" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_email" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_myspace" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_google" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_digg" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_delicious" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_stumbleupon" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_tumblr" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_favorites" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_compact"></a>
            </div>
            <!-- The JS code is in the footer -->

            <script type="text/javascript">
            var addthis_config = {"data_track_clickback":true};
            var addthis_share = {
                templates: { twitter: 'Check out {{title}} #WordPress #plugin at {{lurl}} (via @orbisius)' }
            }
            </script>
            <!-- AddThis Button START part2 -->
            <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=lordspace"></script>
            <!-- AddThis Button END part2 -->
        </p>

        <?php endif; ?>
        <h2>Support & Feature Requests</h2>
        <div class="updated"><p>
            ** NOTE: ** Support is handled on our site: <a href="http://club.orbisius.com/support/" target="_blank" title="[new window]">http://club.orbisius.com/support/</a>.
            Please do NOT use the WordPress forums or other places to seek support.
        </p></div>

        <h2>Mailing List</h2>
        <p>
            Get the latest news and updates about this and future cool
                <a href="http://profiles.wordpress.org/lordspace/"
                    target="_blank" title="Opens a page with the pugins we developed. [New Window/Tab]">plugins we develop</a>.
        </p>
        <p>
            <!-- // MAILCHIMP SUBSCRIBE CODE \\ -->
            1) <a href="http://eepurl.com/guNzr" target="_blank">Subscribe to our newsletter</a>
            <!-- \\ MAILCHIMP SUBSCRIBE CODE // -->
        </p>
        <p>OR</p>
        <p>
            2) Subscribe using our QR code. [Scan it with your mobile device].<br/>
            <img src="<?php echo plugin_dir_url(__FILE__); ?>/i/guNzr.qr.2.png" alt="" />
        </p>
    </div>
    <?php
}

// Generates Options for the plugin
function orbisius_ditto_dashboard_page() {
    $opts = orbisius_ditto_get_options();
    ?>

    <div class="wrap orbisius_ditto_container">
        <h2>Ditto: Dashboard</h2>

        <div class="updated"><pre>
This plugin allows you copy a plugin and (TODO:) theme on a test/sandbox WordPress (local for now) installation.
This is plugin for developers/designers who need a quick way to copy their plugins and themes to another (clean) copy of WordPress
so they can test their work.
Click on the Deploy link to select a plugin to copy to a test site.</pre></div>

        <h2>Video Demo</h2>

        <p class="orbisius_ditto_demo_video">
            <iframe width="560" height="315" src="http://www.youtube.com/embed/vuRFblOKD8c" frameborder="0" allowfullscreen></iframe>

            <br/>Video Link: <a href="http://www.youtube.com/watch?v=vuRFblOKD8c&feature=youtu.be"
                                target="_blank">http://www.youtube.com/watch?v=vuRFblOKD8c</a>
        </p>

        <?php
            $plugin_data = orbisius_ditto_get_plugin_data();

            $app_link = urlencode($plugin_data['PluginURI']);
            $app_title = urlencode($plugin_data['Name']);
            $app_descr = urlencode($plugin_data['Description']);
        ?>
        <h2>Share</h2>
        <p>
            <!-- AddThis Button BEGIN -->
            <div class="addthis_toolbox addthis_default_style addthis_32x32_style">
                <a class="addthis_button_facebook" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_twitter" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_google_plusone" g:plusone:count="false" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_linkedin" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_email" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_myspace" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_google" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_digg" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_delicious" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_stumbleupon" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_tumblr" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_favorites" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                <a class="addthis_button_compact"></a>
            </div>
            <!-- The JS code is in the footer -->

            <script type="text/javascript">
            var addthis_config = {"data_track_clickback":true};
            var addthis_share = {
                templates: { twitter: 'Check out {{title}} #WordPress #plugin at {{lurl}} (via @orbisius)' }
            }
            </script>
            <!-- AddThis Button START part2 -->
            <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=lordspace"></script>
            <!-- AddThis Button END part2 -->
        </p>

        <h2>Support & Feature Requests</h2>
        <div class="updated"><p>
            ** NOTE: ** Support is handled on our site: <a href="http://club.orbisius.com/support/" target="_blank" title="[new window]">http://club.orbisius.com/support/</a>.
            Please do NOT use the WordPress forums or other places to seek support.
        </p></div>
    </div>
    <?php
}

// Generates Options for the plugin
function orbisius_ditto_help_page() {
    $opts = orbisius_ditto_get_options();
    ?>

    <div class="wrap orbisius_ditto_container">
        <h2>Ditto: Help, Support & Feature Requests</h2>

        <div class="updated"><p>
            ** NOTE: ** Support is handled on our site: <a href="http://club.orbisius.com/support/" target="_blank" title="[new window]">http://club.orbisius.com/support/</a>.
            Please do NOT use the WordPress forums or other places to seek support.
        </p></div>

        <h2>Video Demo</h2>

        <p class="orbisius_ditto_demo_video">
            <iframe width="560" height="315" src="http://www.youtube.com/embed/vuRFblOKD8c" frameborder="0" allowfullscreen></iframe>

            <br/>Video Link: <a href="http://www.youtube.com/watch?v=vuRFblOKD8c&feature=youtu.be"
                                target="_blank">http://www.youtube.com/watch?v=vuRFblOKD8c</a>
        </p>
    </div>
    <?php
}

// Generates Options for the plugin
function orbisius_ditto_targets_page() {
    $opts = orbisius_ditto_get_options();
    ?>

    <div class="wrap orbisius_ditto_container">
        <h2>Ditto: Targets</h2>

        <div class="updated00"><p>
            This plugin makes your main navigation stay on top when users scroll down on the page.
        </p></div>

        <form method="post">
            <?php settings_fields('orbisius_ditto_settings'); ?>
            <table class="form-table" width="50%">
                <tr>
                    <th scope="row">Target Test/Sandbox Site URL</th>
                    <td>
                        <label for="orbisius_ditto_settings_menu_selector">
                            <input type="text" id="orbisius_ditto_settings_menu_selector" class="widefat"
                                   name="orbisius_ditto_settings[menu_selector]" autocomplete="off"
                                value="<?php echo esc_attr($opts['menu_selector']); ?>" />

                            How to get the menu class or ID? (<a href="javascript:void(0);"
                               onclick="jQuery('.orbisius_ditto_demo_how_to_get_nav_class_or_id_video').toggle();">show/hide help video</a>)

                            <p class="orbisius_ditto_demo_how_to_get_nav_class_or_id_video hide">
                                <iframe width="560" height="315" src="http://www.youtube.com/embed/bmeYKBTVdLo" frameborder="0" allowfullscreen></iframe>

                                <br/>Video Link: <a href="http://www.youtube.com/watch?v=bmeYKBTVdLo&feature=youtu.be"
                                                    target="_blank">http://www.youtube.com/watch?v=bmeYKBTVdLo</a>
                            </p>

                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Scroll Threshold</th>
                    <td>
                        <label for="orbisius_ditto_settings_scroll_threshold">
                            <input type="text" id="orbisius_ditto_settings_scroll_threshold" class="widefat"
                                   name="orbisius_ditto_settings[scroll_threshold]" autocomplete="off"
                                value="<?php echo esc_attr($opts['scroll_threshold']); ?>" />
                        </label>
                         (How many pixels should the user scroll down that will trigger the menu to be shown at the top)
                    </td>
                </tr>

                <tr>
                    <th scope="row">Menu Custom CSS</th>
                    <td>
                        <label for="orbisius_ditto_settings_menu_custom_css">
                            <textarea id="orbisius_ditto_settings_menu_custom_css" rows="3" class="widefat"
                                      name="orbisius_ditto_settings[menu_custom_css]"
                                ><?php echo esc_attr($opts['menu_custom_css']); ?></textarea>
                        </label>
                        <br/>CSS applied when the menu is shown at the top.
                        Example: <br/>
                        <strong>.orbisius_ditto_fixed_custom { background:#eee; }</strong>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save') ?>" />
            </p>
        </form>
    </div>
    <?php
}


class Orbisius_Ditto_Transfer_Exception extends Exception {

}

/**
 * Parent class defining common methods
 */
class Orbisius_Ditto_Transfer {
    protected $data = array();
    
    /**
     * This checks for correct data.
     * Requirements:
     * - product_dir to be a non-empty string and to exist
     * - target_dir to be a non-empty string and its parent to exist.
     * We don't want the sub-folder to exist, it will be created if necessary.
     * e.g. target dir /target/plugins/orbisius-cyberstore/
     * e.g. parent dir /target/plugins/
     * we don't need (yet) /target/plugins/orbisius-cyberstore/ to exist but
     * we want /target/plugins/ to exist.
     */
    public function init( $data = array() ) {
        $data = apply_filters('orbisius_ditto_transfer_init', $data);

        if ( empty($data['product_base_dir']) ) {
            throw new Orbisius_Ditto_Transfer_Exception('Product base directory is not supplied or is invalid.');
        }

        if (empty($data['product_dir']) || !is_dir($data['product_dir'])) {
            throw new Orbisius_Ditto_Transfer_Exception('Product directory does not exist or is invalid.');
        }

        if ( empty($data['target_dir']) || !is_dir($data['target_dir']) ) {
            throw new Orbisius_Ditto_Transfer_Exception('Target directory does not exist or is invalid.');
        }

        if ( $data['target_dir'] == $data['product_dir'] ) {
            throw new Orbisius_Ditto_Transfer_Exception('Source and target directory cannot be the same. Many bad things can happen.');
        }

        $this->data = $data;
    }
    
    /**
     * Let's go!
     */
    public function transfer() {
        throw new Orbisius_Ditto_Transfer_Exception("Please override parent class' transfer method.");
    }
}

class Orbisius_Ditto_Util {
    /**
     * Recursive Trimmer :)
     * http://php.net/manual/en/function.trim.php
     *
     * @param mixed $arr
     * @param string $charlist
     * @return mixed
     */
    static public function trim($arr, $charlist = ' \t\n\r\0\x0B') {
        if (is_string($arr)) {
            return trim($arr, $charlist);
        } elseif (is_array($arr)) {
            foreach($arr as $key => $value){
                if (is_array($value)) {
                    $result[$key] = self::trim($value, $charlist);
                } else {
                    $result[$key] = trim($value, $charlist);
                }
            }

            return $result;
        } else {
            return $arr;
        }
    }

    /**
     * Checks if it's running on Windows
     * @return bool
     */
    public static function isWindows() {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    public static function time() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Removes a directory recursively.
     * 
     * @param string $dir
     * @return string
     * @see http://php.net/manual/en/function.rmdir.php
     */
    public static function removeDirectory( $dir ) {
       if ( !is_dir( $dir ) ) {
          return true;
       }

       $dir .= '/';
       $dir = self::normalize_path($dir);

       $files = array_diff( scandir( $dir ), array( '.', '..' ) );

       foreach ( $files as $file ) {
         $f = $dir . $file;
         
         is_dir( $f )
            ? self::removeDirectory( $f )
            : unlink( $f );
       }
       
       return rmdir( $dir );
    }

    /**
     * Copies contents of a folder to a target folder.
     * Native copy Needs testing (linux copy part). xcopy on Windows is OK.
     * 
     * @param string $src
     * @param string $target
     * @param bool $native - should it use a native function (quick/default)
     * @return bool true on success and false
     * @see http://php.net/manual/en/function.copy.php
     */
    public static function copyDirectory($src, $target, $native = 0) {
        $status = false;
        
        if (!is_dir($target) && !mkdir($target, 0775, 1)) {
            return false;
        }

        if ($native) {
            $src .= '/';
            $target .= '/';
            
            $src = self::normalize_path($src);
            $target = self::normalize_path($target);

            if (self::isWindows()) {
                $src .= '*.*';

                $src_esc = escapeshellarg($src);
                $target_esc = escapeshellarg($target);

                // /S           Copies directories and subdirectories except empty ones.
                // /E           Copies directories and subdirectories, including empty ones.
                // /I           If destination does not exist and copying more than one file assumes that destination must be a directory.
                // /H           Copies hidden and system files also.
                // /Q           Does not display file names while copying.
                // /Y           Suppresses prompting to confirm you want to overwrite an existing destination file.
                $cmd = "xcopy $src_esc $target_esc";
                $cmd = str_replace('/', '\\', $cmd); // we need to make windows slashes because this '/' break the xcopy
                $cmd .= " /s /e /i /h /q /y /c"; // options
            } else {
                //$src .= '*.*';
                $src_esc = escapeshellarg($src);
                $target_esc = escapeshellarg($target);

                $cmd = "cp -r -a $src_esc $target_esc 2>&1";
                $cmd = str_replace('/', '\\', $cmd); // we need to make windows slashes because this '/' break the xcopy

                self::copy($src, $target);
            }

            $out = shell_exec($cmd); // it is faster to call OS funcs
        } else {
            self::copy($src, $target);
        }

        $status = is_dir($target);

        return $status;
    }

    /**
     * Recursive function to copy (all subdirectories and contents).
     * It doesn't create folder in the target folder.
     * Note: this may be slow if there are a lot of files.
     * The native call might be quicker.
     * 
     * Example: src: folder/1/ target: folder/2/
     * @see http://stackoverflow.com/questions/5707806/recursive-copy-of-directory
     */
    static public function copy($src, $dest, $perm = 0775) {
        if (!is_dir($dest)) {
            mkdir($dest, $perm, 1);
        }

        if (is_dir($src)) {
            $dir = opendir($src);
            
            while ( false !== ( $file = readdir($dir) ) ) {
                if ( $file == '.' || $file == '..' || $file == '.git'  || $file == '.svn' ) {
                    continue;
                }

                $new_src = rtrim($src, '/') . '/' . $file;
                $new_dest = rtrim($dest, '/') . '/' . $file;

                if ( is_dir( $new_src ) ) {
                    self::copy( $new_src, $new_dest );
                } else {
                    copy( $new_src, $new_dest );
                }
            }
            
            closedir($dir);
        } else { // can also handle simple copy commands
            copy($src, $dest);
        }
    }

    /**
     * a simple status message, no formatting except color
     */
    static public function msg($msg, $status = 0, $use_inline_css = 0) {
        $inline_css = '';
        $id = 'app';
        $cls = empty($status) ? 'app_error error' : 'app_success updated fade';

        if ($use_inline_css) {
            $inline_css = empty($status) ? 'background-color:red;' : 'background-color:green;';
            $inline_css .= 'text-align:center;margin-left: auto; margin-right:auto; padding-bottom:10px;color:white;';
        }

        $str = <<<MSG_EOF
<div id='$id-notice' class='$cls' style="$inline_css">
    <p>$msg</p>
</div>
MSG_EOF;
        return $str;
    }

    /**
     * Fixes slashes and double onces too.
     * @param string $path
     * @return string
     */
    public static function normalize_path($path) {
        $path = str_replace('\\', '/', $path); // windows fix
        $path = preg_replace('#/+#si', '/', $path); // double/triple path fix
        
        return $path;
    }
}

/**
 * 
 */
class Orbisius_Ditto_Transfer_Module_Local extends Orbisius_Ditto_Transfer {
    /**
     * Let's go!
     * product_base_dir -> like-gate-pro
     */
    public function transfer() {
        $status = 0;
        $product_dir = $this->data['product_dir'];
        $target_dir = $this->data['target_dir'] . $this->data['product_base_dir'] . '/';

        // we have to delete target folder so we want to make sure
        // we're deleting the right one.
        $target_safety_regex = '#/((?:mu-)?plugins|themes)/#si';
        
        if (!preg_match($target_safety_regex, $target_dir)) {
            throw new Orbisius_Ditto_Transfer_Exception("Safety: The target directory does not contain mu-plugins, plugins or themes in their name. "
                . "Aborting so we don't delete something by accident.");
        }

        if (is_dir($target_dir)) {
            $status = Orbisius_Ditto_Util::removeDirectory($target_dir);
        }

        $status = Orbisius_Ditto_Util::copyDirectory($product_dir, $target_dir);

        return $status;
    }
}

/**
 * HTML related methods
 */
class Orbisius_Ditto_Util_HTML {
    /**
     *
     * Appends a parameter to an url; uses '?' or '&'. It's the reverse of parse_str().
     * If no URL is supplied no prefix is added (? or &)
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public static function add_url_params($url, $params = array()) {
        $str = $query_start = '';

        $params = (array) $params;

        if (empty($params)) {
            return $url;
        }

        if (!empty($url)) {
            $query_start = (strpos($url, '?') === false) ? '?' : '&';
        }

        $str = $url . $query_start . http_build_query($params);

        return $str;
    }

    // generates HTML select
    public static function html_select($name = '', $sel = null, $options = array(), $attr = '') {
        $name = trim($name);
        $elem_name = $name;
        $elem_name = strtolower($elem_name);
        $elem_name = preg_replace('#[^\w]#si', '_', $elem_name);
        $elem_name = trim($elem_name, '_');

        $html = "\n" . '<select id="' . esc_attr($elem_name) . '" name="' . esc_attr($name) . '" ' . $attr . '>' . "\n";

        foreach ($options as $key => $label) {
            $selected = $sel == $key ? ' selected="selected"' : '';

            // if the key contains underscores that means these are labels
            // and should be readonly
            if (strpos($key, '__') !== false) {
                $selected .= ' disabled="disabled" ';
            }

            $html .= "\t<option value='$key' $selected>$label</option>\n";
        }

        $html .= '</select>';
        $html .= "\n";

        return $html;
    }
}

// Generates Options for the plugin
function orbisius_ditto_deploy_page() {
    $msg = '';
    $opts = orbisius_ditto_get_options();
    $product = 'plugin';

    $data_defaults = array(
        'product' => $product, // theme/plugin/site
        'product_dir' => '',
        'target_dir' => '',
    );

    $deploy_data = get_option('orbisius_ditto_deploy');
    $deploy_data = empty($deploy_data) ? array() : $deploy_data;
    $deploy_data_hash = sha1(serialize($deploy_data));

    // Get from last used data.
    if (!empty($deploy_data['target_dir'])) {
        $data_defaults['target_dir'] = $deploy_data['target_dir'];
    }

    if (!empty($deploy_data['product_dir'])) {
        $data_defaults['product_dir'] = $deploy_data['product_dir'];
    }

    $data = empty($_REQUEST['data']) ? array() : $_REQUEST['data'];
    $data = array_merge($data_defaults, $data);

    // Security is everything!
    foreach ($data as $key => $value) {
        $key = wp_kses($key, array());
        $value = wp_kses($value, array());
        $data[$key] = $value;
    }
    
    $data = Orbisius_Ditto_Util::trim($data);

    if (!empty($_POST)) {
        try {
            if ( empty($data['product_dir']) || empty($data['target_dir']) ) {
                throw new Exception("Product or Target directory is empty/invalid.");
            }

            $data['product_dir'] .= '/';
            $data['target_dir'] .= '/';
            
            $data['product_base_dir'] = basename($data['product_dir']); // plugin's slug

            $data['product_dir'] = Orbisius_Ditto_Util::normalize_path($data['product_dir']);
            $data['target_dir'] = Orbisius_Ditto_Util::normalize_path($data['target_dir']);

            $transfer_obj = new Orbisius_Ditto_Transfer_Module_Local();
            $transfer_obj->init($data);
            $status = $transfer_obj->transfer();
            $msg = $status ? 'Done' : 'Error';
            $msg = Orbisius_Ditto_Util::msg(  $status ? 'Done' : 'Error', $status );

            $deploy_data['product_dir'] = $data['product_dir'];
            $deploy_data['target_dir'] = $data['target_dir'];

            // do we need to update. WP does this?
            if (!empty($deploy_data) && sha1(serialize($deploy_data)) != $deploy_data_hash) {
                update_option('orbisius_ditto_deploy', $deploy_data);
            }
        } catch (Exception $e) {
            $msg = Orbisius_Ditto_Util::msg( 'Error: ' . $e->getMessage() );
        }
    }

    /*
     array(53) {
  ["1ins-online-chat-inviter/1ins-online-chat-inviter.php"]=>
  array(11) {
    ["Name"]=>
    string(17) "1ins Chat Inviter"
    ["PluginURI"]=>
    string(19) "http://orbisius.com"
    ["Version"]=>
    string(5) "1.0.0"
    ["Description"]=>
    string(89) "Enables Mibew chat to open a popup window after NN seconds if there are operators online."
    ["Author"]=>
    string(25) "Svetoslav Marinov (SLAVI)"
    ["AuthorURI"]=>
    string(19) "http://orbisius.com"
    ["TextDomain"]=>
    string(0) ""
    ["DomainPath"]=>
    string(0) ""
    ["Network"]=>
    bool(false)
    ["Title"]=>
    string(17) "1ins Chat Inviter"
    ["AuthorName"]=>
    string(25) "Svetoslav Marinov (SLAVI)"
  }
  .....
     */
    $plugins_data = get_plugins();
    
    /*echo "<pre>";
    var_Dump($plugins_data);
    echo "</pre>";*/

    $html_dropdown_plugins = array( '__plugins_sep' => '', '__plugin_label' => '============ Select Plugin ============' );

    foreach ($plugins_data as $plugin_dir_and_main_file => $plugin_data_array) {
        // $plugin_dir_and_main_file is "1ins-online-chat-inviter/1ins-online-chat-inviter.php"
        $plugin_dir = WP_PLUGIN_DIR . '/' . dirname($plugin_dir_and_main_file) . '/';
        $plugin_dir = Orbisius_Ditto_Util::normalize_path($plugin_dir);
        $html_dropdown_plugins[$plugin_dir] = $plugin_data_array['Name'] . " (ver: {$plugin_data_array['Version']})";
    }

    $html_dropdown_themes = array( '__themes_sep' => '',  '__theme_label' => '============ Select Theme ============' );
    
    $theme_load_args = array();
    $themes = wp_get_themes( $theme_load_args );

    $all_themes_root_dir = get_theme_root();
    
    // we use the same CSS as in WP's appearances but put only the buttons we want.
    foreach ($themes as $theme_basedir_name => $theme_obj) {
        $theme_name = $theme_obj->Name . " (ver: $theme_obj->Version)";
        $theme_dir = $all_themes_root_dir  . "/$theme_basedir_name/";
        $theme_dir = Orbisius_Ditto_Util::normalize_path($theme_dir);

        $parent_theme = $theme_obj->get('Template');

        if ( !empty($parent_theme) ) {
            $theme_name .= " (child of $parent_theme)";
        }

        $html_dropdown_themes[$theme_dir] = $theme_name;
    }

    $html_dropdown_themes['__themes_sep2'] = '';

    $html_dropdown_dropdown = $html_dropdown_plugins + $html_dropdown_themes;

    /*echo "<pre>";
    var_Dump($themes);
    echo "</pre>";*/

    ?>

    <div class="wrap orbisius_ditto_container">
        <h2>Ditto: Deploy</h2>

        <div class="updated00"><p>
            Select from the dropdown a plugin that you want to be copied in your test/sandbox installation.
            <br/>Currently, only local (residing on the same computer/server) installations are supported.
            <br/>Note: In future versions of the plugin it will support activation and deactivation of the deployed plugin/theme on <a href="http://qsandbox.com/?utm_source=ditto" target="_blank">qSandbox.com</a>
Currently, target plugin's code is copied, therefore if your plugin requires database creation you have to do that manually.
        </p></div>

        <?php echo $msg; ?>
        
        <form method="post">
            <table class="form-table" width="50%">
                <tr>
                    <th scope="row">Product to Deploy</th>
                    <td>
                        <label for="orbisius_ditto_settings_src">
                           <?php echo Orbisius_Ditto_Util_HTML::html_select('data[product_dir]', $data['product_dir'], $html_dropdown_dropdown); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Parent Target Directory</th>
                    <td>
                        <label for="orbisius_ditto_settings_target">
                            <input type="text" id="orbisius_ditto_settings_target" class="widefat"
                                   name="data[target_dir]" autocomplete="off"
                                value="<?php echo esc_attr($data['target_dir']); ?>" />
                        </label>
                        <br/>
                        <div>
                            Parent Target Directory must be on the same server (just for now) and must belong to another WordPress installation.
                            <br/>Example: /var/www/vhosts/yoursite.com/htdocs/wordpress-clean/wp-content/plugins/
                            <br/>Example: /var/www/vhosts/yoursite.com/htdocs/wordpress-clean/wp-content/themes/
                            <br/>Windows paths work too.

                            <br/><br/>The Target Directory will be: (for plugins) Parent Target Directory/<strong>SELECTED-PLUGIN-SLUG</strong>/
                            or For themes: Parent Target Directory/<strong>SELECTED-THEME-DIR</strong>/
                            <br/><strong>Warning: </strong> the target directory will be deleted before the copy operation.
                            As a safety feature, we require that the target folder contain /plugins/ or /mu-plugins/ or /themes/ in their path
                            so this plugin doesn't delete the wrong folder and its contents by accident.
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Transfer Method</th>
                    <td>
                        <label for="orbisius_ditto_settings_target">
                            Local
                        </label>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Go!') ?>" />
            </p>
        </form>
    </div>
    <?php
}

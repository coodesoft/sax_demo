<?php
/**
 * Plugin Name: GLOBALSAX - Core
 * Description: Agregar funcionalidades a nuestro Ecommerce GLOBALSAX
 * Plugin URI: http://quintesco.com/
 * Author: Eduardo Quintero
 * Author URI: http://quintesco.com/
 * Version: 1.0
 * Text Domain: globalsax-core
 * License: GPL2
 */

/**
 * Get some constants ready for paths when your plugin grows
 */
define('GLOBALSAX_VERSION', '1.0');
define('GLOBALSAX_PATH', dirname(__FILE__));
define('GLOBALSAX_PATH_INCLUDES', dirname(__FILE__) . '/inc');
define('GLOBALSAX_FOLDER', basename(GLOBALSAX_PATH));
define('GLOBALSAX_URL', plugins_url() . '/' . GLOBALSAX_FOLDER);
define('GLOBALSAX_URL_INCLUDES', GLOBALSAX_URL . '/inc');

/**
 * The plugin base class - the root of all WP goods!
 *
 * @author nofearinc
 */
class GLOBALSAX_Plugin_Base {

    public function __construct() {
		add_action('wp_enqueue_scripts', array($this,'globalsax_add_JS'));
        add_action('wp_enqueue_scripts', array($this,'globalsax_add_CSS'));

        // add scripts and styles only available in admin
        add_action('admin_enqueue_scripts', array($this,'globalsax_add_admin_JS'));
        add_action('admin_enqueue_scripts', array($this,'globalsax_add_admin_CSS'));

        // register admin pages for the plugin
        //add_action('admin_menu', array($this,'globalsax_admin_pages_callback'));

        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, 'globalsax_on_activate_callback');
        register_deactivation_hook(__FILE__, 'globalsax_on_deactivate_callback');

        // Translation-ready
        // add_action('plugins_loaded', array($this,'globalsax_add_textdomain'));

        // Add earlier execution as it needs to occur before admin page display
        //add_action('admin_init', array($this,'globalsax_register_settings'), 5);

        /*
         * TODO:
         * template_redirect
         */

        // Add actions for storing value and fetching URL*/
        // use the wp_ajax_nopriv_ hook for non-logged users (handle guest actions)
        add_action('wp_ajax_store_ajax_value', array($this,'store_ajax_value'));
        add_action('wp_ajax_fetch_ajax_url_http', array($this,'fetch_ajax_url_http'));
		/*incluir funcionalidades*/
    }

    /**
     * Adding JavaScript scripts
     * Loading existing scripts from wp-includes or adding custom ones
     */
    public function globalsax_add_JS() {
        wp_enqueue_script('jquery');
        wp_register_script('globalsax-script', plugins_url('/js/globalsax-script.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_script('globalsax-script');

        wp_register_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyA-sMde0_QIgUq_tMtSqK0RamPViALBZSs');
        wp_enqueue_script('google-maps');

    }

    /**
     * Adding JavaScript scripts for the admin pages only
     * Loading existing scripts from wp-includes or adding custom ones
     */
    public function globalsax_add_admin_JS($hook) {
        wp_enqueue_script('jquery');
        wp_register_script('globalsax-script-admin', plugins_url('/js/globalsax-script-admin.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_script('globalsax-script-admin');
    }

    /**
     * Add CSS styles
     */
    public function globalsax_add_CSS() {
        wp_register_style('globalsax-style', plugins_url('/css/globalsax-style.css', __FILE__), array(), '1.0', 'screen');
        wp_enqueue_style('globalsax-style');
    }

    /**
     * Add admin CSS styles - available only on admin
     */
    public function globalsax_add_admin_CSS($hook) {
        wp_register_style('globalsax-style-admin', plugins_url('/css/globalsax-style-admin.css', __FILE__), array(), '1.0', 'screen');
        wp_enqueue_style('globalsax-style-admin');

        if ('toplevel_page_globalsax-plugin-base' === $hook) {
            wp_register_style('globalsax_help_page', plugins_url('/help-page.css', __FILE__));
            wp_enqueue_style('globalsax_help_page');
        }
    }

    /**
     * The content of the base page
     */
    public function globalsax_plugin_base() {
        include_once (GLOBALSAX_PATH_INCLUDES . '/base-page-template.php');
    }

    public function globalsax_plugin_side_access_page() {
        include_once (GLOBALSAX_PATH_INCLUDES . '/remote-page-template.php');
    }

    /**
     * Initialize the Settings class
     * Register a settings section with a field for a secure WordPress admin option creation.
     */
    public function globalsax_register_settings() {
        require_once (GLOBALSAX_PATH . '/globalsax-plugin-settings.class.php');
        new GLOBALSAX_Plugin_Settings();
    }

    /**
     * Add textdomain for plugin
     */
    public function globalsax_add_textdomain() {
        load_plugin_textdomain('globalsaxbase', false, dirname(plugin_basename(__FILE__)) . '/lang/');
    }

    /**
     * Callback for saving a simple AJAX option with no page reload
     */
    public function store_ajax_value() {
        if (isset($_POST['data']) && isset($_POST['data']['globalsax_option_from_ajax'])) {
            update_option('globalsax_option_from_ajax', $_POST['data']['globalsax_option_from_ajax']);
        }
        die();
    }

    /**
     * Callback for getting a URL and fetching it's content in the admin page
     */
    public function fetch_ajax_url_http() {
        if (isset($_POST['data']) && isset($_POST['data']['globalsax_url_for_ajax'])) {
            $ajax_url = $_POST['data']['globalsax_url_for_ajax'];

            $response = wp_remote_get($ajax_url);

            if (is_wp_error($response)) {
                echo json_encode(__('Invalid HTTP resource', 'globalsaxbase'));
                die();
            }

            if (isset($response['body'])) {
                if (preg_match('/<title>(.*)<\/title>/', $response['body'], $matches)) {
                    echo json_encode($matches[1]);
                    die();
                }
            }
        }
        echo json_encode(__('No title found or site was not fetched properly', 'globalsaxbase'));
        die();
    }
}

/**
 * Register activation hook
 */
function globalsax_on_activate_callback() {
    // do something on activation
}

/**
 * Register deactivation hook
 */
function globalsax_on_deactivate_callback() {
    // do something when deactivated
}

// ** CUSTOMIZADO **//
/**
 * inicializar parametros GLOBALSAX
 */
add_action('admin_menu', 'globalsax_init', 0);
function globalsax_init() {
	//add_menu_page('My Cool Plugin Settings', 'Cool Settings', 'administrator', __FILE__, 'my_cool_plugin_settings_page', get_stylesheet_directory_uri('stylesheet_directory')."/images/media-button-other.gif");
	add_menu_page("GLOBALSAX", "GLOBALSAX", "manage_options", "globalsax-core", "theme_settings_page", null, 67);
    include_once ("pantallaAdminGlobalsax.php");
}
// ** CUSTOMIZADO BOTON CONFIGURACION DE GLOBALSAX **
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'globalsaxore_action_links');
function globalsaxore_action_links($links) {
    $plugin_links = array('<a href="' . admin_url('admin.php?page=globalsax-core') . '">' . __('Ajustes', 'globalsax-core') . '</a>');
    return array_merge($plugin_links, $links);
}
// Initialize everything
$globalsax_plugin_base = new GLOBALSAX_Plugin_Base();
/**************************************************************************************************************/
add_action('wp_loaded', 'cargar_funcionalidades',0);
function cargar_funcionalidades() {
	require_once("funcionalidades/test.php");
	require_once("funcionalidades/sincronizarProductos.php");
	require_once("funcionalidades/sincronizarClientes.php");
	require_once("funcionalidades/sincronizarPrecios.php");
  require_once("funcionalidades/sincronizarVendedores.php");
	require_once("funcionalidades/catalogo.php");
	require_once("funcionalidades/botonComprar.php");
  require_once("funcionalidades/gbs_catalogo.php");

}

add_action('wp_head', 'globalsax_ajaxurl');
function globalsax_ajaxurl() {
    echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

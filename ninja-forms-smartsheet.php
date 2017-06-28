<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

require ABSPATH . '..' . DIRECTORY_SEPARATOR .  'vendor' . DIRECTORY_SEPARATOR. 'autoload.php';
use SmartSheet\SmartSheet;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

define('SMARTSHEET_KEY', 'smartsheet');
define('FORM', 'form');
define('FIELDS', 'fields');
define('SMARTSHEET_DELIMITER', '_');
define('SMARTSHEET_ID', 'id');


/*
 * Plugin Name: Ninja Forms - smartsheet
 * Plugin URI: https://github.com/catenare/ninjaforms-smartsheet
 * Description: Add a new sheet to Smartsheet when publishing a new form. Update the sheet with new data when form is submitted. 
 * Version: 3.0.0
 * Author: Johan Martin
 * Author URI: http://www.johan-martin.com
 * Text Domain: ninja-forms-smartsheet
 *
 * Copyright 2017 Johan Martin.
 */



if ( version_compare( get_option( 'ninja_forms_version', '0.0.0' ), '3', '<' ) || get_option( 'ninja_forms_load_deprecated', false ) ) {

	//include 'deprecated/ninja-forms-smartsheet.php';


} else {

	/**
	 * Class NF_Smartsheet
	 */
	final class NF_Smartsheet {
		const VERSION = '0.0.1';
		const SLUG = 'smartsheet';
		const NAME = 'smartsheet';
		const AUTHOR = 'Johan Martin';
		const PREFIX = 'NF_Smartsheet';
		const SMARTSHEET_KEY = 'smartsheet';


		/**
		 * @var NF_Smartsheet
		 * @since 3.0
		 */
		private static $instance;

		/**
		 * Plugin Directory
		 *
		 * @since 3.0
		 * @var string $dir
		 */
		public static $dir = '';

		/**
		 * Plugin URL
		 *
		 * @since 3.0
		 * @var string $url
		 */
		public static $url = '';

		/**
		 * @var SmartSheet
		 */
		private $smartsheet;

		private $smartsheet_field_array_by_label = [];

		/**
		 * @var Logger
		 */
		protected $logger;

		/**
		 * Main Plugin Instance
		 *
		 * Insures that only one instance of a plugin class exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 3.0
		 * @static
		 * @static var array $instance
		 * @return NF_Smartsheet Highlander Instance
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof NF_Smartsheet ) ) {
				self::$instance = new NF_Smartsheet();

				self::$dir = plugin_dir_path( __FILE__ );

				self::$url = plugin_dir_url( __FILE__ );

				/*
				 * Register our autoloader
				 */
				spl_autoload_register( array( self::$instance, 'autoloader' ) );
			}

			return self::$instance;
		}

		public function __construct() {

			$url   = Ninja_Forms()->get_setting( 'api' );
			$token = Ninja_Forms()->get_setting( 'token' );

			$this->smartsheet = new SmartSheet( $token, $url );

			$this->logger = new Logger( SMARTSHEET_KEY );
			$path         = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'output.log';
			$this->logger->pushHandler( new StreamHandler( $path, Logger::DEBUG ) );

//			$this->logger->pushHandler(new BrowserConsoleHandler());
			/*
			 * Required for all Extensions.
			 */
			add_action( 'admin_init', array( $this, 'setup_license' ) );


			/*
			 * Optional. If your extension processes or alters form submission data on a per form basis...
			 */
			add_filter( 'ninja_forms_register_actions', array( $this, 'register_actions' ) );
			add_filter( 'ninja_forms_plugin_settings', array( $this, 'plugin_settings' ) );
			add_filter( 'ninja_forms_plugin_settings_groups', array( $this, 'plugin_settings_groups' ) );
			add_filter( 'ninja_forms_update_setting_smartsheet', array( $this, 'save_smartsheet_settings' ) );
			add_filter( 'ninja_forms_save_form', array( $this, 'form_publish' ) );

		}

		/**
		 * @param $actions
		 *
		 * @return mixed
		 */
		public function register_actions( $actions ) {
			$actions['smartsheet'] = new NF_Smartsheet_Actions_Smartsheet( $this->smartsheet );
			// includes/Actions/Smartsheet.php
			return $actions;
		}

		/**
		 * @param $class_name
		 */
		public function autoloader( $class_name ) {
			if ( class_exists( $class_name ) ) {
				return;
			}

			if ( false === strpos( $class_name, self::PREFIX ) ) {
				return;
			}

			$class_name  = str_replace( self::PREFIX, '', $class_name );
			$classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
			$class_file  = str_replace( '_', DIRECTORY_SEPARATOR, $class_name ) . '.php';

			if ( file_exists( $classes_dir . $class_file ) ) {
				require_once $classes_dir . $class_file;
			}
		}

		/**
		 * Template
		 *
		 * @param string $file_name
		 * @param array $data
		 */
		public static function template( $file_name = '', array $data = array() ) {
			if ( ! $file_name ) {
				return;
			}

			extract( $data );

			include self::$dir . 'includes/Templates/' . $file_name;
		}

		/**
		 * Config
		 *
		 * @param $file_name
		 *
		 * @return mixed
		 */
		public static function config( $file_name ) {
			return include self::$dir . 'includes/Config/' . $file_name . '.php';
		}

		/**
		 *
		 */
		public function setup_license() {
			if ( ! class_exists( 'NF_Extension_Updater' ) ) {
				return;
			}

			new NF_Extension_Updater( self::NAME, self::VERSION, self::AUTHOR, __FILE__, self::SLUG );
		}

		/**
		 * @param $settings
		 *
		 * @return mixed
		 */
		public function plugin_settings( $settings ) {
			$settings['smartsheet'] = array(
				'token' => array(
					'id'    => 'token',
					'type'  => 'textbox',
					'width' => 'one-half',
					'label' => __( 'Token', 'ninja-forms-smartsheet' ),
					'desc'  => __( 'Smartsheet Token Value', 'ninja-forms-smartsheet' )
				),
				'api'   => array(
					'id'    => 'api',
					'type'  => 'textbox',
					'width' => 'one-half',
					'label' => __( 'Url', 'ninja-forms-smartsheet' ),
					'desc'  => __( 'Smartsheet Url Value', 'ninja-forms-smartsheet' )
				)
			);

			return $settings;
		}

		/**
		 * @param $groups
		 */
		public function plugin_settings_groups( $groups ) {
			$groups['smartsheet'] = array(
				'id'    => 'smartsheet',
				'label' => __( 'Smartsheet Settings', 'ninja-forms-smartsheet' ),
			);
		}

		/**
		 * @param $settings_value
		 */
		public function save_smartsheet_settings( $settings_value ) {
			if ( strpos( $settings_value, '_' ) ) {
				$parts = exploded( '_', $settings_value );

				foreach ( $parts as $key => $value ) {
					Ninja_Forms()->update_setting( 'smartsheet_part_' . $key, $value );
				}
			}
		}

		/**
		 * Create new smartsheet form
		 *
		 * @param $id
		 */
		public function form_publish( $id ) {
			$smartsheet = new \NinjaForm\SmartSheet($this->smartsheet, $id);
			if ( $smartsheet->getIsNewForm() && $smartsheet->getIsSmartsheetAction() ) {
				$data = $smartsheet->getDataForSmartsheet();
				$result = $smartsheet->saveToSmartsheet($data);
				$smartsheet->updateColumnSettingWithSmartsheetId();
				$smartsheet->updateSheetSmartsheetId();
			}
		}
	}


	/**
	 * @return NF_Smartsheet
	 */
	function NF_Smartsheet() {
		return NF_Smartsheet::instance();
	}

	NF_Smartsheet();


}

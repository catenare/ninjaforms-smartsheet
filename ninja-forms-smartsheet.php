<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require 'vendor/autoload.php';
use SmartSheet\SmartSheet;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use NinjaForm\SmartSheet;


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

			$this->logger = new Logger( 'smartsheet' );
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
			$actions['smartsheet'] = new NF_Smartsheet_Actions_Smartsheet(); // includes/Actions/Smartsheet.php

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
		public function form_pubish( $id ) {
			$smartsheet = new \NinjaForm\SmartSheet($this->smartsheet, $id);
			if ( $smartsheet->getIsNewForm() && $smartsheet->getIsSmartsheetAction() ) {
				$data = $smartsheet->getDataForSmartsheet();
				$result = $smartsheet->saveToSmartsheet($data);
				$smartsheet->updateColumnSettingWithSmartsheetId();
				$smartsheet->updateSheetSmartsheetId();
			}
		}

		/**
		 * @param $id
		 */
//		public function form_publish( $id ) {
//
//			$form  = Ninja_Forms()->form( $id )->get();
//			$model = Ninja_Forms()->form( $id )->get_model( $id, 'form' );
//			$key   = $model->get_setting( self::SMARTSHEET_KEY );
//
////			if (substr($key, 0, strlen(self::SMARTSHEET_KEY))  === self::SMARTSHEET_KEY) {
////				return;
////			}
//
//			$actions = Ninja_Forms()->form( $id )->get_actions();
//			$result  = $this->check_action( $actions );
//
//			if ( $result ) {
//
//				$fields               = Ninja_Forms()->form( $id )->get_fields();
//				$columns              = $this->create_column_array( $fields );
//				$form_data            = array( 'name' => $form->get_setting( 'title' ) );
//				$form_data['name']    = $form->get_setting( 'title' );
//				$form_data['columns'] = $columns;
//				$smartsheet           = $this->create_smartsheet( $form_data );
//				$model->update_setting( self::SMARTSHEET_KEY,  self::SMARTSHEET_KEY . '_' . $smartsheet->result->id )
//				      ->save();
//				$setting              = $this->process_smartsheet_columns( $fields, $smartsheet->result->columns );
//			}
//
//		}

		/**
		 * @param $actions
		 *
		 * @return bool
		 */
//		protected function check_action( $actions ) {
//			$result = false;
//			foreach ( $actions as $action ) {
//				$setting = $action->get_setting( 'type' );
//				$active  = $action->get_setting( 'active' );
//				if ( $setting == self::SMARTSHEET_KEY && $active == 1 ) {
//					$result = true;
//				}
//			}
//
//			return $result;
//		}

		/**
		 * @param $fields
		 *
		 * @return array
		 */
//		protected function create_column_array( $fields ) {
//			$columns = array();
//			foreach ( $fields as $field ) {
//				$column          = array();
//				$column['title'] = $field->get_setting( 'label' );
//				$type            = $field->get_setting( 'type' );
//				if ( $type !== 'submit' ) {
//					if ( in_array( $type, FIELD_TYPES ) ) {
//						$current_type = FIELD_TYPES[ $type ];
//					} else {
//						$current_type = 'TEXT_NUMBER';
//					}
//					$column['type'] = $current_type;
//					if ( $field->get_setting( 'order' ) == 1 ) {
//						$column['primary'] = true;
//					}
//					$columns[] = $column;
//				}
//			}
//
//			return $columns;
//		}

//		/**
//		 * @param $fields
//		 *
//		 * @return string
//		 */
//		protected function process_smartsheet_columns( $fields, $result_columns ) {
//			$smartsheet_array = $this->get_smartsheet_field_array($result_columns);
//
//			foreach ( $fields as $field ) {
//				$setting = self::SMARTSHEET_KEY . '_' . $smartsheet_array[$field->get_setting('label')];
//				$field->update_setting( self::SMARTSHEET_KEY, $setting )->save();
//			}
//
//			return $setting;
//		}

//		protected function get_smartsheet_field_array( $result_columns ) {
//			$column_array = [];
//			foreach( $result_columns as $column ) {
//				$column_array[$column->title] = $column->id;
//			}
//			$this->smartsheet_field_array_by_label = $column_array;
//			return $this->smartsheet_field_array_by_label;
//		}

//		protected function get_setting() {
//			/*
//			 * 0
//			 * id
//			 * index
//			 * title
//			 * 1
//			 */
//			/*
//			 * field
//			 * setting - label
//			 * setting - key
//			 * order
//			 * where title == label
//			 * return id
//			 */
//
//			return $setting;
//		}

		/**
		 * @param $form_data
		 *
		 * @return mixed
		 */
//		protected function create_smartsheet( $form_data ) {
//			$result = null;
//			$this->logger->info('form data', $form_data );
//			$response = $this->smartsheet->createSheet($form_data);
//			$result = \Zend\Json\Json::decode($response->getBody());
//			return $result;
//		}
	}


	/**
	 * @return NF_Smartsheet
	 */
	function NF_Smartsheet() {
		return NF_Smartsheet::instance();
	}

	NF_Smartsheet();


}

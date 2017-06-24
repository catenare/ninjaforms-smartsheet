<?php if ( ! defined( 'ABSPATH' ) || ! class_exists( 'NF_Abstracts_Action' ) ) {
	exit;
}

use SmartSheet\SmartSheet;

/**
 * Class NF_Action_SmartsheetExample
 */
final class NF_Smartsheet_Actions_Smartsheet extends NF_Abstracts_Action {
	/**
	 * @var string
	 */
	protected $_name = 'smartsheet';

	/**
	 * @var array
	 */
	protected $_tags = array();

	/**
	 * @var string
	 */
	protected $_timing = 'normal';

	/**
	 * @var int
	 */
	protected $_priority = '10';


	protected $smartsheet;

	/**
	 * NF_Smartsheet_Actions_Smartsheet constructor.
	 *
	 * @param null $smartsheet
	 */
	public function __construct( $smartsheet = null ) {
		parent::__construct();


		$this->_nicename  = __( 'Save to Smartsheet', 'ninja-forms' );
		$this->smartsheet = $smartsheet;
	}

	static function form_save( $id ) {

		$form  = Ninja_Forms()->form( $id )->get();
		$model = Ninja_Forms()->form( $id )->get_model( $id, 'form' );

		$actions = Ninja_Forms()->form( $id )->get_actions();
		$fields  = Ninja_Forms()->form( $id )->get_fields();

		$form->update_setting( 'key', 'random_value_yes' );
		$form->save();

		foreach ( $fields as $field ) {
			$field_settings = $field->get_settings();
			$setting        = 'smartsheet' . rand();
			$field->update_setting( 'smartsheet', $setting )->save();
			$current_settings = $field->get_settings();
		}
	}

	/*
	* PUBLIC METHODS
	*/

	public function save( $action_settings ) {

	}

	public function process( $action_settings, $form_id, $data ) {
		//get the form
		$form          = Ninja_Forms()->form( $form_id )->get();
		$smartsheet_id = $form->get_settings( 'key' );
		if ( isset( $smartsheet_id ) ) {
			//save the row to smartsheet.
		}

		return $data;
	}
}
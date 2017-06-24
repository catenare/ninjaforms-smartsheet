<?php if ( ! defined( 'ABSPATH' ) || ! class_exists( 'NF_Abstracts_Action' ) ) {
	exit;
}

use SmartSheet\SmartSheet;
use NinjaForm\SmartSheetRow;

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

	/**
	 * @var \SmartSheet\SmartSheet
	 */
	protected $smartsheet;

	/**
	 * NF_Smartsheet_Actions_Smartsheet constructor.
	 *
	 * @param null $smartsheet
	 */
	public function __construct( $smartsheet = null ) {
		parent::__construct();

		$this->smartsheet = $smartsheet;
		$this->_nicename  = __( 'Save to Smartsheet', 'ninja-forms' );
	}

	static function form_save( $id ) {
	}

	/*
	* PUBLIC METHODS
	*/

	public function save( $action_settings ) {

	}

	/**
	 * Save ninjaform entry to SmartSheet
	 * @param $action_settings
	 * @param $form_id
	 * @param $data
	 *
	 * @return mixed
	 */
	public function process( $action_settings, $form_id, $data ) {
		$row = new SmartSheetRow( $form_id, $data);
		$smartsheet_id = $row->getSmartSheetFormId();
		$data_row = $row->getDataRowArray();
		return $data;
	}
}
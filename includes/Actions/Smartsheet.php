<?php if ( ! defined( 'ABSPATH' ) || ! class_exists( 'NF_Abstracts_Action' ) ) {
	exit;
}

use NinjaForm\SmartSheetRow;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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
	 * @var \Monolog\Logger
	 */
	protected $logger;

	/**
	 * NF_Smartsheet_Actions_Smartsheet constructor.
	 *
	 * @param null $smartsheet
	 */
	public function __construct( $smartsheet = null ) {
		parent::__construct();

		$this->logger = new Logger( SMARTSHEET_KEY );
		$path         = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'output.log';
		$this->logger->pushHandler( new StreamHandler( $path, Logger::DEBUG ) );

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
	 *
	 * @param $action_settings
	 * @param $form_id
	 * @param $data
	 *
	 * @return mixed
	 */
	public function process( $action_settings, $form_id, $data ) {
		$row                 = new SmartSheetRow( $form_id, $data );
		$my_data             = array();
		$my_data['sheet_id'] = $row->getSmartSheetFormId();
		$my_data['data']     = $row->getDataRowArray();
		$response            = $this->smartsheet->addRows( $my_data );
		$body                = \Zend\Json\Json::decode( $response->getBody() );
		$id                  = $body->result->id;
		$data['extra']       = array( SMARTSHEET_KEY . SMARTSHEET_DELIMITER . SMARTSHEET_ID => $id );

		return $data;
	}
}
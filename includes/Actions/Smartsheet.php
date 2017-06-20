<?php if ( ! defined( 'ABSPATH' ) || ! class_exists( 'NF_Abstracts_Action' )) exit;

use SmartSheet\SmartSheet;
/**
 * Class NF_Action_SmartsheetExample
 */
final class NF_Smartsheet_Actions_Smartsheet extends NF_Abstracts_Action
{
    /**
     * @var string
     */
    protected $_name  = 'smartsheet';

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
     * Constructor
     */
    public function __construct()
{
    parent::__construct();


    $this->_nicename = __( 'Save to Smartsheet', 'ninja-forms' );
	$url = Ninja_Forms()->get_setting('api');
	$token = Ninja_Forms()->get_setting('token');
	$this->smartsheet = new SmartSheet($token, $url);
}

    /*
    * PUBLIC METHODS
    */

    public function save( $action_settings )
    {

    }

    public function process( $action_settings, $form_id, $data )
    {
    	//get the form
	    $form = Ninja_Forms()->form($form_id)->get();
	    $smartsheet_id = $form->get_settings('key');
	    if ( isset( $smartsheet_id ) ) {
	    	//save the row to smartsheet.
	    }
    	return $data;
    }
}
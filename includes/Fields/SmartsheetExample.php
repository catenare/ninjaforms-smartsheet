<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_Field_SmartsheetExample
 */
class NF_Smartsheet_Fields_SmartsheetExample extends NF_Fields_Textbox
{
    protected $_name = 'smartsheet';

    protected $_section = 'common';

    protected $_type = 'textbox';

    protected $_templates = 'textbox';

    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __( 'smartsheet Example Field', 'ninja-forms' );
    }
}
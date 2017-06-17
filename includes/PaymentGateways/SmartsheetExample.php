<?php if ( ! defined( 'ABSPATH' ) ) exit;

class NF_Smartsheet_PaymentGateways_SmartsheetExample extends NF_Abstracts_PaymentGateway
{
    protected $_slug = 'smartsheet';

    public function __construct()
    {
        parent::__construct();

        $this->_name = __( 'smartsheet Example Payment Gateway', 'ninja-forms-paypal-express' );
    }

    public function process( $action_settings, $form_id, $data )
    {
        $total = $this->get_total( $action_settings, $data );

        $data[ 'actions' ][ 'smartsheet' ] = array(
            'total' => $total,
        );

        return $data;
    }

    private function get_total( $settings, $data )
    {
        if( isset( $data[ 'new_total' ] ) && $data[ 'new_total' ] ){
            return $data[ 'new_total' ];
        } else {
            return FALSE;
        }
    }
}
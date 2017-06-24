<?php
/**
 * Created by PhpStorm.
 * User: themartins
 * Date: 2017/06/24
 * Time: 07:28
 */

namespace NinjaForm;

/**
 * Class SmartSheet
 * @package NinjaForm
 * Add new SmartSheet when creating new NinjaForm
 */

define( 'FIELD_TYPES', array(
	'checkbox' => 'CHECKBOX',
	'date'     => 'DATE',
) );

class SmartSheet {

	const SMARTSHEET_KEY = 'smartsheet';
	const ACTIVE = 1;
	const FORM = 'form';
	const TYPE = 'type';
	const TYPE_ACTIVE = 'active';
	const SUBMIT = 'submit';
	const NAME = 'name';
	const TITLE = 'title';
	const COLUMNS = 'columns';
	const ORDER = 'order';
	const PRIMARY = 'primary';
	const LABEL = 'label';
	const DEFAULT_TYPE = 'TEXT_NUMBER';

	/**
	 * @var \SmartSheet\SmartSheet
	 */
	private $smartsheet;

	/**
	 * NinjaForm fields
	 * @var
	 */
	private $fields;

	private $actions;

	private $model;

	private $form;

	private $is_new_form = NULL;

	private $smartsheet_columns = NULL;

	private $smartsheet_submit_result = NULL;



	/**
	 * @var int
	 * Form Id
	 */
	private $id;

	public function __construct(\SmartSheet\SmartSheet $smartsheet, $id) {
		$this->smartsheet = $smartsheet;
		$this->form = Ninja_Forms()->form($id)->get();
		$this->fields = Ninja_Forms()->form($id)->get_fields();
		$this->actions = Ninja_Forms()->form($id)->get_actions();
		$this->model = Ninja_Forms()->form($id)->get_model($id, self::FORM);
	}

	/**
	 * @return bool|null
	 * Check Smartsheet key to see if the form has been created in smartsheets.
	 */
	public function getIsNewForm() {
		if (is_null($this->is_new_form)) {
			$key = $this->model->get_setting(self::SMARTSHEET_KEY);
			$this->is_new_form = !(substr($key, 0, strlen(self::SMARTSHEET_KEY)) === self::SMARTSHEET_KEY);
		}
		return $this->is_new_form;
	}

	/**
	 * Check to see if smartsheet is assigned and active
	 * @param $actions
	 *
	 * @return bool
	 */
	public function getIsSmartsheetAction() {

		$result = False;
		foreach( $this->actions as $action ) {
			$setting = $action->get_setting(self::TYPE);
			$active = $action->get_setting(self::TYPE_ACTIVE);
			if ( $setting == self::SMARTSHEET_KEY && $active == self::ACTIVE) {
				$result = True;
				return $result;
			}
		}

		return $result;
	}

	/**
	 * Get current fields from form
	 * @return array
	 */
	private function getFields() {
		return $this->fields;
	}

	/**
	 * Create array of columns to store in smartsheet
	 * @return array
	 */
	private function getColumnsForSmartsheet( ) {
		$columns = array();
		foreach( $this->getFields() as $field ) {

			$type = $field->get_setting(self::TYPE);

			if( $type !== self::SUBMIT ) {
				$column = array();
				$column[self::TYPE] = $this->getFieldType($type);
				$column[self::TITLE] = $field->get_setting(self::LABEL);
				if( $field->get_setting(self::ORDER) == 1 ) {
					$column[self::PRIMARY] = true;
				}
				$columns[] = $column;
			}
		}
		return $columns;
	}

	/**
	 * @param $type
	 *
	 * @return string
	 * Determine column type for smartsheet
	 */
	private function getFieldType($type) {
		if ( in_array( $type, FIELD_TYPES)) {
			$result = FIELD_TYPES[ $type ];
		} else {
			$result = self::DEFAULT_TYPE;
		}
		return $result;
	}

	/**
	 * Get Data array to save in smartsheet
	 * @return array
	 */
	public function getDataForSmartsheet() {
		$result = [];
		$result[self::NAME] = $this->form->get_setting(self::TITLE);
		$result[self::COLUMNS] = $this->getColumnsForSmartsheet();
		return $result;
	}

	/**
	 * Call Smartsheet API
	 * @param $data
	 *
	 * @return mixed
	 *
	 */
	public function saveToSmartsheet( $data ) {
		$response = $this->smartsheet->createSheet($data);
		$result = \Zend\Json\Json::decode( $response->getBody());
		$this->setSmartsheetSubmitResult( $result );
		return $result;
	}

	private function setSmartsheetSubmitResult($result) {
		$this->smartsheet_submit_result = $result;
	}

	private function getSmartsheetSubmitResult() {
		return $this->smartsheet_submit_result;
	}

	/**
	 * Convert smartsheet columns result object to array
	 *
	 * @return array
	 */
	private function getSmartsheetColumnArray(  ) {
		$columns = $this->getSmartsheetSubmitResult()->result->columns;
		if( is_null($this->smartsheet_columns) ) {
			$result = [];
			foreach ( $columns as $column ) {
				$result[ $column->title ] = $column->id;
			}
			$this->smartsheet_columns = $result;
		}
		return $this->smartsheet_columns;

	}

	/**
	 * Update field with smartsheet column id
	 */
	public function updateColumnSettingWithSmartsheetId() {
		$columns = $this->getSmartsheetColumnArray();
		foreach( $this->fields as $field ) {
			$setting = self::SMARTSHEET_KEY . '_' . $columns[$field->get_setting(self::LABEL)];
			$field->update_setting( self::SMARTSHEET_KEY, $setting)->save();
		}
	}

	/**
	 * Update the NinjaForm with the smartsheet form id
	 */
	public function updateSheetSmartsheetId() {
		$this->model->update_setting(self::SMARTSHEET_KEY, self::SMARTSHEET_KEY . '_' . $this->getSmartsheetSubmitResult()->result->id)->save();
	}

}
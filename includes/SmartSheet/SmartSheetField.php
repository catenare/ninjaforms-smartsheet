<?php
/**
 * Created by PhpStorm.
 * User: themartins
 * Date: 2017/06/24
 * Time: 12:09
 */

namespace NinjaForm;


class SmartSheetField {

	private $id;
	private $model;

	public function __construct($field) {
		$this->id = $field['id'];
		$this->model = Ninja_Forms()->form()->get_field( $this->id);
		$this->smartsheet_id = SmartSheetRow::getSmartsheetId( $this->model->get_setting(SMARTSHEET_KEY ) );
		$this->value = $field['value'];
	}

	/**
	 * @return mixed
	 */
	public function getColumnId() {
		return $this->smartsheet_id;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}


}
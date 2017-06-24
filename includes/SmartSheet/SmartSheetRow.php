<?php
/**
 * Created by PhpStorm.
 * User: themartins
 * Date: 2017/06/24
 * Time: 07:29
 */

namespace NinjaForm;

/**
 * Class SmartSheetRow
 * @package NinjaForm
 *
 * Add new SmartSheet Row
 */

class SmartSheetRow {


	private $data;
	private $form_id;
	private $model;
	private $smartsheet_form_id;

	private $data_row = [];
	private $data_row_array = [];

	public function __construct( $form_id, $data ) {
		$this->data               = $data;
		$this->model              = Ninja_Forms()->form( $form_id )->get_model( $form_id, FORM );
		$this->smartsheet_form_id = SmartSheetRow::getSmartsheetId( $this->model->get_setting( SMARTSHEET_KEY ) );
	}

	/**
	 * @return bool|int|string
	 */
	public function getSmartSheetFormId() {
		return $this->smartsheet_form_id;
	}

	/**
	 * @return mixed
	 */
	private function getFields() {
		return $this->data[ FIELDS ];
	}

	/**
	 * @return array
	 */
	private function setDataRow() {
		$result = [];
		foreach ( $this->getFields() as $field ) {

			if ( 'submit' !== $field['settings']['type'] ) {
				$result[] = new SmartSheetField( $field );
			}
		}
		$this->data_row = $result;
		return $this->data_row;
	}

	/**
	 * @return array|null
	 */
	private function getDataRow() {
		if ( 0 == count( $this->data_row ) ) {
			$result = $this->setDataRow();
		} else {
			$result = $this->data_row;
		}

		return $result;
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function getSmartsheetId( $data ) {
		list( $key, $value ) = explode( SMARTSHEET_DELIMITER, $data );

		return $value;
	}

	/**
	 *
	 * https://api.smartsheet.com/2.0/sheets/{sheetId}/rows
	 * '[
	 * {"toTop":true, "cells": [{"columnId": 7960873114331012, "value": true}, {"columnId": 642523719853956, "value": "New status", "strict": false} ] },
	 * {"toTop":true, "cells": [ {"columnId": 7960873114331012, "value": true}, {"columnId": 642523719853956, "value": "New status", "strict": false} ] }
	 * ]'
	 * @return array
	 */
	private function setDataRowArray() {
		$result = array("toTop" => true);
		$cells  = array();
		foreach ( $this->getDataRow() as $data_field ) {
			$cells['columnId'] = $data_field->getColumnId();
			$cells['value'] = $data_field->getValue();
			$result[] = $cells;
		}
		$this->data_row_array = $result;
		return $this->data_row_array;
	}

	/**
	 * @return array
	 */
	public function getDataRowArray() {
		if ( 0 === count($this->data_row_array)  ) {
			$result = $this->setDataRowArray();
		}else{
			$result = $this->data_row_array;
		}
		return $result;
	}
}
<?php
/**
 * Created by PhpStorm.
 * User: themartins
 * Date: 2017/06/19
 * Time: 15:17
 */
require 'vendor/autoload.php';
use SmartSheet\SmartSheet;
/*
 * TOKEN=5aunobc4139vi7ax1q2seikaew
URL=https://api.smartsheet.com/2.0/
 */

$token = '5aunobc4139vi7ax1q2seikaew';
$url = 'https://api.smartsheet.com/2.0/';

$smartsheet = new SmartSheet( $token, $url);

$data['name'] = 'test_form_1';
$data['columns'] = [
	['title'=>'Favorite','type' => 'TEXT_NUMBER'],
	['title'=>'Primary Column', 'primary'=>true, 'type' => 'TEXT_NUMBER']
];

$result = $smartsheet->createSheet($data);
echo $result->getBody();



<?php
namespace App\Utils;

class Util
{
    public static function console_log($output) {
		$js_code = '<script>console.log('.json_encode($output, JSON_HEX_TAG).');</script>';
		echo $js_code;
	}
}
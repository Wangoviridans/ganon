<?php

use Wangoviridans\Ganon\HTML\Parser\Html5;
use Wangoviridans\Ganon\HTML\Formatter;

/**
 * @param $text
 * @param $indent
 * @param string $indent_string
 * @return mixed
 */
function indent_text($text, $indent, $indent_string = '  ') {
	if ($indent && $indent_string) {
		return str_replace("\n", "\n".str_repeat($indent_string, $indent), $text);
	} else {
		return $text;
	}
}

/**
 * @param $str
 * @param bool $return_root
 * @return null|string|Html5
 */
function str_get_dom($str, $return_root = true) {
	$parser = new Html5($str);
	return (($return_root) ? $parser->root : $parser);
}

/**
 * @param $file
 * @param bool $return_root
 * @param bool $use_include_path
 * @param null $context
 * @return bool|null|string|HTML5
 */
function file_get_dom($file, $return_root = true, $use_include_path = false, $context = null) {
	if (version_compare(PHP_VERSION, '5.0.0', '>='))
		$f = file_get_contents($file, $use_include_path, $context);
	else {
		if ($context !== null)
			trigger_error('Context parameter not supported in this PHP version');
		$f = file_get_contents($file, $use_include_path);
	}
	return (($f === false) ? false : str_get_dom($f, $return_root));
}

/**
 * @param $root
 * @param array $options
 * @return bool
 */
function dom_format(&$root, $options = array()) {
	$formatter = new Formatter($options);
	return $formatter->format($root);
}

if (version_compare(PHP_VERSION, '5.0.0', '<')) {
	/**
	 * @param $string
	 * @return array
	 */
	function str_split($string) {
		$res = array();
		$size = strlen($string);
		for ($i = 0; $i < $size; $i++) {
			$res[] = $string[$i];
		}
		return $res;
	}
}

if (version_compare(PHP_VERSION, '5.2.0', '<')) {
	/**
	 * @param $keys
	 * @param $value
	 * @return array
	 */
	function array_fill_keys($keys, $value) {
		$res = array();
		foreach($keys as $k) {
			$res[$k] = $value;
		}
		return $res;
	}
}

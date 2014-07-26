<?php

namespace Wangoviridans\Ganon\HTML\Node;

use Wangoviridans\Ganon\HTML\Node;

class Cdata extends Node {
	const NODE_TYPE = self::NODE_CDATA;

	public $tag = '~cdata~';
	public $text = '';

	public function __construct($parent, $text = '') {
		$this->parent = $parent;
		$this->text = $text;
	}

	protected function filter_element() {
		return false;
	}

	public function toString_attributes() {
		return '';
	}

	public function toString_content($attributes = true, $recursive = true, $content_only = false) {
		return $this->text;
	}

	public function toString($attributes = true, $recursive = true, $content_only = false) {
		return '<![CDATA[' . $this->text . ']]>';
	}
}
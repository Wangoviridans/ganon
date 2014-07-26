<?php

namespace Wangoviridans\Ganon\HTML\Node;

use Wangoviridans\Ganon\HTML\Node;

class Doctype extends Node {
	const NODE_TYPE = self::NODE_DOCTYPE;

	public $tag = '!DOCTYPE';
	public $dtd = '';

	public function __construct($parent, $dtd = '') {
		$this->parent = $parent;
		$this->dtd = $dtd;
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
		return '<' . $this->tag . ' ' . $this->dtd . '>';
	}
}
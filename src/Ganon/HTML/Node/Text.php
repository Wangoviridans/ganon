<?php

namespace Wangoviridans\Ganon\HTML\Node;

use Wangoviridans\Ganon\HTML\Node;

class Text extends Node {
	const NODE_TYPE = self::NODE_TEXT;

	public $tag = '~text~';
	public $text = '';

	public function __construct($parent, $text = '') {
		$this->parent = $parent;
		$this->text = $text;
	}

	public function isText() {
		return true;
	}

	public function isTextOrComment() {
		return true;
	}

	protected function filter_element() {
		return false;
	}

	protected function filter_text() {
		return true;
	}

	public function toString_attributes() {
		return '';
	}

	public function toString_content($attributes = true, $recursive = true, $content_only = false) {
		return $this->text;
	}

	public function toString($attributes = true, $recursive = true, $content_only = false) {
		return $this->text;
	}
}
<?php

namespace Wangoviridans\Ganon\HTML\Node;

use Wangoviridans\Ganon\HTML\Node;

class Conditional extends Node {
	const NODE_TYPE = self::NODE_CONDITIONAL;

	public $tag = '~conditional~';
	public $condition = '';

	public function __construct($parent, $condition = '', $hidden = true) {
		$this->parent = $parent;
		$this->hidden = $hidden;
		$this->condition = $condition;
	}

	protected function filter_element() {
		return false;
	}

	public function toString_attributes() {
		return '';
	}

	public function toString($attributes = true, $recursive = true, $content_only = false) {
		if ($content_only) {
			if (is_int($content_only)) {
				--$content_only;
			}
			return $this->toString_content($attributes, $recursive, $content_only);
		}
		$s = '<!' . (($this->hidden) ? '--' : '') . '[' . $this->condition . ']>';
		if ($recursive) {
			$s .= $this->toString_content($attributes);
		}
		$s .= '<![endif]' . (($this->hidden) ? '--' : '') . '>';
		return $s;
	}
}
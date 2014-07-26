<?php

namespace Wangoviridans\Ganon\HTML;

use Wangoviridans\Ganon\HTML\Parser\Base;
use Wangoviridans\Ganon\HTML\Node;

class Parser extends Base {
	public $root = '\Wangoviridans\Ganon\HTML\Node';
	public $hierarchy = array();
	public $tags_selfclose = array(
		'area'		=> true,
		'base'		=> true,
		'basefont'	=> true,
		'br'		=> true,
		'col'		=> true,
		'command'	=> true,
		'embed'		=> true,
		'frame'		=> true,
		'hr'		=> true,
		'img'		=> true,
		'input'		=> true,
		'ins'		=> true,
		'keygen'	=> true,
		'link'		=> true,
		'meta'		=> true,
		'param'		=> true,
		'source'	=> true,
		'track'		=> true,
		'wbr'		=> true
	);

	public function __construct($doc = '', $pos = 0, $root = null) {
		if ($root === null) {
			$root = new $this->root('~root~', null);
		}
		$this->root =& $root;
		parent::__construct($doc, $pos);
	}

	public function __invoke($query = '*') {
		return $this->select($query);
	}

	public function __toString() {
		return strval($this->root->getInnerText());
	}

	public function select($query = '*', $index = false, $recursive = true, $check_self = false) {
		return $this->root->select($query, $index, $recursive, $check_self);
	}

	protected function parse_hierarchy($self_close = null) {
		if ($self_close === null) {
			$this->status['self_close'] = ($self_close = isset($this->tags_selfclose[strtolower($this->status['tag_name'])]));
		}
		if ($self_close) {
			if ($this->status['closing_tag']) {
				$c = $this->hierarchy[count($this->hierarchy) - 1]->children;
				$found = false;
				for ($count = count($c), $i = $count - 1; $i >= 0; $i--) {
					if (strcasecmp($c[$i]->tag, $this->status['tag_name']) === 0) {
						for($ii = $i + 1; $ii < $count; $ii++) {
							$index = null;
							$c[$i + 1]->changeParent($c[$i], $index);
						}
						$c[$i]->self_close = false;
						$found = true;
						break;
					}
				}
				if (!$found) {
					$this->addError('Closing tag "'.$this->status['tag_name'].'" which is not open');
				}
			} elseif ($this->status['tag_name'][0] === '?') {
				$index = null;
				$this->hierarchy[count($this->hierarchy) - 1]->addXML($this->status['tag_name'], '', $this->status['attributes'], $index);
			} elseif ($this->status['tag_name'][0] === '%') {
				$index = null;
				$this->hierarchy[count($this->hierarchy) - 1]->addASP($this->status['tag_name'], '', $this->status['attributes'], $index);
			} else {
				$index = null;
				$this->hierarchy[count($this->hierarchy) - 1]->addChild($this->status, $index);
			}
		} elseif ($this->status['closing_tag']) {
			$found = false;
			for ($count = count($this->hierarchy), $i = $count - 1; $i >= 0; $i--) {
				if (strcasecmp($this->hierarchy[$i]->tag, $this->status['tag_name']) === 0) {
					for($ii = ($count - $i - 1); $ii >= 0; $ii--) {
						$e = array_pop($this->hierarchy);
						if ($ii > 0) {
							$this->addError('Closing tag "'.$this->status['tag_name'].'" while "'.$e->tag.'" is not closed yet');
						}
					}
					$found = true;
					break;
				}
			}
			if (!$found) {
				$this->addError('Closing tag "'.$this->status['tag_name'].'" which is not open');
			}
		} else {
			$index = null;
			$this->hierarchy[] = $this->hierarchy[count($this->hierarchy) - 1]->addChild($this->status, $index);
		}
	}

	public function parse_cdata() {
		if (!parent::parse_cdata()) {return false;}
		$index = null;
		$this->hierarchy[count($this->hierarchy) - 1]->addCDATA($this->status['cdata'], $index);
		return true;
	}

	public function parse_comment() {
		if (!parent::parse_comment()) {return false;}
		$index = null;
		$this->hierarchy[count($this->hierarchy) - 1]->addComment($this->status['comment'], $index);
		return true;
	}

	public function parse_conditional() {
		if (!parent::parse_conditional()) {return false;}
		if ($this->status['comment']) {
			$index = null;
			$e = $this->hierarchy[count($this->hierarchy) - 1]->addConditional($this->status['tag_condition'], true, $index);
			if ($this->status['text'] !== '') {
				$index = null;
				$e->addText($this->status['text'], $index);
			}
		} else {
			if ($this->status['closing_tag']) {
				$this->parse_hierarchy(false);
			} else {
				$index = null;
				$this->hierarchy[] = $this->hierarchy[count($this->hierarchy) - 1]->addConditional($this->status['tag_condition'], false, $index);
			}
		}
		return true;
	}

	public function parse_doctype() {
		if (!parent::parse_doctype()) {return false;}
		$index = null;
		$this->hierarchy[count($this->hierarchy) - 1]->addDoctype($this->status['dtd'], $index);
		return true;
	}

	public function parse_php() {
		if (!parent::parse_php()) {return false;}
		$index = null;
		$this->hierarchy[count($this->hierarchy) - 1]->addXML('php', $this->status['text'], $index);
		return true;
	}

	public function parse_asp() {
		if (!parent::parse_asp()) {return false;}
		$index = null;
		$this->hierarchy[count($this->hierarchy) - 1]->addASP('', $this->status['text'], $index);
		return true;
	}

	public function parse_script() {
		if (!parent::parse_script()) {return false;}
		$index = null;
		$e = $this->hierarchy[count($this->hierarchy) - 1]->addChild($this->status, $index);
		if ($this->status['text'] !== '') {
			$index = null;
			$e->addText($this->status['text'], $index);
		}
		return true;
	}

	public function parse_style() {
		if (!parent::parse_style()) {return false;}
		$index = null;
		$e = $this->hierarchy[count($this->hierarchy) - 1]->addChild($this->status, $index);
		if ($this->status['text'] !== '') {
			$index = null;
			$e->addText($this->status['text'], $index);
		}
		return true;
	}

	public function parse_tag_default() {
		if (!parent::parse_tag_default()) {return false;}
		$this->parse_hierarchy(($this->status['self_close']) ? true : null);
		return true;
	}

	public function parse_text() {
		parent::parse_text();
		if ($this->status['text'] !== '') {
			$index = null;
			$this->hierarchy[count($this->hierarchy) - 1]->addText($this->status['text'], $index);
		}
	}
	
	public function parse_all() {
		$this->hierarchy = array(&$this->root);
		return ((parent::parse_all()) ? $this->root : false);
	}
}
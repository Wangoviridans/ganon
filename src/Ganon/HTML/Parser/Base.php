<?php

namespace Wangoviridans\Ganon\HTML\Parser;

use Wangoviridans\Ganon\Tokenizer\Base as Tokenizer_Base;

class Base extends Tokenizer_Base {
	const TOK_TAG_OPEN = 100;
	const TOK_TAG_CLOSE = 101;
	const TOK_SLASH_FORWARD = 103;
	const TOK_SLASH_BACKWARD = 104;
	const TOK_STRING = 104;
	const TOK_EQUALS = 105;
	
	public $identifiers = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890:-_!?%';
	public $status = array();
	public $custom_char_map = array(
		'<' => self::TOK_TAG_OPEN,
		'>' => self::TOK_TAG_CLOSE,
		"'" => 'parse_string',
		'"' => 'parse_string',
		'/' => self::TOK_SLASH_FORWARD,
		'\\' => self::TOK_SLASH_BACKWARD,
		'=' => self::TOK_EQUALS
	);
	
	public function __construct($doc = '', $pos = 0) {
		parent::__construct($doc, $pos);
		$this->parse_all();
	}

	public $tag_map = array(
		'!doctype' => 'parse_doctype',
		'?' => 'parse_php',
		'?php' => 'parse_php',
		'%' => 'parse_asp',
		'style' => 'parse_style',
		'script' => 'parse_script'
	);

	protected function parse_string() {
		if ($this->next_pos($this->doc[$this->pos], false) !== self::TOK_UNKNOWN) {
			--$this->pos;
		}
		return self::TOK_STRING;
	}

	public function parse_text() {
		$len = $this->pos - 1 - $this->status['last_pos'];
		$this->status['text'] = (($len > 0) ? substr($this->doc, $this->status['last_pos'] + 1, $len) : '');
	}

	public function parse_comment() {
		$this->pos += 3;
		if ($this->next_pos('-->', false) !== self::TOK_UNKNOWN) {
			$this->status['comment'] = $this->getTokenString(1, -1);
			--$this->pos;
		} else {
			$this->status['comment'] = $this->getTokenString(1, -1);
			$this->pos += 2;
		}
		$this->status['last_pos'] = $this->pos;
		return true;
	}

	public function parse_doctype() {
		$start = $this->pos;
		if ($this->next_search('[>', false) === self::TOK_UNKNOWN)  {
			if ($this->doc[$this->pos] === '[') {
				if (($this->next_pos(']', false) !== self::TOK_UNKNOWN) || ($this->next_pos('>', false) !== self::TOK_UNKNOWN)) {
					$this->addError('Invalid doctype');
					return false;
				}
			}
			$this->token_start = $start;
			$this->status['dtd'] = $this->getTokenString(2, -1);
			$this->status['last_pos'] = $this->pos;
			return true;
		} else {
			$this->addError('Invalid doctype');
			return false;
		}
	}

	public function parse_cdata() {
		if ($this->next_pos(']]>', false) === self::TOK_UNKNOWN) {
			$this->status['cdata'] = $this->getTokenString(9, -1);
			$this->status['last_pos'] = $this->pos + 2;
			return true;
		} else {
			$this->addError('Invalid cdata tag');
			return false;
		}
	}

	public function parse_php() {
		$start = $this->pos;
		if ($this->next_pos('?>', false) !== self::TOK_UNKNOWN) {
			$this->pos -= 2;
		}
		$len = $this->pos - 1 - $start;
		$this->status['text'] = (($len > 0) ? substr($this->doc, $start + 1, $len) : '');
		$this->status['last_pos'] = ++$this->pos;
		return true;
	}

	public function parse_asp() {
		$start = $this->pos;
		if ($this->next_pos('%>', false) !== self::TOK_UNKNOWN) {
			$this->pos -= 2;
		}
		$len = $this->pos - 1 - $start;
		$this->status['text'] = (($len > 0) ? substr($this->doc, $start + 1, $len) : '');
		$this->status['last_pos'] = ++$this->pos;
		return true;
	}

	public function parse_style() {
		if ($this->parse_attributes() && ($this->token === self::TOK_TAG_CLOSE) && ($start = $this->pos) && ($this->next_pos('</style>', false) === self::TOK_UNKNOWN)) {
			$len = $this->pos - 1 - $start;
			$this->status['text'] = (($len > 0) ? substr($this->doc, $start + 1, $len) : '');
			$this->pos += 7;
			$this->status['last_pos'] = $this->pos;
			return true;
		} else {
			$this->addError('No end for style tag found');
			return false;
		}
	}

	public function parse_script() {
		if ($this->parse_attributes() && ($this->token === self::TOK_TAG_CLOSE) && ($start = $this->pos) && ($this->next_pos('</script>', false) === self::TOK_UNKNOWN)) {
			$len = $this->pos - 1 - $start;
			$this->status['text'] = (($len > 0) ? substr($this->doc, $start + 1, $len) : '');
			$this->pos += 8;
			$this->status['last_pos'] = $this->pos;
			return true;
		} else {
			$this->addError('No end for script tag found');
			return false;
		}
	}

	public function parse_conditional() {
		if ($this->status['closing_tag']) {
			$this->pos += 8;
		} else {
			$this->pos += (($this->status['comment']) ? 5 : 3);
			if ($this->next_pos(']', false) !== self::TOK_UNKNOWN) {
				$this->addError('"]" not found in conditional tag');
				return false;
			}
			$this->status['tag_condition'] = $this->getTokenString(0, -1);
		}
		if ($this->next_no_whitespace() !== self::TOK_TAG_CLOSE) {
			$this->addError('No ">" tag found 2 for conditional tag');
			return false;
		}
		if ($this->status['comment']) {
			$this->status['last_pos'] = $this->pos;
			if ($this->next_pos('-->', false) !== self::TOK_UNKNOWN) {
				$this->addError('No ending tag found for conditional tag');
				$this->pos = $this->size - 1;
				$len = $this->pos - 1 - $this->status['last_pos'];
				$this->status['text'] = (($len > 0) ? substr($this->doc, $this->status['last_pos'] + 1, $len) : '');
			} else {
				$len = $this->pos - 10 - $this->status['last_pos'];
				$this->status['text'] = (($len > 0) ? substr($this->doc, $this->status['last_pos'] + 1, $len) : '');
				$this->pos += 2;
			}
		}
		$this->status['last_pos'] = $this->pos;
		return true;
	}

	public function parse_attributes() {
		$this->status['attributes'] = array();
		while ($this->next_no_whitespace() === self::TOK_IDENTIFIER) {
			$attr = $this->getTokenString();
			if (($attr === '?') || ($attr === '%')) {
				break;
			}
			if ($this->next_no_whitespace() === self::TOK_EQUALS) {
				if ($this->next_no_whitespace() === self::TOK_STRING) {
					$val = $this->getTokenString(1, -1);
				} else {
					if (!isset($stop)) {
						$stop = $this->whitespace;
						$stop['<'] = true;
						$stop['>'] = true;
					}
					while ((++$this->pos < $this->size) && (!isset($stop[$this->doc[$this->pos]]))) {}
					--$this->pos;
					$val = $this->getTokenString();
					if (trim($val) === '') {
						$this->addError('Invalid attribute value');
						return false;
					}
				}
			} else {
				$val = $attr;
				$this->pos = (($this->token_start) ? $this->token_start : $this->pos) - 1;
			}
			$this->status['attributes'][$attr] = $val;
		}
		return true;
	}

	public function parse_tag_default() {
		if ($this->status['closing_tag']) {
			$this->status['attributes'] = array();
			$this->next_no_whitespace();
		} else {
			if (!$this->parse_attributes()) {
				return false;
			}
		}
		if ($this->token !== self::TOK_TAG_CLOSE) {
			if ($this->token === self::TOK_SLASH_FORWARD) {
				$this->status['self_close'] = true;
				$this->next();
			} elseif ((($this->status['tag_name'][0] === '?') && ($this->doc[$this->pos] === '?')) || (($this->status['tag_name'][0] === '%') && ($this->doc[$this->pos] === '%'))) {
				$this->status['self_close'] = true;
				$this->pos++;
				if (isset($this->char_map[$this->doc[$this->pos]]) && (!is_string($this->char_map[$this->doc[$this->pos]]))) {
					$this->token = $this->char_map[$this->doc[$this->pos]];
				} else {
					$this->token = self::TOK_UNKNOWN;
				}
			}
		}
		if ($this->token !== self::TOK_TAG_CLOSE) {
			$this->addError('Expected ">", but found "'.$this->getTokenString().'"');
			if ($this->next_pos('>', false) !== self::TOK_UNKNOWN) {
				$this->addError('No ">" tag found for "'.$this->status['tag_name'].'" tag');
				return false;
			}
		}
		return true;
	}

	public function parse_tag() {
		$start = $this->pos;
		$this->status['self_close'] = false;
		$this->parse_text();
		$next = (($this->pos + 1) < $this->size) ? $this->doc[$this->pos + 1] : '';
		if ($next === '!') {
			$this->status['closing_tag'] = false;
			if (substr($this->doc, $this->pos + 2, 2) === '--') {
				$this->status['comment'] = true;
				if (($this->doc[$this->pos + 4] === '[') && (strcasecmp(substr($this->doc, $this->pos + 5, 2), 'if') === 0)) {
					return $this->parse_conditional();
				} else {
					return $this->parse_comment();
				}
			} else {
				$this->status['comment'] = false;
				if ($this->doc[$this->pos + 2] === '[') {
					if (strcasecmp(substr($this->doc, $this->pos + 3, 2), 'if') === 0) {
						return $this->parse_conditional();
					} elseif (strcasecmp(substr($this->doc, $this->pos + 3, 5), 'endif') === 0) {
						$this->status['closing_tag'] = true;
						return $this->parse_conditional();
					} elseif (strcasecmp(substr($this->doc, $this->pos + 3, 5), 'cdata') === 0) {
						return $this->parse_cdata();
					}
				}
			}
		} elseif ($next === '/') {
			$this->status['closing_tag'] = true;
			++$this->pos;
		} else {
			$this->status['closing_tag'] = false;
		}
		if ($this->next() !== self::TOK_IDENTIFIER) {
			$this->addError('Tagname expected');
			$this->status['last_pos'] = $start - 1;
			return true;
		}
		$tag = $this->getTokenString();
		$this->status['tag_name'] = $tag;
		$tag = strtolower($tag);
		if (isset($this->tag_map[$tag])) {
			$res = $this->{$this->tag_map[$tag]}();
		} else {
			$res = $this->parse_tag_default();
		}
		$this->status['last_pos'] = $this->pos;
		return $res;
	}

	public function parse_all() {
		$this->errors = array();
		$this->status['last_pos'] = -1;
		if (($this->token === self::TOK_TAG_OPEN) || ($this->next_pos('<', false) === self::TOK_UNKNOWN)) {
			do {
				if (!$this->parse_tag()) {
					return false;
				}
			} while ($this->next_pos('<') !== self::TOK_NULL);
		}
		$this->pos = $this->size;
		$this->parse_text();
		return true;
	}
}
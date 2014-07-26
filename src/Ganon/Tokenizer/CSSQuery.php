<?php

namespace Wangoviridans\Ganon\Tokenizer;

class CSSQuery extends Base {
	const TOK_BRACKET_OPEN = 100;
	const TOK_BRACKET_CLOSE = 101;
	const TOK_BRACE_OPEN = 102;
	const TOK_BRACE_CLOSE = 103;
	const TOK_STRING = 104;
	const TOK_COLON = 105;
	const TOK_COMMA = 106;
	const TOK_NOT = 107;
	const TOK_ALL = 108;
	const TOK_PIPE = 109;
	const TOK_PLUS = 110;
	const TOK_SIBLING = 111;
	const TOK_CLASS = 112;
	const TOK_ID = 113;
	const TOK_CHILD = 114;
	const TOK_COMPARE_PREFIX = 115;
	const TOK_COMPARE_CONTAINS = 116;
	const TOK_COMPARE_CONTAINS_WORD = 117;
	const TOK_COMPARE_ENDS = 118;
	const TOK_COMPARE_EQUALS = 119;
	const TOK_COMPARE_NOT_EQUAL = 120;
	const TOK_COMPARE_BIGGER_THAN = 121;
	const TOK_COMPARE_SMALLER_THAN = 122;
	const TOK_COMPARE_REGEX = 123;
	const TOK_COMPARE_STARTS = 124;
	
	public $identifiers = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890_-?';
	public $custom_char_map = array(
		'.' => self::TOK_CLASS,
		'#' => self::TOK_ID,
		',' => self::TOK_COMMA,
		'>' => 'parse_gt',
		'+' => self::TOK_PLUS,
		'~' => 'parse_sibling',
		'|' => 'parse_pipe',
		'*' => 'parse_star',
		'$' => 'parse_compare',
		'=' => self::TOK_COMPARE_EQUALS,
		'!' => 'parse_not',
		'%' => 'parse_compare',
		'^' => 'parse_compare',
		'<' => 'parse_compare',
		'"' => 'parse_string',
		"'" => 'parse_string',
		'(' => self::TOK_BRACE_OPEN,
		')' => self::TOK_BRACE_CLOSE,
		'[' => self::TOK_BRACKET_OPEN,
		']' => self::TOK_BRACKET_CLOSE,
		':' => self::TOK_COLON
	);

	protected function parse_gt() {
		if ((($this->pos + 1) < $this->size) && ($this->doc[$this->pos + 1] === '=')) {
			++$this->pos;
			return ($this->token = self::TOK_COMPARE_BIGGER_THAN);
		} else {
			return ($this->token = self::TOK_CHILD);
		}
	}

	protected function parse_sibling() {
		if ((($this->pos + 1) < $this->size) && ($this->doc[$this->pos + 1] === '=')) {
			++$this->pos;
			return ($this->token = self::TOK_COMPARE_CONTAINS_WORD);
		} else {
			return ($this->token = self::TOK_SIBLING);
		}
	}

	protected function parse_pipe() {
		if ((($this->pos + 1) < $this->size) && ($this->doc[$this->pos + 1] === '=')) {
			++$this->pos;
			return ($this->token = self::TOK_COMPARE_PREFIX);
		} else {
			return ($this->token = self::TOK_PIPE);
		}
	}

	protected function parse_star() {
		if ((($this->pos + 1) < $this->size) && ($this->doc[$this->pos + 1] === '=')) {
			++$this->pos;
			return ($this->token = self::TOK_COMPARE_CONTAINS);
		} else {
			return ($this->token = self::TOK_ALL);
		}
	}

	protected function parse_not() {
		if ((($this->pos + 1) < $this->size) && ($this->doc[$this->pos + 1] === '=')) {
			++$this->pos;
			return ($this->token = self::TOK_COMPARE_NOT_EQUAL);
		} else {
			return ($this->token = self::TOK_NOT);
		}
	}

	protected function parse_compare() {
		if ((($this->pos + 1) < $this->size) && ($this->doc[$this->pos + 1] === '=')) {
			switch ($this->doc[$this->pos++]) {
				case '$':
					return ($this->token = self::TOK_COMPARE_ENDS);
				case '%':
					return ($this->token = self::TOK_COMPARE_REGEX);
				case '^':
					return ($this->token = self::TOK_COMPARE_STARTS);
				case '<':
					return ($this->token = self::TOK_COMPARE_SMALLER_THAN);
			}
		}
		return false;
	}

	protected function parse_string() {
		$char = $this->doc[$this->pos];
		while (true) {
			if ($this->next_search($char . '\\', false) !== self::TOK_NULL) {
				if ($this->doc[$this->pos] === $char) {
					break;
				} else {
					++$this->pos;
				}
			} else {
				$this->pos = $this->size - 1;
				break;
			}
		}
		return ($this->token = self::TOK_STRING);
	}
}

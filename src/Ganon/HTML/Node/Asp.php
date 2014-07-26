<?php

namespace Wangoviridans\Ganon\HTML\Node;

use Wangoviridans\Ganon\HTML\Node;

class Asp extends Embedded {
	const NODE_TYPE = self::NODE_ASP;

	public function __construct($parent, $tag = '', $text = '', $attributes = array()) {
		return parent::__construct($parent, '%', $tag, $text, $attributes);
	}
}
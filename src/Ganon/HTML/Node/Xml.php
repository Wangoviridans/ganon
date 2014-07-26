<?php

namespace Wangoviridans\Ganon\HTML\Node;

use Wangoviridans\Ganon\HTML\Node;

class Xml extends Embedded {
	const NODE_TYPE = self::NODE_XML;
	function __construct($parent, $tag = 'xml', $text = '', $attributes = array()) {
		return parent::__construct($parent, '?', $tag, $text, $attributes);
	}
}

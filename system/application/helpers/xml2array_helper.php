<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/** 
 * XML2Array Helper
 * uses domxml to parse a xml string to array
 *
 * @copyright  Copyright (c) TotalCenter
 * @version    $Id: index.php 0.1 2009-02-19 eurico $
**/

function xml2array($xml, $include_root = TRUE, $include_attribs = FALSE) {
	$xmldoc = new DOMDocument();
	$xmldoc->preserveWhiteSpace = FALSE;
	$xmldoc->loadXML($xml);
	$parsed_xml = parse_xml_node($xmldoc, $include_attribs);
	return $include_root ? $parsed_xml : reset($parsed_xml);
}


function parse_xml_node($n, $attribs = FALSE) {
    $return = array();
    foreach($n->childNodes as $nc) {
		($nc->hasChildNodes()) ?
		($n->firstChild->nodeName == $n->lastChild->nodeName && $n->childNodes->length > 1) ?
		$return[$nc->nodeName][] = parse_xml_node($nc, $attribs) :
		$return[$nc->nodeName] = parse_xml_node($nc, $attribs) :
		(($nc->nodeValue == NULL) ? $return[$nc->nodeName] = NULL : $return = $nc->nodeValue);
		if ($attribs && $nc->hasAttributes()) {
			foreach ($nc->attributes as $at)
				$return[$nc->nodeName][$at->name] = $at->value;
		}	
	}
    return $return;
}

// ------------------------------------------------------------------------
/* End of file xml2array_helper.php */
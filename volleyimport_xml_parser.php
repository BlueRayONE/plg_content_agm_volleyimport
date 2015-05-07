<?php
/**
 * @package    volleyImport
 * @subpackage Tools
 * @author     Florian Merkert
 * @author     Created on 05-May-2015
 * @license    GNU/GPL
 */

require_once('volleyimport_xml_data.php');

abstract class XML_Parser {
	protected $url = '';

	function __construct($url) {
		$this->url = $url;
	}

	public function get_data() {
		// TODO: caching ?
		$xml = $this->fetch_content();
		return $this->parse_content($xml);
	}

	// Some data importer might want to sort data ?!
	// default: Use same order as XML file
	public function get_sorted_data($sort_function) {
		return $this->get_data();
	}

	protected function fetch_content() {
		$context  = stream_context_create(array('https' => array('header' => 'Accept: application/xml')));
		$xml = file_get_contents($this->url, false, $context);
		return simplexml_load_string($xml);
	}

	abstract protected function parse_content($xml);
}

class ErgebnisXML_Parser extends XML_Parser {

	function parse_content($xml) {
		$ergebnisse = array();
		foreach($xml as $element => $ergebnis) {
			$ergebnisse[] = new Ergebnis($ergebnis->nr, $ergebnis->datum, $ergebnis->hallenoeffnung,
			             $ergebnis->spielbeginn, $ergebnis->heim, $ergebnis->gast,
						 $ergebnis->sheim, $ergebnis->sgast, $ergebnis->result);
		}
		return $ergebnisse;
	}
}

?>

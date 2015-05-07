<?php
/**
 * @package    volleyImport
 * @subpackage Tools
 * @author     Florian Merkert
 * @author     Created on 07-May-2015
 * @license    GNU/GPL
 */

require_once('volleyimport_xml_data.php');

abstract class View {

	protected $displayable_data = False;

	function __construct($displayable_data = False) {
		// displayable data contains the names of the columns that are shown
		$this->displayable_data = $displayable_data;
	}


	public abstract function create_header();
	public abstract function create_row($data);
	public abstract function create_footer();
}

class ErgebnisView extends View {

	protected $header_map_order = array ("Nr", "Datum", "Hallenöffnung",
										"Spielbeginn", "Heim", "Gast",
			                            "Sätze Heim", "Sätze Gast", "Resultat");
	protected $header_map = array ("Nr" => "nr",
								  "Datum" => "datum",
								  "Hallenöffnung" => "hallenoeffnung",
								  "Spielbeginn" => "spielbeginn",
			                      "Heim" => "heim",
								  "Gast" => "gast",
			                      "Sätze Heim" => "sheim",
								  "Sätze Gast" => "sgast",
			                      "Resultat" => "result");

	protected $header_row = "";
	protected $header_header = "";
	protected $displayable_data = "";
	function __construct($displayable_data = False) {
		$this->displayable_data = $displayable_data;
		if($displayable_data) {
			$this->header_row = "|";
			foreach($displayable_data as $data) {
				$this->header_row .= " ".$data." |";
			}
		} else {
			$this->header_row = "|";
			foreach($this->header_map_order as $data) {
				$this->header_row .= " ".$data." |";
			}
		}
		$this->header_header = "+";
		for ($i = 0; $i < strlen($this->header_row) - 5; $i++) {
			$this->header_header .= "-";
		}
		$this->header_header .= "+";

	}

	public function create_header() {
		$header = "";
		$header .= $this->header_header . "\n";
		$header .= $this->header_row . "\n";
		$header .= $this->header_header . "\n";
		return $header;
	}

	public function create_row($data) {
		$row = "|";
		//print $data->nr;
		//var_dump($data);
		if($this->displayable_data) {
			foreach($this->displayable_data as $key) {
				$k = $this->header_map[$key];
				$row .= " ".$data->$k." |";
			}
		} else {
			foreach($this->header_map_order as $key) {
				$k = $this->header_map[$key];
				$row .= " ".$data->$k." |";
			}
		}
		return $row;
	}

	public function create_footer() {
		$footer = "";
		$footer .= $this->header_header;
		return $footer;
	}
}

// TODO: class ErgebnisHTMLView

?>
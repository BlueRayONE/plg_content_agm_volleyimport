<?php
/**
 * @package    volleyImport
 * @subpackage Tools
 * @author     Florian Merkert
 * @author     Created on 07-May-2015
 * @license    GNU/GPL
 */
class Ergebnis {

	function __construct($nr, $datum, $hallenoeffnung,
			             $spielbeginn, $heim, $gast,
						 $sheim, $sgast, $result) {
		$this->nr = $nr;
		$this->datum = $datum;
		$this->hallenoeffnung = $hallenoeffnung;
		$this->spielbeginn = $spielbeginn;
		$this->heim = $heim;
		$this->gast = $gast;
		$this->sheim = $sheim;
		$this->sgast = $sgast;
		$this->result = $result;
	}

	public $nr = '';
	public $datum = '';
	public $hallenoeffnung = '';
	public $spielbeginn = '';
	public $heim = '';
	public $gast = '';
	public $sheim = '';
	public $sgast = '';
	public $result = '';
}

?>

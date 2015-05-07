<?php
/**
 * @package    volleyImport
 * @subpackage Tools
 * @author     Florian Merkert
 * @author     Created on 07-May-2015
 * @license    GNU/GPL
 */
require_once('../volleyimport_xml_parser.php');
require_once('../volleyimport_data_view.php');

class XMLParserTest extends PHPUnit_Framework_TestCase {

	// TODO: save test xml file in "test" folder? Online available xml could change ...

	public function testHeaderCreation() {

		$ergebnis_viewer = new ErgebnisView();
		//default ErgebnisView contains all elements
		$header = $ergebnis_viewer->create_header();
		$all_data = array('Nr', 'Datum', 'Hallenöffnung', 'Spielbeginn',
				 			'Heim', 'Gast', 'Sätze Heim', 'Sätze Gast', 'Resultat'
		);
		foreach($all_data as $data ) {
			$this->assertEquals(True, strpos($header, $data), "All data: header does not contain ".$data);
		}
		$some_data = array('Datum', 'Heim', 'Gast', 'Sätze Heim', 'Sätze Gast');
		$missing_data = array_diff($all_data, $some_data);
		$ergebnis_viewer = new ErgebnisView($some_data);
		$header = $ergebnis_viewer->create_header();
		foreach($some_data as $data ) {
			$this->assertEquals(True, strpos($header, $data), "Some data: header does not contain ".$data);
		}
		foreach($missing_data as $data ) {
			$this->assertFalse(strpos($header, $data), "Header contains ".$data);
		}

	}

	public function testFooterCreation() {
		$ergebnis_viewer = new ErgebnisView();
		$this->assertGreaterThan(0, strlen($ergebnis_viewer->create_footer()), "At least the footer should not be empty on console");
	}

	public function testNumberOfRows () {
		$xml_parser = new ErgebnisXML_Parser('https://vlw.it4sport.de/data/vbwb/aufsteiger/public/ergebnis_2014_2.xml');
		$handcounted = 6;
		$count = 0;
		$ergebnis_viewer = new ErgebnisView();
		foreach($xml_parser->get_data() as $ergebnis) {
			$this->assertEquals(10, substr_count($ergebnis_viewer->create_row($ergebnis), "|"), "All data: Row contains wrong number of items");
			$count++;
		}
		$this->assertEquals($handcounted, $count, "Number of elements in XML does not equal elements in XML Parser");

		$count = 0;
		$some_data = array('Datum', 'Heim', 'Gast', 'Sätze Heim', 'Sätze Gast');
		$ergebnis_viewer = new ErgebnisView($some_data);
		foreach($xml_parser->get_data() as $ergebnis) {
			$this->assertEquals(count($some_data) + 1, substr_count($ergebnis_viewer->create_row($ergebnis), "|"), "Some data: Row contains wrong number of items");
			$count++;
		}
		$this->assertEquals($handcounted, $count, "Number of elements in XML does not equal elements in XML Parser");
	}
}

?>

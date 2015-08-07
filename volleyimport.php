<?php
/**
 * @package    volleyImport
 * @subpackage Base
 * @author     Alexander Grözinger {@link http://www.agmedia.de}
 * @author     Created on 07-Aug-2015
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

//Import der Joomla Plugin Klasse
jimport('joomla.plugin.plugin');

//Import der Helfer
require_once(JPATH_PLUGINS . '/content/volleyimport/helpers/html.table.class.php');

/**
 * Content Plugin.
 *
 * @package    volleyImport
 * @subpackage Plugin
 */
class plgContentvolleyImport extends JPlugin
{
    /**
     * [SYSTEMVARIABLEN]
     * @var $_app      Enthält JFactory::getApplication()
     * @var $_doc      Enthält JFactory::getDocument()
     * @var $_request  Enthält JFactory::getApplication()->input
     */
    protected $_app;
    protected $_doc;
    protected $_request;

    /**
     * @var int $_table_id Eindeutige ID für das spätere HTML Elemente
     * @var string $_vi_stream Wird mit XML-Stream URL befüllt
     */
    protected $_table_id = 1;
    protected $_vi_stream = "";
    protected $_vi_copyright = true;

    /**
     * [PLUGIN SETTINGS]
     * @var bool $_vi_highlight Soll das eigene Team farbig markiert werden
     * @var string $_vi_verein Enthält einen Teil des Vereinsnames
     * @var bool $_vi_jqueryui_load Soll das jQueryUI Plugin geladen werden. Wird für Themes benutzt
     * @var string $_vi_jqueryui_theme Welches Theme soll geladen werden. Cupertino ist standard.
     * @var array $_allowed_jqueryui_themes Enthällt alle zugelassenen Theme-Namen
     * @var integer $_table_mode Anzeigemodus für Tabelle
     */
    protected $_vi_highlight = false;
    protected $_vi_verein = "";
    protected $_vi_jqueryui_load = true;
    protected $_vi_alternating_rows = true;
    protected $_vi_jqueryui_theme = 'cupertino';
    protected $_allowed_jqueryui_themes = array(
        "black-tie",
        "blitzer",
        "cupertino",
        "dark-hive",
        "dot-luv",
        "eggplant",
        "excite-bike",
        "flick",
        "hot-sneaks",
        "humanity",
        "le-frog",
        "mint-choc",
        "overcast",
        "pepper-grinder",
        "redmond",
        "smoothness",
        "south-street",
        "start",
        "sunny",
        "swanky-purse",
        "trontastic",
        "ui-darkness",
        "ui-lightness",
        "vader"
    );

    /**
     * [TABELLE SETTINGS]
     * @var bool    $_vi_table_column_spiele                    Spalte Spiele anzeigen?
     * @var bool    $_vi_table_column_siege                     Spalte Spiele anzeigen?
     * @var bool    $_vi_table_column_niederlagen               Spalte Niederlagen anzeigen?
     * @var bool    $_vi_table_column_siege_niederl_detail      Minimal/Komprimiert/Maximal Spalte Siege/Niederlagen als 3:0,3:1, ... anzeigen?
     * @var bool    $_vi_table_column_ballquotient              Spalte Ballquotient anzeigen?
     * @var bool    $_vi_table_column_ballverhaeltnis           Spalte Ballverhätlnis anzeigen?
     * @var bool    $_vi_table_column_satzquotient              Spalte Satzquotient anzeigen?
     * @var bool    $_vi_table_column_satzverhaeltnis           Spalte Satzverhältnis anzeigen?
     * @var bool    $_vi_table_column_punkte                    Spalte Punkte anzeigen?
     */
    protected $_vi_table_column_spiele = true;
    protected $_vi_table_column_siege = true;
    protected $_vi_table_column_niederlagen = true;
    protected $_vi_table_column_siege_niederl_detail = 2;
    protected $_vi_table_column_ballquotient = true;
    protected $_vi_table_column_ballverhaeltnis = true;
    protected $_vi_table_column_satzquotient = true;
    protected $_vi_table_column_satzverhaeltnis = true;
    protected $_vi_table_column_punkte = true;

    /**
     * [TABELLE SETTINGS]
     * @var integer $_vi_spielplan_anzeigemodus     0: Alle Mannschaften; 1: Nur eigene Mannschaft
     */
    protected $_vi_spielplan_anzeigemodus = 0;


    /**
     * Construction Method
     *
     * Method is called by the view
     *
     * @param  object &$subject The content object
     * @param  object $config The content params
     */
    public function __construct(&$subject, $config)
    {

        // Check Joomla version
        $version = new JVersion();
        if ($version->PRODUCT == 'Joomla!' AND $version->RELEASE < '2.5') {
            JError::raiseWarning(100, JText::_('AGM_VI_WRONGJOOMLAVERSION'));
            return;
        }

        parent::__construct($subject, $config);

        //Systemvariablen befüllen
        $this->_request = JFactory::getApplication()->input;
        $this->_app = JFactory::getApplication();
        $this->_doc = JFactory::getDocument();

        //Variablen aus Config laden & befüllen
        $this->_vi_highlight = $this->params->get('vi_highlight', 0);
        $this->_vi_verein = $this->params->get('vi_verein', '');
        $this->_vi_jqueryui_load = $this->params->get('vi_jqueryui_load', 1);
        $this->_vi_alternating_rows = $this->params->get('vi_alternating_rows', 1);
        $this->_vi_jqueryui_theme = $this->params->get('vi_jqueryui_theme', 'cuppertino');
        $this->_vi_copyright = $this->params->get('vi_copyright', 1);

        //Parameter für Tabelle aus Config laden & befüllen
        $this->_vi_table_column_spiele =                $this->params->get('vi_tabelle_spiele', 1);
        $this->_vi_table_column_siege =                 $this->params->get('vi_tabelle_siege', 1);
        $this->_vi_table_column_niederlagen =           $this->params->get('vi_tabelle_niederlagen', 1);
        $this->_vi_table_column_siege_niederl_detail =  $this->params->get('vi_tabelle_siege_niederl_detail', 2);
        $this->_vi_table_column_ballquotient =          $this->params->get('vi_tabelle_ballquotient', 1);
        $this->_vi_table_column_ballverhaeltnis =       $this->params->get('vi_tabelle_ballverhaeltnis', 1);
        $this->_vi_table_column_satzquotient =          $this->params->get('vi_tabelle_satzquotient', 1);
        $this->_vi_table_column_satzverhaeltnis =       $this->params->get('vi_tabelle_satzverhaeltnis', 1);
        $this->_vi_table_column_punkte =                $this->params->get('vi_tabelle_punkte', 1);

        //Parameter für Spielplan aus Config laden & befüllen
        $this->_vi_spielplan_anzeigemodus =             $this->params->get('vi_spielplan_anzeigemodus', 0);

        switch ($this->params->get('vi_verband', 0)) {
            case 0:
                $this->_vi_stream = 'https://vlw.it4sport.de/data/vbwb/aufsteiger/public/';
                break;
            case 1:
                $this->_vi_stream = 'https://tvv.it4sport.de/data/vbth/aufsteiger/public/';
                break;
        }

        ###Prüfen ob jQuery geladen ist
        //JHtml war in Joomla 2.5 noch nicht verfügbar. Klasse wird in VI mitgeliefert und muss hier registriert werden.
        if ($version->RELEASE == '2.5') {
            $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'cms';
            if (method_exists('JLoader', 'registerPrefix')) {
                JLoader::registerPrefix('J', $path);
                $this->debug_to_console("Path registered");
            }
        }

        //jQuery Laden
        JHtml::_('jquery.framework', true, true);

        $this->_doc->addScript(JURI::base() . 'plugins/content/volleyimport/media/thRotate.js');
        $this->_doc->addStyleSheet(JURI::base() . 'plugins/content/volleyimport/media/vi_style.css');

        //Stylesheet laden sofern er im Array der erlaubten Themes gefunden wird. Sonst Standard Stylesheet laden.
        if ($this->_vi_jqueryui_load AND in_array($this->_vi_jqueryui_theme, $this->_allowed_jqueryui_themes)) {
            $this->_doc->addStyleSheet('//code.jquery.com/ui/1.11.2/themes/' . $this->_vi_jqueryui_theme . '/jquery-ui.css');
        } //end if jqueryui_load
    } //end of function


    /**
     * Prepare Content
     *
     * Method is called by the view
     *
     * @param  string $context The context of the content being passed to the plugin.
     * @param  object &$article The content object.  Note $article->text is also available
     * @param  object &$params The content params
     * @param  int $limitstart The 'page' number
     */
    public function onContentPrepare($context, &$article, &$params, $limitstart)
    {

        //Definiere Regex für {volleyImport|2012|146|2} - {vlwImport|Saison|StaffelID|Anzeige} wobei bei Saison 2011/2012 das hintere Jahr benutzt wird
        $regex = '/{volleyImport\|([0-9]{4})\|([0-9]{1,3})\|([etsv]{1})(?![0-9a-z])(.*)}/';
        //$regex_params = '/([a-z]*=[a-z]*)*/'; //abc=def

        //Alle Uebereinstimmungen finden und in $matches speichern
        $matches = array();
        preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

        //Alle Übereinstimmungen durchlaufen und ersetzen
        $i_foreach = 1;
        $anz_matches = count($matches);
        foreach ($matches as $elm) {
            $saison = $elm[1];
            $staffelID = $elm[2];
            $anzeige = $elm[3];
            $params_matches = $elm[4];
            $params_matched = array(); //Initialise empty array

            ##Build Stream URLs and check them
            $stream_url = array(
                'ergebnis' => $this->_vi_stream . 'ergebnis_' . $saison . '_' . $staffelID . '.xml',
                'tabelle' => $this->_vi_stream . 'tabelle_' . $saison . '_' . $staffelID . '.xml',
                'vorschau' => $this->_vi_stream . 'vorschau_' . $saison . '_' . $staffelID . '.xml',
                'spielplan' => $this->_vi_stream . 'spielplan_' . $saison . '_' . $staffelID . '.xml'
            );

            foreach ($stream_url as $key => $url) {
                $headers = get_headers($url);
                $is_url = is_array($headers) ? preg_match('/^HTTP\/\d+\.\d+\s+2\d\d\s+.*$/', $headers[0]) : false;
                if (!$is_url) {
                    JError::raiseWarning(100, JText::_('AGM_VI_URLNOTREACHABLE'));
                }
            }

            ##Parameter auslesen
            /*if ($params_matches) {
                preg_match_all($regex_params, $params_matches, $params_matched);
                $params_matched = $params_matched[1];
                $params_matched = $this->array_filter_recursive($params_matched);
            }*/

            //Start Output
            $output = '<div class="volleyImportContainer table-responsive">';

            $element_to_search_for_highlighting = array();
            $xml_stream = '';
            switch ($anzeige) {
                case "e": //Ergebnis           
                    $xml_stream = $stream_url['ergebnis'];
                    break;
                case "s": //Spielplan
                    $xml_stream = $stream_url['spielplan'];
                    break;
                case "v": //Vorschau
                    $xml_stream = $stream_url['vorschau'];
                    break;
                case "t": //Tabelle           
                default:
                    $xml_stream = $stream_url['tabelle'];
                    break;
            }

            ###Generate DATA set for Tabelle
            $context = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
            $xml = file_get_contents($xml_stream, false, $context);
            $xml = simplexml_load_string($xml);
            $xml_has_content = (count($xml) > 0) ? true : false;
            //$this->debug_to_console("XML HAVE CONTENT: ". $xml_has_content);

            if ($xml_has_content) {
                //Generate HTML Element Container
                $table = new STable();
                $table->id = 'vi_table_' . $this->_table_id;
                $table->border = 0;
                $table->cellpadding = 0;
                $table->cellspacing = 0;

                $table_class_extension = ($anzeige == "t") ? ' table-header-rotated' : '';
                $table_class_jqueryui = ($this->_vi_jqueryui_load) ? ' ui-widget' : 'vi_basic';

                $table->class = $table_class_jqueryui . $table_class_extension;
                $table->width = "100%";


                //Tabellenheader aufbauen
                $thead_class_jqueryui = ($this->_vi_jqueryui_load) ? ' ui-widget-header' : '';
                $table->thead($thead_class_jqueryui);
                switch ($anzeige) {
                    case "t": //Tabelle
                        $table->th('#')
                            ->th('Verein');
                        if ($this->_vi_table_column_spiele) $table->thRotate('Spiele');
                        if ($this->_vi_table_column_siege) $table->thRotate('Siege');
                        if ($this->_vi_table_column_niederlagen) $table->thRotate('Niederlagen');

                        switch ($this->_vi_table_column_siege_niederl_detail) {
                            case 0:
                                break;
                            case 1:
                                $table->thRotate('3:0 / 3:1')
                                    ->thRotate('3:2')
                                    ->thRotate('2:3')
                                    ->thRotate('1:3 / 0:3');
                                break;
                            case 2:
                            default:
                                $table->thRotate('3:0')
                                    ->thRotate('3:1')
                                    ->thRotate('3:2')
                                    ->thRotate('2:3')
                                    ->thRotate('1:3')
                                    ->thRotate('0:3');
                                break;
                        }

                        if ($this->_vi_table_column_ballquotient) $table->thRotate('Ballquotient');
                        if ($this->_vi_table_column_ballverhaeltnis) $table->thRotate('Ballverhältnis');
                        if ($this->_vi_table_column_satzquotient) $table->thRotate('Satzquotient');
                        if ($this->_vi_table_column_satzverhaeltnis) $table->thRotate('Satzverhältnis');
                        if ($this->_vi_table_column_punkte) $table->thRotate('Punkte');
                        $table->th('');
                        break;
                    case "e": //Ergebnis
                        $table->th('Nr.')
                            ->th('Datum')
                            ->th('Spielbeginn')
                            ->th('Heim')
                            ->th('Gast')
                            ->th('Ergebnis');
                        break;
                    case "s": //Spielplan
                        $table->th('Nr.')
                            ->th('Spieltag')
                            ->th('Datum')
                            ->th('Hallenöffnung')
                            ->th('Spielbeginn')
                            ->th('Halle')
                            ->th('Heim')
                            ->th('Gast')
                            ->th('Ergebnis');
                        break;
                    case "v": //Todo: Vorschau
                        $table->th('Nr.');
                        break;
                }


                //Tabellenbody aufbauen
                $tbody_class_jqueryui = ($this->_vi_jqueryui_load) ? ' ui-widget-content' : '';
                $table->tbody($tbody_class_jqueryui);

                $i = 0;
                foreach ($xml as $element => $row) {
                    //Wenn Anzeigemodus Spielplan und nur eigene Mannschaften angezeigt werden sollen, dann Prüfen ob diese Zeile geskippt werden muss
                    if($anzeige == "s" AND $this->_vi_spielplan_anzeigemodus == 1 AND !$this->preg_match_array('/' . $this->_vi_verein . '/i', $row)) {
                        continue;
                    }

                    //Class Attribut für TR-Zeile ermitteln. Spezialfall "Highlighting" berücksichtigen.
                    //TODO: Highlighting Klasse wenn JQUERYUI nicht benutzt wird
                    $class_tr = '';
                    if ($this->_vi_alternating_rows) {
                        $class_alternating = ($this->_vi_jqueryui_load) ? ' ui-state-default' : ' alternate';
                        $class_tr = ($i % 2) ? $class_alternating : '';
                    }
                    $i++;

                    //Prüfen ob die Zeile gehighlightet werden muss. Wenn Anzeigemodus Spielplan und nur eigene dann highlighing überspringen
                    if ($this->_vi_highlight && !empty($this->_vi_verein)) {
                        if ($this->preg_match_array('/' . $this->_vi_verein . '/i', $row)) {
                            if($this->_vi_jqueryui_load) {
                                $class_tr = 'ui-state-highlight';
                            } else {
                                $class_tr = 'highlight';
                            }
                        }
                    }

                    if($anzeige == "s" AND $this->_vi_spielplan_anzeigemodus == "1") {
                        $class_tr = '';
                    }

                    //Tabellenzeilen aufbauen
                    $table->tr($class_tr);
                    switch ($anzeige) {
                        case "t": //Tabelle
                            $table->td($row->platz, 'text-center')
                                ->td($row->team);
                            if ($this->_vi_table_column_spiele) $table->td($row->spiele, 'text-center');
                            if ($this->_vi_table_column_siege) $table->td($row->dpsiege, 'text-center');
                            if ($this->_vi_table_column_niederlagen) $table->td($row->dpniederlagen, 'text-center');

                            switch ($this->_vi_table_column_siege_niederl_detail) {
                                case 0:
                                    break;
                                case 1:
                                    $table->td($row->dpgewinn30 + $row->dpgewinn31, 'text-center')
                                        ->td($row->dpgewinn32, 'text-center')
                                        ->td($row->dpniederlage23, 'text-center')
                                        ->td($row->dpniederlage13 + $row->dpniederlage03, 'text-center');
                                    break;
                                case 2:
                                default:
                                    $table->td($row->dpgewinn30, 'text-center')
                                        ->td($row->dpgewinn31, 'text-center')
                                        ->td($row->dpgewinn32, 'text-center')
                                        ->td($row->dpniederlage23, 'text-center')
                                        ->td($row->dpniederlage13, 'text-center')
                                        ->td($row->dpniederlage03, 'text-center');
                                    break;
                            }

                            if ($this->_vi_table_column_ballquotient) $table->td(round($row->plusbaelle / $row->minusbaelle, 2), 'text-center');
                            if ($this->_vi_table_column_ballverhaeltnis) $table->td($row->plusbaelle . ':' . $row->minusbaelle, 'text-center');
                            if ($this->_vi_table_column_satzquotient) $table->td(round($row->plussaetze / $row->minussaetze, 2), 'text-center');
                            if ($this->_vi_table_column_satzverhaeltnis) $table->td($row->plussaetze . ':' . $row->minussaetze, 'text-center');
                            if ($this->_vi_table_column_punkte) $table->td($row->dppunkte, 'text-center');
                            $table->td('', '', 'width="0px"');
                            break;
                        case "e": //Ergebnis
                            $table->td($row->nr, 'text-center')
                                ->td($row->datum, 'text-center')
                                ->td($row->spielbeginn, 'text-center')
                                ->td($row->heim)
                                ->td($row->gast)
                                ->td($row->sheim . ':' . $row->sgast . '<br>(' . $row->result . ')', 'text-center');
                            break;
                        case "s": //Spielplan
                            $table->td($row->nr, 'text-center')
                                ->td($row->spieltag, 'text-center')
                                ->td($row->datum, 'text-center')
                                ->td($row->hallenoeffnung, 'text-center')
                                ->td($row->spielbeginn, 'text-center')
                                ->td($row->halle)
                                ->td($row->heim)
                                ->td($row->gast)
                                ->td($row->sheim . ':' . $row->sgast . '<br>(' . $row->result . ')', 'text-center');
                            break;
                        case "v": //TODO: Vorschau
                            $table->td($row->nr, 'text-center');
                            break;
                    }
                }

                $output .= $table->getTable();

                $this->_table_id++;
            } else { //endif xml_have_content
                $output .= "Keine Daten verfügbar";
            }
            $output .= '</div><!-- END #volleyImportContainer -->';

            //Copyright Verlinkung zum Plugin-Autor. Bitte nicht löschen. Kann in den Plugineinstellungen deaktiviert werden. Würde mich dann über eine Erwähnung an anderer Stelle oder Spende sehr freuen.
            if($anz_matches == $i_foreach AND $this->_vi_copyright) $output .= '<div id="volleyImportAuthor" style="padding: 10px 0; font-size: 11px;"><p style="float: right;">Datenaufbereitung: <a href="http://www.agmedia.de" title="volleyImport - AGMedia Joomla! Extensions" target="_blank">volleyImport</a></p></div>';

            //Tabelle an entsprechender Position einfügen
            $article->text = str_replace($elm[0], $output, $article->text); //preg_replace($regex, $output, $article->text,1);

            $i_foreach++;
        } //end of preg_match_all


    }//function


    /**
     * Simple helper to debug to the console
     *
     * @param  Array , Object, String $data
     * @return String
     */
    protected function debug_to_console($data)
    {

        $output = '';

        if (is_array($data)) {
            $output .= "<script>console.warn( 'Debug Objects with Array.' ); console.log( '" . implode(',', $data) . "' );</script>";
        } else if (is_object($data)) {
            $data = var_export($data, TRUE);
            $data = explode("\n", $data);
            foreach ($data as $line) {
                if (trim($line)) {
                    $line = addslashes($line);
                    $output .= "console.log( '{$line}' );";
                }
            }
            $output = "<script>console.warn( 'Debug Objects with Object.' ); $output</script>";
        } else {
            $output .= "<script>console.log( 'Debug Objects: {$data}' );</script>";
        }

        echo $output;
    }

    protected function array_filter_recursive($input)
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = $this->array_filter_recursive($value);
            }
        }

        return array_filter($input);
    }

    protected function preg_match_array($regex, $haystack)
    {
        $matches = array();

        foreach ($haystack as $str) {
            //$this->debug_to_console('STR: '.$str);
            if (preg_match($regex, $str, $m)) {
                return true;
            }
        }

        return false;
    }
}//class

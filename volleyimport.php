<?php
/**
 * @package    volleyImport
 * @subpackage Base
 * @author     Alexander Grözinger {@link http://www.agmedia.de}
 * @author     Created on 06-May-2015
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');


jimport('joomla.plugin.plugin');

require_once(JPATH_PLUGINS . '/content/volleyimport/helpers/html.table.class.php');

//JHtml::_('jquery.framework',  true, true);
//JHtml::_('script','//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js', false, false, false, false);

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
     * @var int    $_table_id       Eindeutige ID für das spätere HTML Elemente
     * @var string $_vi_stream      Wird mit XML-Stream URL befüllt
     */
    protected $_table_id = 1;
    protected $_vi_stream = "";
    
    /**
     * [PLUGIN SETTINGS]
     * @var bool    $_vi_highlight            Soll das eigene Team farbig markiert werden
     * @var string  $_vi_verein               Enthält einen Teil des Vereinsnames
     * @var bool    $_vi_datatable_load       Soll das jQuery Plugin datatables benutzt werden
     * @var bool    $_vi_datatable_ordering   Soll die datatable sortierbar sein
     * @var bool    $_vi_jqueryui_load        Soll das jQueryUI Plugin geladen werden. Wird für Themes benutzt
     * @var string  $_vi_jqueryui_theme       Welches Theme soll geladen werden. Cupertino ist standard.
     * @var array   $_allowed_jqueryui_themes Enthällt alle zugelassenen Theme-Namen
     */
    protected $_vi_highlight = false;
    protected $_vi_verein = "";
    protected $_vi_datatable_load = true;
    protected $_vi_datatable_ordering = true;
    protected $_vi_jqueryui_load = true;
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
     * Construction Method
     *
     * Method is called by the view
     *
     * @param  object  &$subject    The content object
     * @param  object  $config      The content params
     */     
    public function __construct(&$subject, $config)
    {
       
       // Check Joomla version
       $version = new JVersion();
       if($version->PRODUCT == 'Joomla!' AND $version->RELEASE < '2.5')
       {
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
       $this->_vi_verein= $this->params->get('vi_verein', '');
       $this->_vi_datatable_load = $this->params->get('vi_datatable_load', 1);
       $this->_vi_datatable_ordering = $this->params->get('vi_datatable_ordering', 1);       
       $this->_vi_jqueryui_load = $this->params->get('vi_jqueryui_load', 1);       
       $this->_vi_jqueryui_theme = $this->params->get('vi_jqueryui_theme', 'cuppertino');          
       
       switch($this->params->get('vi_verband', 0)) {
          case 0:
             $this->_vi_stream = 'https://vlw.it4sport.de/data/vbwb/aufsteiger/public/';
             break;
          case 1:
             $this->_vi_stream = 'https://tvv.it4sport.de/data/vbth/aufsteiger/public/';
             break;
       }
       
       ###Prüfen ob jQuery geladen ist
       //JHtml war in Joomla 2.5 noch nicht verfügbar. Klasse wird in VI mitgeliefert und muss hier registriert werden.
       if ($version->RELEASE == '2.5')
       {
          $path = dirname(__FILE__).DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'cms';
          if (method_exists('JLoader', 'registerPrefix')) {
             JLoader::registerPrefix('J', $path);
             $this->debug_to_console("Path registered");
          }
       }
       
       JHtml::_('jquery.framework', true, true);
       
       
       //Load DataTable if activated
       if($this->_vi_datatable_load) {
          //Adding jQuery Datatable
          $this->_doc->addCustomTag('<script src="//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js" type="text/javascript"></script>');
          
          //Adding DataTable date formatting
          $this->_doc->addCustomTag('<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js" type="text/javascript"></script>');
          $this->_doc->addCustomTag('<script src="//cdn.datatables.net/plug-ins/1.10.7/sorting/datetime-moment.js" type="text/javascript"></script>');
          
          //JQUERYUI SUPPORT FOR PREDEFINED STYLES
          if($this->_vi_jqueryui_load) {
             $this->_doc->addCustomTag('<script src="//cdn.datatables.net/plug-ins/f2c75b7247b/integration/jqueryui/dataTables.jqueryui.js" type="text/javascript"></script>');
             
             //Only load style if found in allowed styles array. load default datatable style if no in array
             if(in_array($this->_vi_jqueryui_theme, $this->_allowed_jqueryui_themes)) {
                $this->_doc->addStyleSheet('//code.jquery.com/ui/1.11.2/themes/'.$this->_vi_jqueryui_theme.'/jquery-ui.css');
                $this->_doc->addStyleSheet('//cdn.datatables.net/plug-ins/f2c75b7247b/integration/jqueryui/dataTables.jqueryui.css');   
             } else {
                $this->_doc->addStyleSheet('//cdn.datatables.net/1.10.5/css/jquery.dataTables.min.css');
             }
          }  else {
             //Load default datatable stylesheet if jqueryui is deactivated
             $this->_doc->addStyleSheet('//cdn.datatables.net/1.10.5/css/jquery.dataTables.min.css');
          } //end if jqueryui_load    
       }//end if load_datatable
       
    } //end of function
    
    
    
     /**
     * Prepare Content
     *
     * Method is called by the view
     *
     * @param  string  $context     The context of the content being passed to the plugin.
     * @param  object  &$article    The content object.  Note $article->text is also available
     * @param  object  &$params     The content params
     * @param  int     $limitstart  The 'page' number
     */
    public function onContentPrepare($context, &$article, &$params, $limitstart)
    {
         
       //Definiere Regex für {volleyImport|2012|146|2} - {vlwImport|Saison|StaffelID|Anzeige} wobei bei Saison 2011/2012 das hintere Jahr benutzt wird
       $regex = '/{volleyImport\|([0-9]{4})\|([0-9]{1,3})\|([etsv]{1})(?![0-9a-z])(.*)}/';
       $regex_params = '/([a-z]*=[a-z]*)*/'; //abc=def
   
       //Alle Uebereinstimmungen finden und in $matches speichern
       $matches = array();
       preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER );
       
       //Alle Übereinstimmungen durchlaufen und ersetzen
       foreach ($matches as $elm) {
          $saison    = $elm[1];
          $staffelID = $elm[2];
          $anzeige   = $elm[3];    
          $params_matches = $elm[4];
          $params_matched = array(); //Initialise empty array
          
          ##Build Stream URLs and check them
          $stream_url = array(
                           'ergebnis' => $this->_vi_stream.'ergebnis_'.$saison.'_'.$staffelID.'.xml',
                           'tabelle' => $this->_vi_stream.'tabelle_'.$saison.'_'.$staffelID.'.xml',
                           'vorschau' => $this->_vi_stream.'vorschau_'.$saison.'_'.$staffelID.'.xml',
                           'spielplan' => $this->_vi_stream.'spielplan_'.$saison.'_'.$staffelID.'.xml'
                        );
                        
          foreach($stream_url as $key => $url) {
             $headers = get_headers($url);
             $is_url = is_array($headers) ? preg_match('/^HTTP\/\d+\.\d+\s+2\d\d\s+.*$/',$headers[0]) : false;
             if(!$is_url) {
                   JError::raiseWarning(100, JText::_('AGM_VI_URLNOTREACHABLE'));
             }
          }     
          
          ##Parameter auslesen
          if($params_matches) {
             preg_match_all($regex_params, $params_matches, $params_matched);
             $params_matched = $params_matched[1];
             $params_matched = $this->array_filter_recursive($params_matched);
          }
          $this->debug_to_console("Params Matched:");
          $this->debug_to_console($params_matched);
          
          //Start Output
          $output = '<div class="volleyImportContainer">';                                                                                  
          
          $element_to_search_for_highlighting = array();
          $xml_stream = ''; //TODO: Überprüfung damit nicht leer sein kann
          switch ($anzeige) {
                case "t": //Tabelle                   
                   $xml_stream= $stream_url['tabelle'];
                   $dt_orderable_targets = '[3,4,5]';
                   break;
                case "e": //Ergebnis           
                   $xml_stream= $stream_url['ergebnis'];
                   $dt_orderable_targets = '[]';
                   break;
                case "s": //Spielplan
                   $xml_stream= $stream_url['spielplan'];
                   $dt_orderable_targets = '[]';
                   break;
                case "v": //Vorschau
                   $xml_stream= $stream_url['vorschau'];
                   $dt_orderable_targets = '[]';
                   break;             
             }
             
          ###Generate DATA set for Tabelle
          $context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
          $xml = file_get_contents($xml_stream, false, $context);
          $xml = simplexml_load_string($xml); 
          $xml_have_content = (count($xml) > 0) ? true : false;  
          //$this->debug_to_console("XML HAVE CONTENT: ". $xml_have_content);
          
          if($xml_have_content) {
             //Generate HTML Element Container
             $table = new STable();
             $table->id = 'vi_table_'.$this->_table_id;
             $table->border = 0;
             $table->cellpadding = 0;
             $table->cellspacing = 0;
             $table->class = "dataTable compact cell-border";
             $table->width = "100%";
             
             
             //Tabellenheader aufbauen
             $table->thead();
             switch($anzeige) {
                case "t": //Tabelle
                   $table->th('Platz')
                         ->th('Team')
                         ->th('Spiele')
                         ->th('Satzverhältnis')
                         ->th('Siege<br>3:0/3:1 | 3:2')
                         ->th('Niederlagen<br>0:3/1:3 | 2:3')
                         ->th('Punkte');
                   break;
                case "e": //Ergebnis
                   $table->th('Nr.')
                         ->th('Datum')
                         ->th('Spielbeginn')
                         ->th('Heim vs. Gast')
                         ->th('Ergebnis');
                   break;
                case "s": //Spielplan
                   $table->th('Nr.')
                         ->th('Spieltag')
                         ->th('Datum')
                         ->th('Hallenöffnung')
                         ->th('Spielbeginn')
                         ->th('Halle')
                         ->th('Heim vs. Gast')
                         ->th('Ergebnis');             
                   break;
                case "v": //Todo: Vorschau
                   $table->th('Nr.');
                   break;
             }
             
             
   
             
             foreach($xml as $element => $row) {    
                           
                $class_highlight = '';
                if($this->_vi_highlight && !empty($this->_vi_verein)) {
                   if ($this->preg_match_array('/'. $this->_vi_verein .'/i', $row)) { 
                      $class_highlight = 'ui-state-highlight';
                   }
                }
                
                
                //Tabellenzeilen aufbauen
                $table->tr($class_highlight);
                switch($anzeige) {
                   case "t": //Tabelle                   
                      $table->td($row->platz, 'dt-body-center')
                            ->td($row->team)
                            ->td($row->spiele, 'dt-body-center')
                            ->td($row->plussaetze.':'.$row->minussaetze, 'dt-body-center')
                            ->td($row->dpgewinn3031.' | '.$row->dpgewinn32, 'dt-body-center')
                            ->td($row->dpniederlage1303.' | '.$row->dpniederlage23, 'dt-body-center')
                            ->td($row->dppunkte, 'dt-body-center');
                      break;
                   case "e": //Ergebnis
                      $table->td($row->nr, 'dt-body-center')
                            ->td($row->datum, 'dt-body-center')
                            ->td($row->spielbeginn, 'dt-body-center')
                            ->td($row->heim.' vs. '.$row->gast)
                            ->td($row->sheim.':'.$row->sgast.'<br>('.$row->result.')', 'dt-body-center');           
                      break;
                   case "s": //Spielplan
                      $table->td($row->nr, 'dt-body-center')
                            ->td($row->spieltag, 'dt-body-center')
                            ->td($row->datum, 'dt-body-center')
                            ->td($row->hallenoeffnung, 'dt-body-center')
                            ->td($row->spielbeginn, 'dt-body-center')
                            ->td($row->halle)
                            ->td($row->heim.' vs. '.$row->gast)
                            ->td($row->sheim.':'.$row->sgast.'<br>('.$row->result.')', 'dt-body-center');    
                      break;
                   case "v": //TODO: Vorschau
                      $table->td($row->nr, 'dt-body-center');
                      break;
                }
             }
             
             $output .= $table->getTable();
             
             #############################################################
             ###Script declaration for current table
             $dt_orderable = 'true';
             $dt_searchable = 'false';
             $dt_pagination = 'false';
             $dt_sdom = '<"H"lfr>t<"F"ip>';
             $jQueryUI = ($this->_vi_datatable_load == true) ? '"jQueryUI": true,' : '' ;
             if(in_array('orderable=false', $params_matched)) {
                $dt_orderable = 'false';
                $dt_orderable_targets = '"_all"';
             }
             if(in_array('searchable=true', $params_matched)) {
                $dt_searchable = 'true';
             }
             if(in_array('pagination=true', $params_matched)) {
                $dt_pagination = 'true';
             }
             if($dt_searchable == 'false' && $dt_pagination == 'false') {
                $dt_sdom = '<rt><"F">';
             }
             
             $this->_doc->addScriptDeclaration('
                jQuery(document).ready(function(){
                  
                   jQuery.fn.dataTable.moment( "DD.MM.YYYY" );
                
                   jQuery("#vi_table_'.$this->_table_id.'").DataTable({
                       "columnDefs": [{ 
                          "orderable": '.$dt_orderable.', 
                          "targets": '.$dt_orderable_targets.'
                       }],
                       paging: '.$dt_pagination.',
                       searching: '.$dt_searchable.',
                       info: '.$dt_pagination.',
                       "scrollX": true, //Allow Table to be scrolled horizontally if needed
                       '.$jQueryUI.'
                       dom: \''.$dt_sdom.'\'
                   });  
                });
             ');
             
             $this->_table_id++;
          } else { //endif xml_have_content
             $output .= "Keine Daten verfügbar";
          }
          
          
          $output .= '</div><!-- END #volleyImportContainer -->';
          $article->text = str_replace( $elm[0], $output, $article->text); //preg_replace($regex, $output, $article->text,1);
          
       } //end of preg_match_all
       
       
       
    }//function
    
    
    /**
    * Simple helper to debug to the console
    * 
    * @param  Array, Object, String $data
    * @return String
    */
   protected function debug_to_console( $data ) {
      
      $output = '';
      
      if ( is_array( $data ) ) {
         $output .= "<script>console.warn( 'Debug Objects with Array.' ); console.log( '" . implode( ',', $data) . "' );</script>";
      } else if ( is_object( $data ) ) {
         $data    = var_export( $data, TRUE );
         $data    = explode( "\n", $data );
         foreach( $data as $line ) {
            if ( trim( $line ) ) {
               $line    = addslashes( $line );
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
      foreach ($input as &$value) 
      { 
         if (is_array($value)) 
         { 
            $value = $this->array_filter_recursive($value); 
         } 
      } 
     
      return array_filter($input); 
   } 
   
   protected function preg_match_array($regex, $haystack) {
      $matches = array ();    
      
      foreach ($haystack as $str)  {
         //$this->debug_to_console('STR: '.$str);
         if (preg_match ($regex, $str, $m)) {
            return true;
         }
      }
      
      return false;
   }
    
}//class

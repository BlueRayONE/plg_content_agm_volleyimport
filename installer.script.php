<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

ini_set('display_errors', TRUE);                                                                                                                                                                                 
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));  

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file'); 

//Todo: Installationsstatus Tabelle erstellen

/**
 * Script file for the plg_system_example plugin    
 */
class plgContentvolleyimportInstallerScript
{
   /**
    * Called before any type of action
    *
    * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
    * @param   JAdapterInstance  $adapter  The object responsible for running this script
    *
    * @return  boolean  True on success
    */
   public function preflight($type, $parent) {

   }
 
   /**
    * Called after any type of action
    *
    * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
    * @param   JAdapterInstance  $adapter  The object responsible for running this script
    *
    * @return  boolean  True on success
    */
   public function postflight($type, $parent) {
      
   }
 
   /**
    * Called on installation
    *
    * @param   JAdapterInstance  $adapter  The object responsible for running this script
    *
    * @return  boolean  True on success
    */
   public function install($parent) {
      $this->processFiles();
     return true;
   }
 
   /**
    * Called on update
    *
    * @param   JAdapterInstance  $adapter  The object responsible for running this script
    *
    * @return  boolean  True on success
    */
   public function update($parent) {
      $this->processFiles('update');
     return true;
   }
 
   /**
    * Called on uninstallation
    *
    * @param   JAdapterInstance  $adapter  The object responsible for running this script
    */
   public function uninstall($parent) {
      $this->processFiles('uninstall');
     return true;
   }
   
   protected function processFiles($type = 'install') {
      $version = new JVersion();
      $plugin_path = JPATH_ROOT.'/plugins/content/volleyimport/';
      $target_path = JPATH_ROOT.'/media/jui';
      $joomla_media_path = JPATH_ROOT.'/media';
      
      /*$this->debug_to_console('#################################');
      $this->debug_to_console('INSTALL: processing Files startet:');
      $this->debug_to_console('Plugin Path: '.$plugin_path);
      $this->debug_to_console('Target Path: '.$target_path);
      $this->debug_to_console('JRELEASE: '.$version->RELEASE);*/
      
      //Todo: echo's entfernen
      
      if($version->RELEASE == '2.5') {
         switch($type) {
            case 'install':
            echo "Joomla 2.5 detected. Filesupport added.";
                //Check if Folder exists and delete if neccessary
                if(JFolder::exists($target_path)) {
               JFolder::delete($target_path); 
                }               
                JFolder::move($plugin_path.'media/jui', $target_path); 
                JFolder::delete($plugin_path.'media');      
            break;
         case 'update':
            echo "Joomla 2.5 detected. Filesupport updated.";
            //Check if Folder exists and delete if neccessary
                if(JFolder::exists($target_path)) {
               JFolder::delete($target_path); 
                }               
                JFolder::move($plugin_path.'media/jui', $target_path); 
                JFolder::delete($plugin_path.'media'); 
            break;
            case 'uninstall':
            echo "Joomla 2.5 detected. Filesupport removed.";
               //$this->debug_to_console('Type uninstall');
               if(JFolder::exists($target_path)) {
                  JFolder::delete($target_path);  
                  //$this->debug_to_console('- Target Folder deleted');
               }
               break;
         }
      }
   }
   
   
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
}
?>
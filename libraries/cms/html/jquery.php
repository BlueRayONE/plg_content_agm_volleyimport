<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for jQuery JavaScript behaviors
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       3.0
 */
abstract class JHtmlJquery
{
   /**
    * @var    array  Array containing information for loaded files
    * @since  3.0
    */
   protected static $loaded = array();

   /**
    * Method to load the jQuery JavaScript framework into the document head
    *
    * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
    *
    * @param   boolean  $noConflict  True to load jQuery in noConflict mode [optional]
    * @param   mixed    $debug       Is debugging mode on? [optional]
    * @param   boolean  $migrate     True to enable the jQuery Migrate plugin
    *
    * @return  void
    *
    * @since   3.0
    */
   public static function framework($noConflict = true, $debug = null, $migrate = true)
   {
      // Only load once
      if (!empty(self::$loaded[__METHOD__]))
      {
         return;
      }

      // If no debugging value is set, use the configuration setting
      if ($debug === null)
      {
         $config = JFactory::getConfig();
         $debug  = (boolean) $config->get('debug');
      }

      JHtml::_('script', 'jui/jquery.min.js', false, true, false, false, $debug);

      // Check if we are loading in noConflict
      if ($noConflict)
      {
         JHtml::_('script', 'jui/jquery-noconflict.js', false, true, false, false, false);
      }

      // Check if we are loading Migrate
      if ($migrate)
      {
         JHtml::_('script', 'jui/jquery-migrate.min.js', false, true, false, false, $debug);
      }

      self::$loaded[__METHOD__] = true;

      return;
   }

   /**
    * Method to load the jQuery UI JavaScript framework into the document head
    *
    * If debugging mode is on an uncompressed version of jQuery UI is included for easier debugging.
    *
    * @param   array  $components  The jQuery UI components to load [optional]
    * @param   mixed  $debug       Is debugging mode on? [optional]
    *
    * @return  void
    *
    * @since   3.0
    */
   public static function ui(array $components = array('core'), $debug = null)
   {
      // Set an array containing the supported jQuery UI components handled by this method
      $supported = array('core', 'sortable');

      // Include jQuery
      self::framework();

      // If no debugging value is set, use the configuration setting
      if ($debug === null)
      {
         $config = JFactory::getConfig();
         $debug  = (boolean) $config->get('debug');
      }

      // Load each of the requested components
      foreach ($components as $component)
      {
         // Only attempt to load the component if it's supported in core and hasn't already been loaded
         if (in_array($component, $supported) && empty(self::$loaded[__METHOD__][$component]))
         {
            JHtml::_('script', 'jui/jquery.ui.' . $component . '.min.js', false, true, false, false, $debug);
            self::$loaded[__METHOD__][$component] = true;
         }
      }

      return;
   }



/**
 * class JHtmlBehavior
 */

   /**
    * Add unobtrusive JavaScript support for a color picker.
    *
    * @return  void
    *
    * @since   1.7
    */
   public static function colorpicker()
   {
      // Only load once
      if (isset(self::$loaded[__METHOD__]))
      {
         return;
      }

      // Include jQuery
      JHtml::_('jquery.framework');

      JHtml::_('script', 'jui/jquery.minicolors.min.js', false, true);
      JHtml::_('stylesheet', 'jui/jquery.minicolors.css', false, true);
      JFactory::getDocument()->addScriptDeclaration("
            jQuery(document).ready(function (){
               jQuery('.minicolors').each(function() {
                  jQuery(this).minicolors({
                     control: jQuery(this).attr('data-control') || 'hue',
                     position: jQuery(this).attr('data-position') || 'right',
                     theme: 'bootstrap'
                  });
               });
            });
         "
      );

      self::$loaded[__METHOD__] = true;
   }

   /**
    * Add unobtrusive JavaScript support for a simple color picker.
    *
    * @return  void
    *
    * @since   3.1
    */
   public static function simplecolorpicker()
   {
      // Only load once
      if (isset(self::$loaded[__METHOD__]))
      {
         return;
      }

      // Include jQuery
      JHtml::_('jquery.framework');

      JHtml::_('script', 'jui/jquery.simplecolors.min.js', false, true);
      JHtml::_('stylesheet', 'jui/jquery.simplecolors.css', false, true);
      JFactory::getDocument()->addScriptDeclaration("
            jQuery(document).ready(function (){
               jQuery('select.simplecolors').simplecolors();
            });
         "
      );

      self::$loaded[__METHOD__] = true;
   }
   
   /**
    * Add unobtrusive JavaScript support to keep a tab state.
    *
    * Note that keeping tab state only works for inner tabs if in accordance with the following example
    * parent tab = permissions
    * child tab = permission-<identifier>
    *
    * Each tab header "a" tag also should have a unique href attribute
    *
    * @return  void
    *
    * @since   3.2
    */
   public static function tabstate()
   {
      if (isset(self::$loaded[__METHOD__]))
      {
         return;
      }
      // Include jQuery
      JHtml::_('jquery.framework');
      JHtml::_('script', 'system/tabs-state.js', false, true);
      self::$loaded[__METHOD__] = true;
   }
}

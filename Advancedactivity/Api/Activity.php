<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Activity.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Api_Activity extends Core_Api_Abstract
{

  /**
   * Loader for parsers
   * 
   * @var Zend_Loader_PluginLoader
   */
  protected $_pluginLoader;

  // Parsing
  /**
   * Activity template parsing
   * 
   * @param string $body
   * @param array $params
   * @return string
   */
  public function assemble($body, array $params = array())
  {

    // Translate body
    $body = nl2br($this->getHelper('translate')->direct($body));

    //print_r($params['body']);die;
    // Do other stuff
    preg_match_all('~\{([^{}]+)\}~', $body, $matches, PREG_SET_ORDER);

    foreach( $matches as $match ) {
      $tag = $match[0];
      $args = explode(':', $match[1]);
      $helper = array_shift($args);

      $helperArgs = array();
      foreach( $args as $arg ) {
        if( substr($arg, 0, 1) === '$' ) {
          $arg = substr($arg, 1);
          $helperArgs[] = ( isset($params[$arg]) ? $params[$arg] : null );
        } else {
          $helperArgs[] = $arg;
        }
      }

      $helper = $this->getHelper($helper);
      $helper->setAction($params['action']);
      $r = new ReflectionMethod($helper, 'direct');
      $content = $r->invokeArgs($helper, $helperArgs);
      $content = preg_replace('/\$(\d)/', '\\\\$\1', $content);

      if( is_string($content) && is_string($body) ) {
        $body = preg_replace("/" . preg_quote($tag) . "/", $content, $body, 1);
      } elseif( is_array($content) && is_array($body) ) {
        $body = preg_replace("/" . preg_quote($tag) . "/", $content, $body, 1);
      }
    }


    return $body;
  }

  /**
   * Gets the plugin loader
   * 
   * @return Zend_Loader_PluginLoader
   */
  public function getPluginLoader()
  { // Customize this functions 
    if( null === $this->_pluginLoader ) {
      $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR
        . 'modules' . DIRECTORY_SEPARATOR
        . 'Advancedactivity';
      $this->_pluginLoader = new Zend_Loader_PluginLoader(array(
        'Advancedactivity_Model_Helper_' => $path . '/Model/Helper/'
      ));
    }

    return $this->_pluginLoader;
  }

  /**
   * Get a helper
   * 
   * @param string $name
   * @return Activity_Model_Helper_Abstract
   */
  public function getHelper($name)
  {
    $name = $this->_normalizeHelperName($name);
    if( !isset($this->_helpers[$name]) ) {
      $helper = $this->getPluginLoader()->load($name);
      $this->_helpers[$name] = new $helper;
    }

    return $this->_helpers[$name];
  }

  /**
   * Normalize helper name
   * 
   * @param string $name
   * @return string
   */
  protected function _normalizeHelperName($name)
  {
    $name = preg_replace('/[^A-Za-z0-9]/', '', $name);
    //$name = strtolower($name);
    $name = ucfirst($name);
    return $name;
  }

  /**
   * Style the feed body 
   * 
   * @param string $body
   * @return string
   */
  public function setFeedStyle($body, $params = array())
  {

    $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');
    $charLength = $coreSettingsApi->getSetting('advancedactivity.feed.char.length', 50);
    $trimBody = trim($body);
    if( !empty($trimBody) && strlen($trimBody) <= $charLength && empty($params['feed-banner']['background-color']) ) {
      $fontSize = $coreSettingsApi->getSetting('advancedactivity.feed.font.size', 25);
      $customizeBody = "<span style='font-size:" . $fontSize . "px;";
      $fontColor = $coreSettingsApi->getSetting('advancedactivity.feed.font.color');
      if( $fontColor ) {
        $customizeBody .= 'color:' . $fontColor . ';';
      }
      $fontStyle = $coreSettingsApi->getSetting('advancedactivity.feed.font.style');
      if( $fontStyle ) {
        $customizeBody .= 'font-style:' . $fontStyle . ';';
      }

      $customizeBody .= "'> " . $body . " </span>";
      $body = $customizeBody;
    }

    $words = Engine_Api::_()->getDbtable('words', 'advancedactivity')->getActivityStylingWords();
    $keyWords = array();
    foreach( $words as $key => $item ) {
      if( strripos($body, $item['title']) === false ) {
        continue;
      }
      if( !empty($item['params']) ) {
        $item['params'] = json_decode($item['params'], true);
      }
      $key = 'AAF_HIGH_' . $key . time() . '_KEY';
      $keyWords[$key] = $item['title'];
      $spanStyle = "<span style='";
      if( $item['background_color'] && !empty($item['params']['bg_enabled']) ) {
        $spanStyle .= 'background-color:' . $item['background_color'] . ';';
      }
      if( $item['color'] ) {
        $spanStyle .= 'color:' . $item['color'] . ';';
      }
      if( $item['style'] ) {
        $spanStyle .= 'font-style:' . $item['style'] . ';';
      }
      $spanStyle .= "'";
      if( $item['params']['animation'] ) {
        $spanStyle .= ' data-animation="' . $item['params']['animation'] . '"';
      }
      $spanStyle .= ' >';
      $body = str_ireplace($item['title'], $spanStyle . $key . "</span>", $body);
    }
    if( $keyWords ) {
      $body = str_replace(array_keys($keyWords), $keyWords, $body);
    }
    return $body;
  }

}

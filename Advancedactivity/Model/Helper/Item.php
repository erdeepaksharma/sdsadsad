<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: Item.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_Model_Helper_Item extends Advancedactivity_Model_Helper_Abstract
{
  /**
   * Generates text representing an item
   * 
   * @param mixed $item The item or item guid
   * @param string $text (OPTIONAL)
   * @param string $href (OPTIONAL)
   * @return string
   */
  public function direct($item, $text = null, $href = null)
  {
    $item = $this->_getItem($item, false);

    // Check to make sure we have an item
    if( !($item instanceof Core_Model_Item_Abstract) )
    {
      return false;
    }

    if( !isset($text) )
    {
      $text = $item->getTitle();
    }

    // translate text
    $translate = Zend_Registry::get('Zend_Translate');
    if( $translate instanceof Zend_Translate ) {
      $text = $translate->translate($text);
      // if the value is pluralized, only use the singular
      if (is_array($text))
        $text = $text[0];
    }

    if( !isset($href) )
    {
      $href = $item->getHref();
    }
    $suffix="";
    if(in_array($item->getType(), array('activity_action', 'siteeventticket_ticket'))){
      $suffix="_no";
    }
        
    return '<a '
      . 'class="feed_item_username notranslate sea_add_tooltip_link'.$suffix.' feed_'.$item->getType().'_title" '
      .'rel="'.$item->getType().' '.$item->getIdentity().'" '
      . ( $href ? 'href="'.$href.'"' : '' )
      . '>'
      . $text
      . '</a>';
  }
}
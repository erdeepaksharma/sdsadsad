<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: Actors.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_Model_Helper_Actors extends Advancedactivity_Model_Helper_Abstract
{
  public function direct($subject, $object, $separator = '  &#8594; ')
  {
    $pageSubject = Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : null;

    $subject = $this->_getItem($subject, false);
    $object = $this->_getItem($object, false);
    
    // Check to make sure we have an item
    if( !($subject instanceof Core_Model_Item_Abstract) || !($object instanceof Core_Model_Item_Abstract) )
    {
      return false;
    }

    $attribsSubject = array('class' => 'feed_item_username  sea_add_tooltip_link','rel'=>$subject->getType().' '.$subject->getIdentity());
    $attribsObject = array('class' => 'feed_item_username notranslate sea_add_tooltip_link','rel'=>$object->getType().' '.$object->getIdentity());

    if( null === $pageSubject ) {
      return $subject->toString($attribsSubject) . $separator . $object->toString($attribsObject);
    } else if( $pageSubject->isSelf($subject) ) {
      return $subject->toString($attribsSubject) . $separator . $object->toString($attribsObject);
    } else if( $pageSubject->isSelf($object) ) {
      return $subject->toString($attribsSubject);
    } else {
      return $subject->toString($attribsSubject) . $separator . $object->toString($attribsObject);
    }
  }
}

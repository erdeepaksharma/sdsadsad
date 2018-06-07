<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Words.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_Words extends Engine_Db_Table
{

  protected $_rowClass = 'Advancedactivity_Model_Word';
  protected $_serializedColumns = array('params');
  protected $_activityStylingWords = null;

  public function getActivityStylingWords()
  {
    if( $this->_activityStylingWords !== null ) {
      return $this->_activityStylingWords;
    }
    $wordTable = Engine_Api::_()->getDbtable('words', 'advancedactivity');
    $select = $wordTable->select();
    $words = $wordTable->fetchAll($select)->toArray();
    usort($words, array($this, 'shortWords'));
    $this->_activityStylingWords = $words;
    return $this->_activityStylingWords;
  }

  protected function shortWords($a, $b)
  {
    return strlen($b['title']) - strlen($a['title']);
  }

}

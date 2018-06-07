<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Share.php 8968 2011-06-02 00:48:35Z john $
 * @author     John
 */
class Advancedactivity_Form_TargetUser extends Engine_Form
{
  public function init()
  {
    $this->addElement('Radio', 'who', array(
      'label' => '',
      'description' => "Target your audience to whom you want to show this post",
      'multiOptions' => array(
        '' => 'All',
        'male' => 'Male',
        'female' => 'Female',
      ),
      'value' => '',
    ));
    $age = array('Min age');
    $ageOption = 13;
    while( $ageOption <= 100 ) {
      $age[$ageOption] = ++$ageOption;
    }
    $select = array();
    $this->addElement('Select', 'min_age', array(
      'label' => 'Age',
      'multiOptions' => $age,
      'value' => 0,
    ));
    $age[0] = 'Max age';
    $select[] = 'min_age';
    $this->addElement('Select', 'max_age', array(
      'multiOptions' => $age,
      'value' => 0,
    ));
    $select[] = 'max_age';
    $this->addDisplayGroup($select, 'selects');
  }

}

<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Create.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */

class Advancedactivity_Form_Admin_Link_Edit extends Advancedactivity_Form_Admin_Link_Create {
  public function init() {

    // Init form
    $this
      ->setTitle('Edit New Link')
      ->setDescription('Here, edit link for your users. You can make this links available to users by default. 
')
    ;


    parent::init();
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save',
      'type' => 'submit',
      'order' => 999
    ));
  }

  public function saveValues($feelingtype = null) {
    $values = $this->getValues();
    
  try {
    if (empty($feelingtype)) {
      $params = Array();
      $params['title'] = $values['title'];
      $params['body'] = $values['body'];
      $params['start_time'] = $values['start_time'];
      if ($values['end_time'] === '0000-00-00') {
        $values['end_time'] = '2050-12-31 23:59:59'; 
      }
      $params['end_time'] = $values['end_time'];
      $params['enabled'] = $values['enabled'];
      $params['include'] = $values['include'];
      $feelingtype = Engine_Api::_()->getDbtable('feelingtypes', 'advancedactivity')->createRow();
      $feelingtype->setFromArray($params);
      $feelingtype->save();
   
     
        
    }
  }  catch (Exception $e){
      die("Exception ".$e);
  }

    // Do other stuff
    $count = 0;
    foreach ($values['file'] as $feeling_id) {
      $feeling = Engine_Api::_()->getItem("advancedactivity_feeling", $feeling_id);
      if (!($feeling instanceof Core_Model_Item_Abstract) || !$feeling->getIdentity())
        continue;

      if (!$feelingtype->feeling_id) {
        $feelingtype->feeling_id = $feeling_id;
        $feelingtype->save();
      }

      $feeling->feelingtype_id = $feelingtype->feelingtype_id;
      $feeling->order = $feeling_id;
      $feeling->save();
      $count++;
    }

    return $feelingtype;
  }

}

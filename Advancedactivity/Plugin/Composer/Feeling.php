<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Feeling.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Plugin_Composer_Feeling extends Core_Plugin_Composer {

  public function onAAFComposerFeeling($data, $params) {
       
    $action = (empty($params)) ? null : $params['action'];
    
    if (!$action || empty($data['feeling']['parent']) || empty($data['feeling']['child'])) {
      return;
    }
  
    $action->params = array_merge((array) $action->params, array('feelings' => $data['feeling']));
    $action->save();

  
  }
}
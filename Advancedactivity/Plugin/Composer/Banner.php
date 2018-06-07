<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Tag.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Plugin_Composer_Banner extends Core_Plugin_Composer
{
  public function onAAFComposerBanner($data, $params)
  {

    $action = (empty($params)) ? null : $params['action'];
    $trimBody = trim($action->body);
    if( !$action || empty($data['banner']) || empty($trimBody)) {
      return;
    }

    $action->params = array_merge((array) $action->params, array('feed-banner' => $data['banner']));
    $action->save();
  }

}

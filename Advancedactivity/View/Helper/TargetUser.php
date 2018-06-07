<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: TargetUser.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_View_Helper_TargetUser extends Zend_View_Helper_Abstract
{
  public function targetUser()
  {
       $form = new Advancedactivity_Form_TargetUser();
    return $this->view->partial(
        '_targetUser.tpl', 'advancedactivity', array(
            'form' => $form,
        )
    );
  }

  
}

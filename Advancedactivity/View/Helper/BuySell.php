<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: BuySell.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_View_Helper_BuySell extends Zend_View_Helper_Abstract
{
  public function buySell()
  { 
    $this->view->form = $form = new Advancedactivity_Form_BuySell_Create();
    return $this->view->partial(
        '_buySell.tpl', 'advancedactivity', array(
        'form' => $form,
        )
    );
  }

}

<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: FormFancyUpload.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */

class Advancedactivity_Form_BuySell_FormFancyUpload extends Engine_Form_Decorator_FormFancyUpload {
  public function render($content) { die(" data ew");
    $data = $this->getElement()->getAttrib('data');
    if ($data) {
      $this->getElement()->setAttrib('data', null);
    }
    $view = $this->getElement()->getView();
    return $view->partial('buy-sell/upload.tpl', 'advancedactivity', array(
        'name' => $this->getElement()->getName(),
        'data' => $data,
        'element' => $this->getElement()
    ));
  }

}

<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Photos.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Album_Photos extends Engine_Form {

  public function init() {
    
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    $this->addElement('Radio', 'cover', array(
        'label' => 'Album Cover',
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
    ));
  }

}
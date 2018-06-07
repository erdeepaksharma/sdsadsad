<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2010-2011 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Photo.php 2011-05-05 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_BuySell_Photo extends Engine_Form {

  public function init() {
    $this
            ->setTitle('Add More Photos')
            ->setAttrib('enctype', 'multipart/form-data')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ;

  // Init file
    $this->addElement('FancyUpload', 'file');
    $this->file->addPrefixPath('Advancedactivity_Form_Decorator', 'application/modules/Advancedactivity/Form/Decorator', 'decorator');
    $this->addElement('Hidden', 'photo_id', array(
                      'value'=>''
    ));
    $this->addElement('Button', 'execute', array(
        'label' => 'Save Photos',
        'type' =>'submit'
        
    ));
    
  }
}

?>
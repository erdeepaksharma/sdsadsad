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
class Advancedactivity_Form_Photo extends Engine_Form {

  public function init() {
    $this
            ->setTitle('Upload Your Profile Picture')
            ->setAttrib('enctype', 'multipart/form-data')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setAttrib('name', 'EditPhoto');


    $this->addElement('Image', 'current', array(
        'label' => 'Current Photo',
        'ignore' => true,
        'decorators' => array(array('ViewScript', array(
                    'viewScript' => '_formEditImage.tpl',
                     'class' => 'form element',
                    'testing' => 'testing'
            )))
    ));
    Engine_Form::addDefaultDecorators($this->current);

    $this->addElement('File', 'Filedata', array(
        'label' => 'Choose New Photo',
        'destination' => APPLICATION_PATH . '/public/temporary/',
        'validators' => array(
            array('Extension', false, 'jpg,jpeg,png,gif'),
        ),
        'onchange' => 'javascript:uploadPhoto();'
    ));

    $this->addElement('Hidden', 'coordinates', array(
        'order' => 900,
        'filters' => array(
            'HtmlEntities',
        )
    ));
    
    $this->addElement('Button', 'cancel', array(
        'label' => 'Cancel',
        'order'=> 5,
        'onclick' => 'parent.Smoothbox.close();',
        //'decorators' => array('ViewHelper'),
    ));
    
  }
}

?>
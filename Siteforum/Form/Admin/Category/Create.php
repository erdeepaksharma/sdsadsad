<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Create.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Admin_Category_Create extends Engine_Form {

    public function init() {

        $this->setTitle('Add Category');

        // Element: title
        $this->addElement('Text', 'title', array(
            'allowEmpty' => false,
            'required' => true,
            'label' => 'Title',
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Zend_Filter_StringTrim,
                new Engine_Filter_StringLength(array('max' => '63')),
            )
        ));

        // Element: submit
        $this->addElement('Button', 'submit', array(
            'label' => 'Add',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array('ViewHelper')
        ));

        $this->addElement('Cancel', 'cancel', array(
            'label' => 'cancel',
            'link' => true,
            'prependText' => ' or ',
            'href' => '',
            'onclick' => 'parent.Smoothbox.close();',
            'decorators' => array(
                'ViewHelper'
            )
        ));

        $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
        $this->getDisplayGroup('buttons');
    }

}

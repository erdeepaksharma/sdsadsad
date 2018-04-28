<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Rename.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Topic_Rename extends Engine_Form {

    public function init() {
        $this
                ->setTitle('Rename Topic')
        ;

        $this->addElement('Text', 'title', array(
            'label' => 'Title',
            'allowEmpty' => false,
            'required' => true,
            'validators' => array(
                array('StringLength', true, array(1, 64)),
            ),
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
            ),
        ));

        // Element: execute
        $this->addElement('Button', 'execute', array(
            'label' => 'Rename Topic',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array('ViewHelper'),
            'order' => 20,
        ));

        // Element: cancel
        $this->addElement('Cancel', 'cancel', array(
            'label' => 'cancel',
            'link' => true,
            'prependText' => ' or ',
            'href' => '',
            'onClick' => 'parent.Smoothbox.close();',
            'decorators' => array(
                'ViewHelper'
            ),
            'order' => 21,
        ));

        $this->addDisplayGroup(array(
            'execute',
            'cancel'
                ), 'buttons', array(
        ));
    }

}

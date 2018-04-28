<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Move.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Topic_Move extends Engine_Form {

    public function init() {
        $this
                ->setTitle('Move Topic')
        ;

        $this->addElement('Select', 'forum_id', array(
            'label' => 'Select Forum',
            'allowEmpty' => false,
            'required' => true,
        ));

        // Element: execute
        $this->addElement('Button', 'execute', array(
            'label' => 'Move Topic',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array('ViewHelper'),
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
        ));

        $this->addDisplayGroup(array(
            'execute',
            'cancel'
                ), 'buttons', array(
        ));
    }

}

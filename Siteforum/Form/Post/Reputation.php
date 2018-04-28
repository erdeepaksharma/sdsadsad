<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Reputation.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Post_Reputation extends Engine_Form {

    public function init() {
        $this->setTitle('Provide Reputation')
                ->setDescription('Provide reputation to this user by submitting below form.');

        $this->addElement('radio', 'reputation', array(
            'label' => 'Select the option',
            'multiOptions' => array(
                '1' => 'Increase',
                '0' => 'Decrease',
            ),
            'value' => 1,
        ));

        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'class' => 'mtop10',
            'decorators' => array(
                'ViewHelper',
            ),
        ));

        $this->addElement('Cancel', 'cancel', array(
            'label' => 'Cancel',
            'link' => true,
            'prependText' => ' or ',
            'decorators' => array(
                'ViewHelper',
            ),
            'onclick' => 'parent.Smoothbox.close();'
        ));

        $this->addDisplayGroup(array(
            'submit',
            'cancel'
                ), 'buttons', array(
            'decorators' => array(
                'FormElements'
            )
        ));

        $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))->setMethod('POST');
    }

}

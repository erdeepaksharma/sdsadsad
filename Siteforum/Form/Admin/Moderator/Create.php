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
class Siteforum_Form_Admin_Moderator_Create extends Engine_Form {

    public function init() {
        $this
                ->setTitle('Add Moderator')
                ->setDescription('Search for a member to add as a moderator for this forum.')
                ->setAttrib('id', 'siteforum_form_admin_moderator_create')
                ->setAttrib('class', 'global_form_popup')
        ;

        $this->addElement('Text', 'username', array(
            'label' => 'Member Name'
        ));

        $this->addElement('Hidden', 'user_id', array(
            'order' => 9000,
            'label' => 'User Identity',
            'required' => true,
            'allowEmpty' => false,
        ));

        // Buttons
        $this->addElement('Button', 'execute', array(
            'label' => 'Search',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array('ViewHelper')
        ));

        $this->addElement('Cancel', 'cancel', array(
            'label' => 'cancel',
            'link' => true,
            'prependText' => ' or ',
            'onclick' => 'parent.Smoothbox.close();',
            'decorators' => array(
                'ViewHelper'
            )
        ));

        $this->addDisplayGroup(array('execute', 'cancel'), 'buttons');
        $this->getDisplayGroup('buttons');
    }

}

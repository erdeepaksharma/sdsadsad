<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Create.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Wishlist_Create extends Engine_Form {

  public function init() {

    $this->setTitle('Create New Wishlist')
            ->setAttrib('id', 'form-upload-wishlist')
            ->setAttrib('enctype', 'multipart/form-data');

    $this->addElement('Text', 'title', array(
        'label' => 'Wishlist Name',
        'maxlength' => '63',
        'required' => true,
        'allowEmpty' => false,
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
            new Engine_Filter_StringLength(array('max' => '63')),
        )
    ));

    $this->addElement('Textarea', 'body', array(
        'label' => 'Wishlist Note',
        'maxlength' => '512',
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
            new Engine_Filter_StringLength(array('max' => '512')),
        )
    ));

    $availableLabels = array(
        'everyone' => 'Everyone',
        'registered' => 'All Registered Members',
        'owner_network' => 'Friends and Networks',
        'owner_member_member' => 'Friends of Friends',
        'owner_member' => 'Friends Only',
        'owner' => 'Just Me'
    );

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_wishlist', $viewer, 'auth_view');
    $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
    $orderPrivacyHiddenFields = 786590;
    if (count($viewOptions) == 1) {
      $this->addElement('hidden', 'auth_view', array('value' => key($viewOptions), 'order' => ++$orderPrivacyHiddenFields));
    }
    elseif (count($viewOptions) < 1) {
      $this->addElement('hidden', 'auth_view', array('value' => 'everyone', 'order' => ++$orderPrivacyHiddenFields));
    } else {
      $this->addElement('Select', 'auth_view', array(
          'label' => 'View Privacy',
          'description' => 'Who may see this wishlist?',
          'multiOptions' => $viewOptions,
          'value' => key($viewOptions),
      ));
      $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
    }

    $this->addElement('Button', 'submit', array(
        'label' => 'Create',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
        'type' => 'submit'
    ));

    $this->addElement('Cancel', 'cancel', array(
        'prependText' => ' or ',
        'label' => 'cancel',
        'link' => true,
        'onclick' => "javascript:parent.Smoothbox.close();",
        'decorators' => array(
            'ViewHelper'
        ),
    ));

    $this->addDisplayGroup(array(
        'submit',
        'cancel'
            ), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper',
        ),
    ));
  }

}
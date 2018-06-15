<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Email.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Review_Email extends Engine_Form {

  public $_error = array();

  public function init() {

    $this->setTitle('Email Review')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setDescription("Please fill the form below and then click on 'Send Email' button to send a mail to your friends to let them know about this Review.");

    // RECIVER EMAILS
    $this->addElement('Text', 'emailTo', array(
        'label' => 'To *',
        'allowEmpty' => false,
        'required' => true,
        'description' => 'Separate multiple addresses with commas.',
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    ));
    $this->emailTo->getDecorator("Description")->setOption("placement", "append");

    $this->addElement('textarea', 'userComment', array(
        'label' => 'Message *',
        'required' => true,
        'allowEmpty' => false,
        'attribs' => array('rows' => 24, 'cols' => 150, 'style' => 'width:230px; max-width:400px;height:120px;'),
        'value' => Zend_Registry::get('Zend_Translate')->_('Thought you would be interested in this.'),
        'description' => 'You can send a personal note in the mail.',
        'filters' => array(
            'StripTags',
            new Engine_Filter_HtmlSpecialChars(),
            new Engine_Filter_EnableLinks(),
            new Engine_Filter_Censor(),
        ),
    ));
    $this->userComment->getDecorator("Description")->setOption("placement", "append");

    $this->addElement('Button', 'send', array(
        'label' => 'Send Email',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'onclick' => 'javascript:parent.Smoothbox.close()',
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addDisplayGroup(array(
        'send',
        'cancel',
            ), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper'
        ),
    ));
  }

}
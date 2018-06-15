<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: TellAFriend.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Wishlist_TellAFriend extends Engine_Form {

    public $_error = array();

    public function init() {

        $this->setTitle('Tell a Friend')
                ->setDescription("Please fill the form below and then click on 'Tell a Friend' button to let your friends know about this Wishlist.");
        $this->setAttrib('class', 'global_form seaocore_form_comment');
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        $viewr_name = "";
        $viewr_email = "";
        if ($viewer_id > 0) {
            $viewr_name = $viewer->getTitle();
            $viewr_email = $viewer->email;
        }

        $this->addElement('Text', 'wishlist_sender_name', array(
            'label' => 'Your Name *',
            'allowEmpty' => false,
            'required' => true,
            'value' => $viewr_name,
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_StringLength(array('max' => '63')),
        )));

        $this->addElement('Text', 'wishlist_sender_email', array(
            'label' => 'Your Email *',
            'allowEmpty' => false,
            'required' => true,
            'value' => $viewr_email,
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_StringLength(array('max' => '63')),
        )));

        $this->addElement('Text', 'wishlist_reciver_emails', array(
            'label' => 'To *',
            'allowEmpty' => false,
            'required' => true,
            'description' => 'Separate multiple addresses with commas.',
                       'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
        ));

        $this->wishlist_reciver_emails->getDecorator("Description")->setOption("placement", "append");

        $this->addElement('textarea', 'wishlist_message', array(
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
        $this->wishlist_message->getDecorator("Description")->setOption("placement", "append");

        $this->addElement('Checkbox', 'wishlist_send_me', array(
            'label' => "Send a copy to my email address.",
        ));

        if (empty($viewer_id)) {
            if (Engine_Api::_()->hasModuleBootstrap('siterecaptcha')) {
                Zend_Registry::get('Zend_View')->recaptcha($this);
            } else {
                $this->addElement('captcha', 'captcha', array(
                    'description' => 'Please type the characters you see in the image.',
                    'captcha' => 'image',
                    'required' => true,
                    'captchaOptions' => array(
                        'wordLen' => 6,
                        'fontSize' => '30',
                        'timeout' => 300,
                        'imgDir' => APPLICATION_PATH . '/public/temporary/',
                        'imgUrl' => $this->getView()->baseUrl() . '/public/temporary',
                        'font' => APPLICATION_PATH . '/application/modules/Core/externals/fonts/arial.ttf'
                )));
                $this->captcha->getDecorator("Description")->setOption("placement", "append");
            }
        }

        $this->addElement('Button', 'wishlist_send', array(
            'label' => 'Tell a Friend',
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
            'wishlist_send',
            'cancel',
                ), 'wishlist_buttons', array(
            'decorators' => array(
                'FormElements',
                'DivDivDivWrapper'
            ),
        ));
    }

}

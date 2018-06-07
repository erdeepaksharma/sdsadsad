<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Create.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */

class Advancedactivity_Form_Admin_Greeting_Create extends Engine_Form {
  public function init() {

    // Init form
    $this
      ->setTitle('Add New Greeting / Announcement')
      ->setDescription('You can add a greeting / announcement for your users. Decorate the greeting / announcement with TINYMCE editor and set the duration and time for the greeting / announcement to be visible to users.')
      ->setAttrib('class', 'global_form')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
    ;

    $user_level = Engine_Api::_()->user()->getViewer()->level_id;
    $allowed_html = 'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr, iframe';
    $upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module'=>'advancedactivity','controller' =>'greeting','action' => 'upload-photo'), 'admin_default', true);
    
    $editorOptions = array(
      'upload_url' => $upload_url,
      'html' => (bool) $allowed_html,
    );

    if (!empty($upload_url))
    {
      $editorOptions['plugins'] = array(
        'table', 'fullscreen', 'media', 'preview', 'paste',
        'code', 'image', 'textcolor', 'jbimages', 'link'
      );

      $editorOptions['toolbar1'] = array(
        'undo', 'redo', 'removeformat', 'pastetext', '|', 'code',
        'media', 'image', 'jbimages', 'link', 'fullscreen',
        'preview'
      );
    }

    // Init name
    $this->addElement('Text', 'title', array(
      'label' => 'Greeting / Announcement Title',
      'maxlength' => '100',
      'allowEmpty' => false,
      'required' => true,
      'filters' => array(
        new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
        new Engine_Filter_StringLength(array('max' => '100')),
      )
    ));
   
    $this->addElement('TinyMce', 'body', array(
      'required' => true,
      'description' => 'Below you can create any type of greeting / announcement including images, video & rich text and for user name and photo you can simply use [USER_NAME] & [USER_PHOTO] respectivily in place of them.',
      'allowEmpty' => false,
      'editorOptions' => $editorOptions,
      'filters' => array(
        new Engine_Filter_Censor(),
        new Engine_Filter_Html(array('AllowedTags'=>$allowed_html))),
    ));
    $this->addElement('Radio', 'repeat', array(
            'label' => 'Duration of Greeting / Announcement',
            'description' => "Do you want to display this greeting / announcement everyday or for specific duration?",
            'multiOptions' => array(
                1 => 'Everyday',
                0 => 'Specific Duration'
            ),
            'onchange' => 'showHide(this.value)',
            'value' => 0,
        ));
    $starttime = new Engine_Form_Element_CalendarDateTime('starttime');
    $starttime->setLabel("Start Time");
    $starttime->setAllowEmpty(false);
    $starttime->setValue(date('Y-m-d H:i:s'));
    $this->addElement($starttime);


    //Start End date work
    $endtime = new Engine_Form_Element_CalendarDateTime('endtime');
    $endtime->setLabel("End Time");
    $endtime->setAllowEmpty(false);
    $endtime->setValue(date('Y-m-d H:i:s'));
    $this->addElement($endtime);
    
    $this->addElement('Radio', 'enabled', array(
            'label' => 'Enable Greeting / Announcement',
            'description' => "Do you want to enable this greeting / announcement?",
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => 1,
        ));

       // Init preview
    $this->addElement('Button', 'preview', array(
      'label' => 'Preview',
      'order' => 999,
      'prependText' => ' or ',
      'onclick' => 'showPreview()',
      'decorators' => array('ViewHelper'),
    ));
       // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Create',
      'type' => 'submit',
      'order' => 998,
      'decorators' => array('ViewHelper'),
    ));
    $this->addDisplayGroup(array('submit', 'preview'), 'buttons', array(
          'decorators' => array(
              'FormElements',
              'DivDivDivWrapper',
          )
      ));
  }

   
}

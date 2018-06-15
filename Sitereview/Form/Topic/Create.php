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
class Sitereview_Form_Topic_Create extends Engine_Form {

  public function init() {
    
    $this->setTitle('Post Discussion Topic')
            ->setAttrib('id', 'sitereview_topic_create');

    $this->addElement('Text', 'title', array(
        'label' => 'Title',
        'allowEmpty' => false,
        'required' => true,
        'filters' => array(
          'StripTags',
            new Engine_Filter_Censor(),
            new Engine_Filter_HtmlSpecialChars(),
        ),
        'validators' => array(
            array('StringLength', true, array(1, 64)),
        )
    ));

    if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.tinymceditor', 1)) {
      $this->addElement('Textarea', 'body', array(
          'label' => 'Message',
          'allowEmpty' => false,
          'required' => true,
          'filters' => array(
            'StripTags',
              new Engine_Filter_Censor(),
              new Engine_Filter_HtmlSpecialChars(),
              new Engine_Filter_EnableLinks(),
          ),
      ));
    } else {
      $this->addElement('TinyMce', 'body', array(
          'label' => 'Message',
          'allowEmpty' => false,
          'required' => true,
          'attribs' => array('rows' => 180, 'cols' => 350, 'style' => 'width:740px; max-width:740px;height:858px;'),

        'editorOptions' => Engine_Api::_()->seaocore()->tinymceEditorOptions(),
          'filters' => array(new Engine_Filter_Censor()),
      ));
    }

    $this->addElement('Checkbox', 'watch', array(
        'label' => 'Send me notifications when other members reply to this topic.',
        'value' => true,
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Post New Topic',
        'ignore' => true,
        'type' => 'submit',
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'prependText' => ' or ',
        'type' => 'link',
        'link' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }

}
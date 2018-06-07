<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Linkedin
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: LinkedinCompose.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_LinkedinCompose extends Engine_Form
{
  public function init()
  {
    $this->setTitle('Send a message')   
       ->setAttrib('id', 'linkedin_compose');
    //$user_level = Engine_Api::_()->user()->getViewer()->level_id;
    
    // init to
    $this->addElement('Text', 'to',array(
        'label'=>'Send To',
         'disabled' => 'disabled',
        'filters' => array(
                'StripTags',
                 new Engine_Filter_Censor()),
        ));

    Engine_Form::addDefaultDecorators($this->to);

    // Init to Values
    $this->addElement('Hidden', 'memberid', array(  
    'order' => 998,    
      'required' => true,
      'allowEmpty' => false,     
      'validators' => array(
        'NotEmpty'
      ),
      'filters' => array(
        'HtmlEntities'
      ),
    ));
    Engine_Form::addDefaultDecorators($this->memberid);

    // init title
    $this->addElement('Text', 'title', array(
      'label' => 'Subject',      
      'filters' => array(
        'StripTags',  
        new Engine_Filter_Censor(),
        new Engine_Filter_HtmlSpecialChars(),
      ),
    ));
    
    // init body - editor
    //$editor = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('messages', $user_level, 'editor');
    
  $this->addElement('Textarea', 'body', array(
        'label' => 'Message',        
        'required' => true,
        'allowEmpty' => false,
        'filters' => array(
           'StripTags',   
          new Engine_Filter_HtmlSpecialChars(),
          new Engine_Filter_Censor(),
          new Engine_Filter_EnableLinks(),
        ),
      ));
    // init submit
    $buttons = array();
    $this->addElement('Button', 'share', array(
        'label' => 'submit',
				'data-role' => 'button',
				'type' => 'submit',
				'class' => 'ui-btn-right',
        'onclick' => '$(".ui-page-active").removeClass("pop_back_max_height");sm4.socialactivity.linkedin.sendMessage($(this));return false;',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
    $buttons[] = 'submit';

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
				'data-role' => 'none',
				'class' => 'ui-link',
        'link' => true,
        'prependText' => ' or ',
        'href' => '',
        'onclick' => '$(".ui-page-active").removeClass("pop_back_max_height");$("#feedsharepopup").remove();$(window).scrollTop(parentScrollTop)',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $buttons[] = 'cancel';


    $this->addDisplayGroup($buttons, 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }
}
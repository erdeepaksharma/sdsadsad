<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Share.php 8968 2011-06-02 00:48:35Z john $
 * @author     John
 */
class Advancedactivity_Form_SitemobileShare extends Engine_Form {

  public function init() {
    $this
            ->setTitle('')
            ->setDescription('Share this by re-posting it with your own message.')
            ->setMethod('POST')
	    ->setAttrib('id', 'form_share_creation')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
    ;

    $this->addElement('Textarea', 'body', array(
        //'required' => true,
        //'allowEmpty' => false,
	'autofocus' => 'autofocus',
        'filters' => array(
            'StripTags',
            new Engine_Filter_HtmlSpecialChars(),
            new Engine_Filter_EnableLinks(),
            new Engine_Filter_Censor(),
        ),
    ));

    // Buttons
    $buttons = array();

    $translate = Zend_Registry::get('Zend_Translate');

    // Facebook
    $session = new Zend_Session_Namespace();
   $facebookApi = $facebook = Seaocore_Api_Facebook_Facebookinvite::getFBInstance();   
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('facebook.enable', Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable == 'publish'? 1:0) &&
            $facebookApi && Seaocore_Api_Facebook_Facebookinvite::checkConnection(null, $facebookApi)) { 
      $this->addElement('Dummy', 'post_to_facebooks', array(
          'content' => '
          <span href="javascript:void(0);" class="composer_facebook_toggle" onclick="toggleFacebookShareCheckbox();">
            <span class="cm-icons cm-icon-facebook">          
            </span>
            <input type="checkbox" name="post_to_facebooks" id="post_to_facebooks" value="1" style="display:none;">
          </span>',
      ));
      $this->getElement('post_to_facebooks')->clearDecorators();
      $buttons[] = 'post_to_facebook';
    }

    // Twitter
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('twitter.enable', Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable == 'publish'? 1:0 )) {
      $Api_twitter = Engine_Api::_()->getApi('twitter_Api', 'seaocore');
      $OBJ_twitter = $Api_twitter->getApi(); 
      if ($OBJ_twitter && 
              $Api_twitter->isConnected()) {
        $this->addElement('Dummy', 'post_to_twitters', array(
            'content' => '
          <span href="javascript:void(0);" class="composer_twitter_toggle" onclick="toggleTwitterShareCheckbox();">
            <span class="cm-icons cm-icon-twitter">           
            </span>
            <input type="checkbox" name="post_to_twitters" id="post_to_twitters" value="1" style="display:none;">
          </span>',
        ));
        $this->getElement('post_to_twitters')->clearDecorators();
        $buttons[] = 'post_to_twitter';
      }
    }
    
    // LinkedIn
$linkedin_enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.enable', 0);
    if ($linkedin_enable) { 
      $Api_linkedin = Engine_Api::_()->getApi('linkedin_Api', 'seaocore');
      $OBJ_linkedin = $Api_linkedin->getApi(); 
      if ($OBJ_linkedin && $Api_linkedin->isConnected()) {
        $this->addElement('Dummy', 'post_to_linkedin', array(
            'content' => '
          <span href="javascript:void(0);" class="composer_linkedin_toggle" onclick="toggleLinkedinShareCheckbox();">
            <span class="cm-icons cm-icon-linkedin">           
            </span>
            <input type="checkbox" name="post_to_linkedin" id="post_to_linkedin" value="1" style="display:none;">
          </span>',
        ));
        $this->getElement('post_to_linkedin')->clearDecorators();
        $buttons[] = 'post_to_linkedins';
     }
    }
		if(count($buttons)){
		 $this->addElement('Button', 'sharenone', array(
        'decorators' => array('ViewHelper')
    ));
    $buttons[] = 'sharenone';
	$this->addDisplayGroup($buttons, 'sbuttons');
    $this->getDisplayGroup('sbuttons');
		}
/*		$buttons = array();
    $this->addElement('Button', 'share', array(
        'label' => 'Share',
				'data-role' => 'button',
				'type' => 'submit',
				'class' => 'ui-btn-right',
        'onclick' => '$(".ui-page-active").removeClass("pop_back_max_height");sm4.activity.feedShare($(this));return false;',
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


	if(count($buttons)>1){
    $this->addDisplayGroup($buttons, 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
}*/
  }

}

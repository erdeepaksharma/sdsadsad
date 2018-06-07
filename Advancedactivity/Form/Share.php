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
class Advancedactivity_Form_Share extends Engine_Form {

  public function init() {
    $this
            ->setTitle('Share')
            ->setDescription('Share this by re-posting it with your own message.')
            ->setMethod('POST')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
    ;

    $this->addElement('Textarea', 'body', array(
        //'required' => true,
        //'allowEmpty' => false,
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
    $enable_socialdnamodule = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('socialdna');
    if (('publish' == Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable || $enable_socialdnamodule) &&
            $facebookApi && Seaocore_Api_Facebook_Facebookinvite::checkConnection(null, $facebookApi)) {
      $this->addElement('Dummy', 'post_to_facebook', array(
          'content' => '
          <span href="javascript:void(0);" class="composer_facebook_toggle aaf_share_tooltip_wrapper" onclick="toggleFacebookShareCheckbox();">
            <span class="aaf_composer_tooltip aaf_share_tooltip">
              ' . $translate->translate('Publish this on Facebook') . '
              <img src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" alt="" />
            </span>
            <input type="checkbox" name="post_to_facebook" value="1" style="display:none;">
          </span>',
      ));
      $this->getElement('post_to_facebook')->clearDecorators();
      $buttons[] = 'post_to_facebook';
    }

    // Twitter
    if ('publish' == Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable) {
      $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
      $Api_twitter = Engine_Api::_()->getApi('twitter_Api', 'seaocore');  
      $twitterOauth = $twitter = $Api_twitter->getApi();
      if ($twitterOauth && 
              $Api_twitter->isConnected()) {
        $this->addElement('Dummy', 'post_to_twitter', array(
            'content' => '
          <span href="javascript:void(0);" class="composer_twitter_toggle aaf_share_tooltip_wrapper" onclick="toggleTwitterShareCheckbox();">
            <span class="aaf_composer_tooltip aaf_share_tooltip">
              ' . $translate->translate('Publish this on Twitter') . '
            	<img src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" alt="" />  
            </span>
            <input type="checkbox" name="post_to_twitter" value="1" style="display:none;">
          </span>',
        ));
        $this->getElement('post_to_twitter')->clearDecorators();
        $buttons[] = 'post_to_twitter';
      }
    }

    $this->addElement('Button', 'submit', array(
        'label' => 'Share',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
    $buttons[] = 'submit';

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => '',
        'onclick' => 'parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $buttons[] = 'cancel';


    $this->addDisplayGroup($buttons, 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }

}
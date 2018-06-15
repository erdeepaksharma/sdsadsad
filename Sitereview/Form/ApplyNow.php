<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ApplyNow.php 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_ApplyNow extends Engine_Form {

  public $_error = array();

  public function init() {

    $params = Engine_Api::_()->sitereview()->getWidgetparams();
    $params = !empty($params) ? Zend_Json::decode($params) : array();

    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    $listingTypetitle = ucfirst(Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'title_singular'));
    if ($listingTypetitle == 'Job') {
      $fileTitle = 'Resume';
      $postTitle = 'Apply';
    } else {
      $fileTitle = 'File';
      $postTitle = 'Post';
    }

    if (isset($params['title']) && !empty($params['title']))
      $widgetTitle = $params['title'];
    else
      $widgetTitle = 'Apply Now';

    $this->setTitle($widgetTitle)
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setAttrib('name', 'sitereviews_create');

    $viewer = Engine_Api::_()->user()->getViewer();

    if (in_array('1', $params['show_option'])) {
      $this->addElement('Text', 'sender_name', array(
          'label' => 'Your Name *',
          'allowEmpty' => false,
          'required' => true,
          'value' => $viewer->displayname,
          'filters' => array(
              'StripTags',
              new Engine_Filter_Censor(),
              new Engine_Filter_StringLength(array('max' => '63')),
              )));
    }

    if (in_array('2', $params['show_option'])) {
      $this->addElement('Text', 'sender_email', array(
          'label' => 'Your Email *',
          'allowEmpty' => false,
          'required' => true,
          'value' => $viewer->email,
          'filters' => array(
              'StripTags',
              new Engine_Filter_Censor(),
              new Engine_Filter_StringLength(array('max' => '63')),
              )));
    }

    if (in_array('3', $params['show_option'])) {
      $this->addElement('Text', 'contact', array(
          'label' => 'Contact',
          'filters' => array(
              'StripTags',
              new Engine_Filter_Censor(),
              new Engine_Filter_StringLength(array('max' => '63')),
              )));
    }

    if (in_array('4', $params['show_option'])) {
      $this->addElement('File', 'filename', array(
          'label' => $fileTitle,
          'description' => '',
          'attribs' => array('size' => 10),
      ));
    }
    if (in_array('5', $params['show_option'])) {
      $this->addElement('textarea', 'body', array(
          'label' => 'Message *',
          'required' => true,
          'allowEmpty' => false,
          'attribs' => array('rows' => 24, 'cols' => 150, 'style' => 'width:230px; max-width:400px;height:120px;'),
          'value' => Zend_Registry::get('Zend_Translate')->_('Thought you would be interested in this.'),
          'filters' => array(
              'StripTags',
              new Engine_Filter_HtmlSpecialChars(),
              new Engine_Filter_EnableLinks(),
              new Engine_Filter_Censor(),
          ),
      ));
    }

    $this->addElement('Button', 'send', array(
        'label' => $postTitle,
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
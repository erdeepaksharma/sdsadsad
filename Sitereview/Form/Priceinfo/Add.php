<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Add.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Priceinfo_Add extends Engine_Form {

  public $_error = array();

  public function init() {

    //GET LISTING TYPE ID
    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $listing_singular_upper = strtoupper($listingtypeArray->title_singular);
    $listing_singular_lc = strtolower($listingtypeArray->title_singular);
    $this->setTitle('ADD_DASHBOARD_' . $listing_singular_upper . '_WHERE_TO_BUY_OPTION');
    $this->setDescription('Add DASHBOARD_' . $listing_singular_upper . '_WHERE_TO_BUY option for this ' . $listing_singular_lc . ' using the form below. Enter the name of the Store and other details where this ' . $listing_singular_lc . ' is available.');

    //GET LISTING TYPE ID
    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    $whereToBuyArray = array();
    $otherTitle = null;
    $whereToBuy = Engine_Api::_()->getItemTable('sitereview_wheretobuy')->getList(array('enabled' => 1));
    foreach ($whereToBuy as $item):
      if ($item->getIdentity() == 1):
        $otherTitle = $item->getTitle();
        continue;
      endif;
      $whereToBuyArray[$item->getIdentity()] = $item->getTitle();
    endforeach;
    if ($otherTitle)
      $whereToBuyArray[1] = $otherTitle;

    $this->addElement('Select', 'wheretobuy_id', array(
        'label' => $listing_singular_upper .'_STORE',
        'description' => "Select the Store where this $listing_singular_lc is available.",
        'required' => true,
        'allowEmpty' => false,
        'multioptions' => $whereToBuyArray,
        'onchange' => 'otherWhereToBuy(this.value)'
    ));
    $this->addElement('Text', 'title', array(
        'label' => 'Name',
        'style' => 'width:200px;',
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    ));

    $this->addElement('Text', 'url', array(
        'label' => $listing_singular_upper .'_STORE_URL',
        'description' => "Enter the URL of the Store's webpage where this $listing_singular_lc is available. (We recommend you to enter complete URL.)",
        'style' => 'width:200px;',
        'required' => true,
        'allowEmpty' => false,
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    ));

    if ($listingtypeArray->where_to_buy == 2) {
      $localeObject = Zend_Registry::get('Locale');
      $currencyCode = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
      $currencyName = Zend_Locale_Data::getContent($localeObject, 'nametocurrency', $currencyCode);
      $this->addElement('Text', 'price', array(
          'label' => sprintf(Zend_Registry::get('Zend_Translate')->_('Price %s'), $currencyName),
          'style' => 'width:100px;',
          //        'required' => true,
          //        'allowEmpty' => false,
          'validators' => array(
              array('Float', true),
          //  array('GreaterThan', true, array(0)),
          ),
      ));
    }

    $this->addElement('Text', 'address', array(
        'label' => 'Address', 'style' => 'width:200px;',
        'description' => 'ENTER_THE_ADDRESS_OF_'.$listing_singular_upper.'_STORE',
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    ));
    $this->addElement('Text', 'contact', array(
        'label' => 'Phone', 'style' => 'width:200px;',
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    ));

    $this->addElement('Button', 'execute', array(
        'label' => 'Add',
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
        'execute',
        'cancel',
            ), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper'
        ),
    ));
  }

}
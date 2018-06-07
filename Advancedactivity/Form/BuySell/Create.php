<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Create.php 8968 2011-06-02 00:48:35Z john $
 * @author     John
 */
class Advancedactivity_Form_BuySell_Create extends Engine_Form
{
  public function init()
  {
    $this
      ->setDescription('Attach product here to advertise.')
      ->setMethod('POST')
      ->setAttrib('id', 'form-upload')
      ->setAttrib('name', 'buy_sell')
      ->setAttrib('enctype', 'multipart/form-data')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
    ;
    $view = Zend_Registry::get('Zend_View');
     $this->addElement('Text', 'title', array(
      'required' => true,
      'label'=>'',
      'placeholder' => $view->translate('What to sell?'),
      'maxlength' => 100
    ));
    $this->addElement('Text', 'price', array(
      'required' => true,
      'label'=>'',
      'onkeypress'=>'return event.charCode >= 48 && event.charCode <= 57',
      'placeholder' => $view->translate('What is price?'),
      'maxlength' => 9
    ));
    
    if(Engine_Api::_()->hasModuleBootstrap('sitemulticurrency')){
        $currency = Engine_Api::_()->getDbTable('currencyrates', 'sitemulticurrency')->getAllowedCurrencies();
        $selected = Engine_Api::_()->sitemulticurrency()->getSelectedCurrency();
    } else {
        $translationList = Zend_Locale::getTranslationList('nametocurrency', Zend_Registry::get('Locale'));
        $symbols = array_keys($translationList);
        $currency = array_combine($symbols,$symbols);
        $selected = 'USD';
    }
    $this->addElement('Select', 'currency', array(
      'autocompleter' => 'off',
      'required' => true,
      'multiOptions' => $currency,
      'value' => $selected
    ));
    $this->addElement('Text', 'place', array(
      'autocompleter' => 'off',
      'required' => true,
      'placeholder' => $view->translate('Where to sell?')
    ));
    $this->addElement('Textarea', 'description', array(
      'required' => false,
      'label'=>'',
      'rows'=> '4',
      'placeholder' => $view->translate('Product description(optional)'),
      'maxlength' => 500
    ));
    if( !$this->_item ) {
    $customFields = new Advancedactivity_Form_Custom_Fields();
    } else {
      $customFields = new Advancedactivity_Form_Custom_Fields(array(
        'item'=>$this->getItem()
      ));
    }
    $this->addSubForms(array(
      'fields' => $customFields
    ));
  }

}

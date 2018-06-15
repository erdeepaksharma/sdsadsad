<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Address.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Address extends Engine_Form {

  public $_error = array();
  protected $_item;

  public function getItem() {
    return $this->_item;
  }

  public function setItem($item) {
    $this->_item = $item;
    return $this;
  }

  public function init() {

    $this->setTitle('Edit Location')
            ->setDescription('Edit your location below, then click "Save Location" to save your location.');

    //GET LISTINGTYPE ID
    $listingtype_id = $this->getItem()->listingtype_id;
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);

    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);

    if ($listingtypeArray->location) {
      $this->addElement('Text', 'location', array(
          'label' => 'Location',
          'description' => 'Eg: Fairview Park, Berkeley, CA',
          'filters' => array(
              'StripTags',
              new Engine_Filter_Censor(),
              )));
      $this->location->getDecorator('Description')->setOption('placement', 'append');
      $this->addElement('Hidden', 'locationParams', array( 'order' => 800000));
      
      
      include_once APPLICATION_PATH.'/application/modules/Seaocore/Form/specificLocationElement.php';      
    }

    $this->addElement('Button', 'submit', array(
        'label' => 'Save Location',
        'order' => '998',
        'type' => 'submit',
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'order' => '999',
        'onclick' => "javascript:parent.Smoothbox.close();",
        'href' => "javascript:void(0);",
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addDisplayGroup(array(
        'submit',
        'cancel',
            ), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper'
        ),
    ));
    $button_group = $this->getDisplayGroup('buttons');
    $button_group->setOrder('999');
  }

}
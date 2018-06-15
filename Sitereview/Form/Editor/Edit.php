<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Edit.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Editor_Edit extends Sitereview_Form_Editor_Create {

  public $_error = array();
  protected $_item;

  public function getItem() {
    return $this->_item;
  }

  public function setItem(Core_Model_Item_Abstract $item) {
    $this->_item = $item;
    return $this;
  }

  public function init() {
    parent::init();
    $listing_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id');
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    $this->loadDefaultDecorators();
    $sitereview_title = "<b>" . $sitereview->title . "</b>";

    $this
            ->setTitle('Edit an Editor Review')
            ->setDescription(sprintf(Zend_Registry::get('Zend_Translate')->_("You can edit the editor review for %s below:"), $sitereview_title))
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))->getDecorator('Description')->setOption('escape', false);

    $this->submit->setLabel('Save Changes');
  }

}
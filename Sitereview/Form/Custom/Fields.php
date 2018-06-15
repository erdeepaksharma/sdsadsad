<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Fields.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Custom_Fields extends Fields_Form_Standard {

  public $_error = array();
  protected $_name = 'fields';
  protected $_elementsBelongTo = 'fields';

  public function init() {
    if (!$this->_item) {
      $sitereview_item = new Sitereview_Model_Sitereview(array());
      $this->setItem($sitereview_item);
    }
    parent::init();

    $this->removeElement('submit');
  }

  public function loadDefaultDecorators() {
    if ($this->loadDefaultDecoratorsIsDisabled()) {
      return;
    }

    $decorators = $this->getDecorators();
    if (empty($decorators)) {
      $this
              ->addDecorator('FormElements');
    }
  }

}
<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: CompareButton.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_View_Helper_CompareButton extends Zend_View_Helper_Abstract {

  /**
   * Assembles action string
   * 
   * @return string
   */
  public function compareButton($item, $buttonType=null) {

    if (!(isset($item->subsubcategory_id) && isset($item->subcategory_id) && isset($item->category_id))) {
      $item = Engine_Api::_()->getItem('sitereview_listing', $item->getIdentity());
    }

    if (!Zend_Registry::isRegistered('sr_compare_item_listingtype_enabled_' . $item->listingtype_id)) {
      $listingType = Zend_Registry::get('listingtypeArray' . $item->listingtype_id);
      Zend_Registry::set('sr_compare_item_listingtype_enabled_' . $item->listingtype_id, $listingType->compare);
    }

    if (!Zend_Registry::get('sr_compare_item_listingtype_enabled_' . $item->listingtype_id))
      return;
    $complareFage = false;
    $data = array();
    $category_id = $item->category_id;
    if (!Zend_Registry::isRegistered('sr_compare_item_category_disabled_' . $category_id)) {
      $category = Engine_Api::_()->getItem('sitereview_category', $category_id);
      if ($category && $category->apply_compare) {
        $complareFage = true;
      } else {
        Zend_Registry::set('sr_compare_item_category_disabled_' . $category_id, $data);
      }
    }
    if (!$complareFage && $item->subcategory_id) {
      $category_id = $item->subcategory_id;
      if (!Zend_Registry::isRegistered('sr_compare_item_category_disabled_' . $category_id)) {
        $category = Engine_Api::_()->getItem('sitereview_category', $category_id);
        if ($category->apply_compare) {
          $complareFage = true;
        } else {
          Zend_Registry::set('sr_compare_item_category_disabled_' . $category_id, $data);
        }
      }
    }
 
    if (!$complareFage && $item->subsubcategory_id) {
      $category_id = $item->subsubcategory_id;
      if (!Zend_Registry::isRegistered('sr_compare_item_category_disabled_' . $category_id)) {
        $category = Engine_Api::_()->getItem('sitereview_category', $category_id);
        if ($category->apply_compare) {
          $complareFage = true;
        } else {
          Zend_Registry::set('sr_compare_item_category_disabled_' . $category_id, $data);
        }
      }
    }

    if (Zend_Registry::isRegistered('sr_compare_item_category_disabled_' . $category_id)) {
      return;
    }
    $data = array();
    if (Zend_Registry::isRegistered('sr_compare_item_category_' . $category_id)) {
      $data = Zend_Registry::get('sr_compare_item_category_' . $category_id);
    } else {

      $compareSettingsTable = Engine_Api::_()->getDbtable('compareSettings', 'sitereview');
      $this->view->compareSettingList = $result = $compareSettingsTable->getCompareList(array(
          'category_id' => $category_id,
          'fetchRow' => 1
              ));
      if (empty($result) || !$result->enabled) {
        Zend_Registry::set('sr_compare_item_category_disabled_' . $category_id, $data);
        return;
      }

      $data = array('category_title' => $category->category_name, 'category_id' => $category_id);
      Zend_Registry::set('sr_compare_item_category_' . $category_id, $data);
    }

    $data['item'] = $item;
    $data['buttonType'] = $buttonType;
    return $this->view->partial(
                    '_compareButton.tpl', 'sitereview', $data
    );
  }

}
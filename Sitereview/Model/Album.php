<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Album.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_Album extends Core_Model_Item_Collection {

  protected $_searchTriggers = false;
  protected $_modifiedTriggers = false;
  protected $_parent_type = 'sitereview_listing';
  protected $_owner_type = 'user';
  protected $_children_types = array('sitereview_photo');
  protected $_collectible_type = 'sitereview_photo';

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array()) {
    
    return $this->getOwner()->getHref($params);
  }

  public function getAuthorizationItem() {
    
    return $this->getParent('sitereview_listing');
  }

  protected function _delete() {
    
    //DELTE ALL CHILD POST
    $photoTable = Engine_Api::_()->getItemTable('sitereview_photo');
    $photoSelect = $photoTable->select()->where('album_id = ?', $this->getIdentity());
    foreach ($photoTable->fetchAll($photoSelect) as $sitereviewPhoto) {
      $sitereviewPhoto->delete();
    }
    parent::_delete();
  }

}
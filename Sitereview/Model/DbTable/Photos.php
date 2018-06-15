<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Photos.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Photos extends Engine_Db_Table {

  protected $_rowClass = 'Sitereview_Model_Photo';

  public function getPhotoId($listing_id = null, $file_id = null) {

    $photo_id = 0;
    $photo_id = $this->select()
            ->from($this->info('name'), array('photo_id'))
            ->where("listing_id = ?", $listing_id)
            ->where("file_id = ?", $file_id)
            ->query()
            ->fetchColumn();

    return $photo_id;
  }

  /**
   * Return photos
   *
   * @param string $listing_id
   * @return photos
   */
  public function GetListingPhoto($listing_id, $params = array()) {

    $select = $this->select()
            ->where('listing_id = ?', $listing_id);
    if (isset($params['show_slidishow']))
      $select->where('show_slidishow = ?', $params['show_slidishow']);

    if (isset($params['limit']) && !empty($params['limit']))
      $select->limit($params['limit']);

    if (isset($params['order']) && !empty($params['order']))
      $select->order($params['order']);
    return $this->fetchAll($select)->toArray();
  }

}
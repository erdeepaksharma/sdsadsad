<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Sell.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
class Advancedactivity_Model_Sell extends Core_Model_Item_Abstract {

    protected $_searchTriggers = false;

    // Properties
    public function getAllPhotos($photo_ids = null) {
        if (empty($photo_ids)) {
            return array();
        }
        foreach (array_filter(explode(" ", $photo_ids)) as $photo_id) {

            $photo = Engine_Api::_()->getItem('album_photo', $photo_id);
            $photo ? $images[$photo_id] = $photo->getPhotoUrl() : '';
        }
        return $images;
    }
    public function getPhotoUrl($type = null) {
        $photo_ids =  explode(" ", $this->photo_id);
        if (empty($photo_ids[0])) {
          return null;
        }
        $photo = Engine_Api::_()->getItem('album_photo', $photo_ids[0]);
        if (!$photo) {
          return null;
        }
        return $photo->getPhotoUrl();
    }
}

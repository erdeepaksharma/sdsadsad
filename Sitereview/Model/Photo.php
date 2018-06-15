<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Photo.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_Photo extends Core_Model_Item_Collectible {

  protected $_parent_type = 'sitereview_album';
  protected $_owner_type = 'user';
  protected $_searchColumns = array("");
  protected $_collection_type = 'sitereview_album';

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array()) {

    //GET LISTING TYPE ID
    $listingtype_id = Engine_Api::_()->getDbTable('listings', 'sitereview')->getListingTypeId($this->listing_id);

    $params = array_merge(array(
        'route' => "sitereview_image_specific_listtype_$listingtype_id",
        'reset' => true,
        'listing_id' => $this->listing_id,
        'photo_id' => $this->getIdentity(),
            ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble($params, $route, $reset);
  }

  public function getPhotoUrl($type = null) {
    
    if (empty($this->file_id)) {
      return null;
    }

    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, $type);
    if (!$file) {
      return null;
    }

    return $file->map();
  }

  /**
   * Set a photo
   *
   * @param array photo
   * @return photo object
   */
  public function setPhoto($photo) {

    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if ($photo instanceof Storage_Model_File) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }

    if (!$fileName) {
      $fileName = $file;
    }

    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => $this->getType(),
        'parent_id' => $this->getIdentity(),
        'user_id' => $this->user_id,
        'name' => $fileName,
    );

    // Add autorotation for uploded images. It will work only for SocialEngine-4.8.9 Or more then.
    $usingLessVersion = Engine_Api::_()->seaocore()->usingLessVersion('core', '4.8.9');
    if(!empty($usingLessVersion)) {
      $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
      $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(720, 720)
              ->write($mainPath)
              ->destroy();
      $normalPath = $path . DIRECTORY_SEPARATOR . $base . '_in.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(140, 160)
              ->write($normalPath)
              ->destroy();
      $normalLargePath = $path . DIRECTORY_SEPARATOR . $base . '_inl.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(250, 250)
              ->write($normalLargePath)
              ->destroy();
    }else {
      $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
      $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->autoRotate()
              ->resize(720, 720)
              ->write($mainPath)
              ->destroy();
      $normalPath = $path . DIRECTORY_SEPARATOR . $base . '_in.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->autoRotate()
              ->resize(140, 160)
              ->write($normalPath)
              ->destroy();
      $normalLargePath = $path . DIRECTORY_SEPARATOR . $base . '_inl.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->autoRotate()
              ->resize(250, 250)
              ->write($normalLargePath)
              ->destroy();
    }

    //RESIZE IMAGE (ICON)
    $iSquarePath = $path . DIRECTORY_SEPARATOR . $base . '_is.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file);

    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 48, 48)
            ->write($iSquarePath)
            ->destroy();
    try {
      $iMain = $filesTable->createFile($mainPath, $params);
      $iIconNormal = $filesTable->createFile($normalPath, $params);
      $iMain->bridge($iIconNormal, 'thumb.normal');
      $iIconNormalLarge = $filesTable->createFile($normalLargePath, $params);
      $iMain->bridge($iIconNormalLarge, 'thumb.large');
      $iSquare = Engine_Api::_()->storage()->create($iSquarePath, $params);
      $iMain->bridge($iSquare, 'thumb.icon');
    } catch (Exception $e) {
      @unlink($mainPath);
      @unlink($normalPath);
      @unlink($normalLargePath);
      @unlink($iSquarePath);
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        throw new Album_Model_Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    @unlink($mainPath);
    @unlink($normalPath);
    @unlink($normalLargePath);
    $this->modified_date = date('Y-m-d H:i:s');
    $this->file_id = $iMain->file_id;
    $this->save();
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    return $this;
  }

  public function isSearchable() {
    
    $collection = $this->getCollection();
    if (!$collection instanceof Core_Model_Item_Abstract) {
      return false;
    }
    return $collection->isSearchable();
  }

  public function getAuthorizationItem() {
    
    return $this->getParent('sitereview_listing');
  }

  public function comments() {
    
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
  }

  public function likes() {
    
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
  }

  public function tags() {
    
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
  }

  protected function _postDelete() {
    
    if ($this->_disableHooks)
      return;

    // This is dangerous, what if something throws an exception in postDelete
    // after the files are deleted?
    try {
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id);
      if ($file)
        $file->remove();
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, 'thumb.normal');
      if ($file)
        $file->remove();
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, 'thumb.large');
      if ($file)
        $file->remove();
      $album = $this->getCollection();
      $nextPhoto = $this->getNextCollectible();

      if (($album instanceof Core_Model_Item_Collection) && ($nextPhoto instanceof Core_Model_Item_Collectible) &&
              (int) $album->photo_id == (int) $this->file_id) {
        $album->photo_id = $nextPhoto->file_id;
        $album->save();
        $subject_sitereview = Engine_Api::_()->getItem('sitereview_listing', $this->listing_id);
        $subject_sitereview->photo_id = $nextPhoto->file_id;
        $subject_sitereview->save();
        $subject_sitereview->updateAllCoverPhotos();
      } elseif (($album instanceof Core_Model_Item_Collection) && (int) $album->photo_id == (int) $this->file_id) {
        Engine_Api::_()->getItemTable('sitereview_listing')->update(array('photo_id' => 0), array('photo_id = ?' => $this->file_id, 'listing_id = ? ' => $this->listing_id));
        Engine_Api::_()->getItemTable('sitereview_album')->update(array('photo_id' => 0), array('photo_id = ? ' => $this->file_id, 'album_id = ? ' => $this->album_id));
      }
    } catch (Exception $e) {
      // @todo completely silencing them probably isn't good enough
      //throw $e;
    }
  }

}
<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Sticker.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_Feeling extends Core_Model_Item_Abstract {
  protected $_searchTriggers = false;

  /**
   * Gets a url to the current photo representing this item. Return null if none
   * set
   *
   * @param string The photo type (null -> main, thumb, icon, etc);
   * @return string The photo url
   */
  public function getPhotoUrl($type = null) {
    $photo_id = $this->file_id;
    if (!$photo_id) {
      return null;
    }

    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($photo_id, $type);
    if (!$file) {
      return null;
    }

    return $file->map();
  }

  public function setSticker($sticker) {
    if ($sticker instanceof Zend_Form_Element_File) {
      $file = $sticker->getFileName();
      $fileName = $file;
    } else if ($sticker instanceof Storage_Model_File) {
      $file = $sticker->temporary();
      $fileName = $sticker->name;
    } else if ($sticker instanceof Core_Model_Item_Abstract && !empty($sticker->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $sticker->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($sticker) && !empty($sticker['tmp_name'])) {
      $file = $sticker['tmp_name'];
      $fileName = $sticker['name'];
    } else if (is_string($sticker) && file_exists($sticker)) {
      $file = $sticker;
      $fileName = $sticker;
    } else {
      throw new Core_Model_Exception('invalid argument passed to setPhoto');
    }

    if (!$fileName) {
      $fileName = basename($file);
    }

    $extension = ltrim(strrchr(basename($fileName), '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';

    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');

    // Resize image (main)
    $bigIconPath = $path . DIRECTORY_SEPARATOR . $base . '_b-i.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(320, 320)
      ->write($bigIconPath)
      ->destroy();

    // Resize image (profile)
    $smallIconPath = $path . DIRECTORY_SEPARATOR . $base . '_s-i.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(64, 64)
      ->write($smallIconPath)
      ->destroy();


    // Store
    $bigIcon = $filesTable->createSystemFile($bigIconPath);
    $smallIcon = $filesTable->createSystemFile($smallIconPath);
    $bigIcon->bridge($smallIcon, 'thumb.small-icon');

    // Remove temp files
    @unlink($bigIconPath);
    @unlink($smallIconPath);
    $this->title = trim(str_replace(array('-', '_'), ' ', preg_replace('/[0-9]+/', '', $base)));
    $this->file_id = $bigIcon->getIdentity();
    $this->save();
    return $this;
  }

}

<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Collection.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_Feelingtype extends Core_Model_Item_Abstract {
  protected $_searchTriggers = false;

  public function getPhotoUrl($type = null) {
    if (empty($this->photo_id)) {
      $feelingTable = Engine_Api::_()->getItemTable('advancedactivity_feeling');
      $iconInfo = $feelingTable->select()
        ->from($feelingTable, array('feeling_id', 'file_id'))
        ->where('feelingtype_id = ?', $this->feelingtype_id)
        ->order('order ASC')
        ->limit(1)
        ->query()
        ->fetch();
      if (!empty($iconInfo)) {
        $this->photo_id = $iconInfo['file_id'];
        $this->save();
        $file_id = $iconInfo['file_id'];
      } else {
        return;
      }
    } else {
      $file_id = $this->photo_id;
    }

    if (!$file_id) {
      return;
    }

    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($file_id, $type);
    if (!$file) {
      return;
    }

    return $file->map();
  }

  public function getFeelings() {
    $feelingTable = Engine_Api::_()->getItemTable('advancedactivity_feeling');
    $select = $feelingTable->select()
      ->where('feelingtype_id = ?', $this->feelingtype_id)
      ->order('order ASC');
    return $feelingTable->fetchAll($select);
  }

  public function getFirstFeeling() {
    $feelingTable = Engine_Api::_()->getItemTable('advancedactivity_feeling');
    $select = $feelingTable->select()
      ->where('feelingtype_id = ?', $this->feelingtype_id)
      ->order('order ASC')
      ->limit(1);
    return $feelingTable->fetchRow($select);
  }

  public function getLastFeeling() {
    $feelingTable = Engine_Api::_()->getItemTable('advancedactivity_feeling');
    $select = $feelingTable->select()
      ->where('feelingtype_id = ?', $this->feelingtype_id)
      ->order('order DESC')
      ->limit(1);
    return $feelingTable->fetchRow($select);
  }

  public function count() {
    $feelingTable = Engine_Api::_()->getItemTable('advancedactivity_feeling');
    return $feelingTable->select()
        ->from($feelingTable, new Zend_Db_Expr('COUNT(feeling_id)'))
        ->where('feelingtype_id = ?', $this->feelingtype_id)
        ->limit(1)
        ->query()
        ->fetchColumn();
  }

  public function createFeelings($feelings = array()) {
    $feelingTable = Engine_Api::_()->getDbtable('feelings', 'advancedactivity');
    $feelingRows = array();
    foreach ($feelings as $feelingData) {
      $feeling = $feelingTable->createRow();
      $feeling->save();
      $feeling->order = $feeling->feeling_id;
      $feeling->feelingtype_id = $this->feelingtype_id;
      $feeling->setSticker($feelingData['Filedata']);
      $feeling->save();
      $feelingRows[] = $feeling;
    }

    if (!$this->photo_id && $feelingRows) {
      $this->photo_id = $feelingRows[0]->file_id;
      $this->save();
    }
    return $feelingRows;
  }
  public function setContentIcon($icon) {
    if ($icon instanceof Zend_Form_Element_File) {
      $file = $icon->getFileName();
      $fileName = $file;
    } else if ($icon instanceof Storage_Model_File) {
      $file = $icon->temporary();
      $fileName = $icon->name;
    } else if ($icon instanceof Core_Model_Item_Abstract && !empty($icon->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $icon->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($icon) && !empty($icon['tmp_name'])) {
      $file = $icon['tmp_name'];
      $fileName = $icon['name'];
    } else if (is_string($icon) && file_exists($icon)) {
      $file = $icon;
      $fileName = $icon;
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
    return $bigIcon->getIdentity();;

  }

}

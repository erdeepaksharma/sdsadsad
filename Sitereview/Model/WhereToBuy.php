<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: WhereToBuy.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_WhereToBuy extends Core_Model_Item_Abstract {

  /**
   * Set listing photo
   *
   * */
  public function setPhoto($photo) {
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
    } else {
      throw new Engine_Exception('invalid argument passed to setPhoto');
    }

    $name = basename($file);
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';

    // Add autorotation for uploded images. It will work only for SocialEngine-4.8.9 Or more then.
    $usingLessVersion = Engine_Api::_()->seaocore()->usingLessVersion('core', '4.8.9');
    if(!empty($usingLessVersion)) {
      $image = Engine_Image::factory();
      $thumb_file = $path . '/in_' . $name;
      $image->open($file)
              ->resize(120, 240)
              ->write($thumb_file)
              ->destroy();
    }else {
      $image = Engine_Image::factory();
      $thumb_file = $path . '/in_' . $name;
      $image->open($file)
              ->autoRotate()
              ->resize(120, 240)
              ->write($thumb_file)
              ->destroy();
    }

    try {
      $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
          'parent_type' => $this->getType(),
          'parent_id' => $this->getIdentity()
              ));

      // Remove temp file
      @unlink($thumb_file);
    } catch (Exception $e) {
      
    }

    $this->photo_id = $thumbFileRow->file_id;

    $this->save();

    return $this;
  }

}
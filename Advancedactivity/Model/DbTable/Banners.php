<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Banners.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_Banners extends Engine_Db_Table
{

  protected $_rowClass = 'Advancedactivity_Model_Banner';

  public function getBanners($params = array())
  {
    $api = Engine_Api::_()->getApi('settings', 'core');
    $limit = $api->getSetting('advancedactivity.banner.count', 10);
    $order = $api->getSetting('advancedactivity.feed.banner.order', 'random');
    $select = $this->select()
      ->where('enabled =? ', 1)
      ->where('startdate <= now()')
      ->limit($limit);
    if( $order !== 'random' ) {
      $select->order('order ASC');
    } else {
      $select->order('Rand()');
    }
    $banners = $this->fetchAll($select);
    $data = array();
    $dataHighlighted = array();
    foreach( $banners as $banner ) {
      if( $banner->enddate != '0000-00-00' && $banner->enddate < date('Y-m-d') ) {
        continue;
      }
      $bannersFormat = array();
      $bannersFormat['backgroundImage'] = 'none';
      if( !empty($banner->file_id) ) {
        $file = Engine_Api::_()->getDbtable('files', 'storage')->getFile($banner->file_id);
        $bannersFormat['backgroundImage'] = $file ? 'url(' . $file->getHref() . ')' : 'none';
      } else if( $banner->gradient ) {
        $bannersFormat['backgroundImage'] = $banner->gradient;
      }
      $bannersFormat['backgroundColor'] = $banner->background_color;
      $bannersFormat['color'] = $banner->color;
      $bannersFormat['highlighted'] = $banner->highlighted;
      if( $banner->highlighted ) {
        $dataHighlighted[] = $bannersFormat;
        continue;
      }
      $data[] = $bannersFormat;
    }

    return array_merge($dataHighlighted, $data);
  }

  public function setPhoto($banner)
  {
    if( $banner instanceof Zend_Form_Element_File ) {
      $file = $banner->getFileName();
      $fileName = $file;
    } else if( $banner instanceof Storage_Model_File ) {
      $file = $banner->temporary();
      $fileName = $banner->name;
    } else if( $banner instanceof Core_Model_Item_Abstract && !empty($banner->file_id) ) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $banner->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if( is_array($banner) && !empty($banner['tmp_name']) ) {
      $file = $banner['tmp_name'];
      $fileName = $banner['name'];
    } else if( is_string($banner) && file_exists($banner) ) {
      $file = $banner;
      $fileName = $banner;
    } else {
      throw new Core_Model_Exception('invalid argument passed to setPhoto');
    }

    if( !$fileName ) {
      $fileName = basename($file);
    }

    $extension = ltrim(strrchr(basename($fileName), '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';

    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');

    // Resize image (main)
    $bigBannerPath = $path . DIRECTORY_SEPARATOR . $base . '_b-i.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(750, 350)
      ->write($bigBannerPath)
      ->destroy();

    // Resize image (profile)
    $smallBannerPath = $path . DIRECTORY_SEPARATOR . $base . '_s-i.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(350, 200)
      ->write($smallBannerPath)
      ->destroy();


    // Store
    $bigBanner = $filesTable->createSystemFile($bigBannerPath);
    $smallBanner = $filesTable->createSystemFile($smallBannerPath);
    $bigBanner->bridge($smallBanner, 'thumb.normal');

    // Remove temp files
    @unlink($bigBannerPath);
    @unlink($smallBannerPath);

    return $bigBanner->getIdentity();
  }

}

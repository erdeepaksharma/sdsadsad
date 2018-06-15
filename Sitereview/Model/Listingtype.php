<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Listingtype.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_Listingtype extends Core_Model_Item_Abstract {

  protected $_searchTriggers = false;
  const IMAGE_WIDTH = 720;
  const IMAGE_HEIGHT = 720;
  const THUMB_WIDTH = 140;
  const THUMB_HEIGHT = 160;
  const THUMB_LARGE_WIDTH = 250;
  const THUMB_LARGE_HEIGHT = 250;

  public function getTitle($inflect = false) {
    
    if ($inflect) {
      return ucwords($this->title_plural);
    } else {
      return $this->title_plural;
    }
  }

  public function getHref($params = array()) {
    
    $params = array_merge(array(
        'route' => "sitereview_general_listtype_$this->listingtype_id",
        'action' => isset($this->redirection) ? $this->redirection : 'home',
        'reset' => true,
            ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble($params, $route, $reset);
  }

  public function getPhotoUrl($type = null) {
    
    $photo_id = $this->photo_id;
    $photo_type = $this->photo_type;
    if ($photo_type == 'user') {
      $module = "User";
      $shorType = "user";
      if ($type == 'thumb_normal' || $type == 'thumb.normal' || $type == 'thumb_main' || $type == 'thumb.main')
        $type = 'thumb_profile';
    } else {
      $module = "Sitereview";
      $shorType = "listing";
    }
    if (empty($photo_id)) {
      $type = ( $type ? str_replace('.', '_', $type) : 'main' );
      return Zend_Registry::get('Zend_View')->layout()->staticBaseUrl . 'application/modules/' . $module . '/externals/images/nophoto_' . $shorType . '_'
              . $type . '.png';
    }

    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($photo_id, $type);
    if (!$file) {
      $type = ( $type ? str_replace('.', '_', $type) : 'main' );
      return Zend_Registry::get('Zend_View')->layout()->staticBaseUrl . 'application/modules/' . $module . '/externals/images/nophoto_' . $shorType . '_'
              . $type . '.png';
    }

    return $file->map();
  }

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
      throw new Classified_Model_Exception('invalid argument passed to setPhoto');
    }

    if (!$fileName) {
      $fileName = basename($file);
    }
    $extension = ltrim(strrchr(basename($fileName), '.'), '.');
    $name = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    //GET IMAGE INFO AND RESIZE
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
//    $extension = ltrim(strrchr($file['name'], '.'), '.');

    $mainName = $path . '/m_' . $name . '.' . $extension;
    $thumbName = $path . '/t_' . $name . '.' . $extension;
    $thumbLargeName = $path . '/t_l_' . $name . '.' . $extension;
    $thumbMidumName = $path . '/t_m_' . $name . '.' . $extension;

    // Add autorotation for uploded images. It will work only for SocialEngine-4.8.9 Or more then.
    $usingLessVersion = Engine_Api::_()->seaocore()->usingLessVersion('core', '4.8.9');
    if(!empty($usingLessVersion)) {
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(self::IMAGE_WIDTH, self::IMAGE_HEIGHT)
              ->write($mainName)
              ->destroy();

      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(self::THUMB_WIDTH, self::THUMB_HEIGHT)
              ->write($thumbName)
              ->destroy();
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(self::THUMB_LARGE_WIDTH, self::THUMB_LARGE_HEIGHT)
              ->write($thumbLargeName)
              ->destroy();

      //RESIZE IMAGE (Midum)
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(200, 200)
              ->write($thumbMidumName)
              ->destroy();
    }else {
      $image = Engine_Image::factory();
      $image->open($file)
              ->autoRotate()
              ->resize(self::IMAGE_WIDTH, self::IMAGE_HEIGHT)
              ->write($mainName)
              ->destroy();

      $image = Engine_Image::factory();
      $image->open($file)
              ->autoRotate()
              ->resize(self::THUMB_WIDTH, self::THUMB_HEIGHT)
              ->write($thumbName)
              ->destroy();
      $image = Engine_Image::factory();
      $image->open($file)
              ->autoRotate()
              ->resize(self::THUMB_LARGE_WIDTH, self::THUMB_LARGE_HEIGHT)
              ->write($thumbLargeName)
              ->destroy();

      //RESIZE IMAGE (Midum)
      $image = Engine_Image::factory();
      $image->open($file)
              ->autoRotate()
              ->resize(200, 200)
              ->write($thumbMidumName)
              ->destroy();
    }

    //RESIZE IMAGE (ICON)
    $iSquarePath = $path . '/is_' . $name . '.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file);

    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 48, 48)
            ->write($iSquarePath)
            ->destroy();
    
    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    $photoFile = $filesTable
            ->createSystemFile($mainName);
    $thumbFile = $filesTable
            ->createSystemFile($thumbName);
    //Engine_Api::_()->storage()->create($thumbName, $photo_params);
    $photoFile->bridge($thumbFile, 'thumb.normal');

    $thumbLargeFile = $filesTable
            ->createSystemFile($thumbLargeName);  //Engine_Api::_()->storage()->create($thumbLargeName, $photo_params);
    $photoFile->bridge($thumbLargeFile, 'thumb.large');
    $thumbMidumFile = $filesTable
            ->createSystemFile($thumbMidumName);  //Engine_Api::_()->storage()->create($thumbLargeName, $photo_params);
    $photoFile->bridge($thumbMidumFile, 'thumb.midum');

    $iSquare = $filesTable
            ->createSystemFile($iSquarePath); //Engine_Api::_()->storage()->create($iSquarePath, $photo_params);
    $photoFile->bridge($iSquare, 'thumb.icon');
    //REMOVE TEMP FILES
    @unlink($mainName);
    @unlink($thumbName);
    @unlink($thumbLargeName);
    @unlink($iSquarePath);
    $this->removePhoto();
    $this->photo_id = $photoFile->getIdentity();
    $this->save();
    return $this;
  }

  public function removePhoto() {
    
    if (empty($this->photo_id))
      return;
    
    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id);
    if ($file)
      $file->remove();
    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, 'thumb.normal');
    if ($file)
      $file->remove();
    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, 'thumb.large');
    if ($file)
      $file->remove();
    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, 'thumb.icon');
    if ($file)
      $file->remove();
    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, 'thumb.midum');
    if ($file)
      $file->remove();
  }

  protected function _postInsert() {
    
    parent::_postInsert();

    if (Engine_Api::_()->hasItemType('advancedactivity_content')) {
      Engine_Api::_()->advancedactivity()->contentTabSettings('sitereview_listtype_' . $this->listingtype_id, 'add', array('module_name' => 'sitereviewlistingtype', 'resource_title' => ucfirst($this->title_plural)));
    }
    if (Engine_Api::_()->hasItemType('advancedactivity_customtype')) {
      Engine_Api::_()->advancedactivity()->customListSettings('sitereview_listing_listtype_' . $this->listingtype_id, 'add', array('module_name' => 'sitereviewlistingtype', 'resource_title' => ucfirst($this->title_plural)));
    }
  }

  protected function _postUpdate() {
    parent::_postUpdate();
    if (Engine_Api::_()->hasItemType('advancedactivity_content')) {
      Engine_Api::_()->advancedactivity()->contentTabSettings('sitereview_listtype_' . $this->listingtype_id, 'add', array('module_name' => 'sitereviewlistingtype', 'resource_title' => ucfirst($this->title_plural)));
    }
    if (Engine_Api::_()->hasItemType('advancedactivity_customtype')) {
      Engine_Api::_()->advancedactivity()->customListSettings('sitereview_listing_listtype_' . $this->listingtype_id, 'add', array('module_name' => 'sitereviewlistingtype', 'resource_title' => ucfirst($this->title_plural)));
    }
  }

  /**
     * Delete the listingtype and belongings
     * 
     */
    public function _delete() {

        //$db = Engine_Db_Table::getDefaultAdapter();
        //$db->beginTransaction();
        try {

            $listingTypeApi = Engine_Api::_()->getApi('listingType', 'sitereview');
            $listingTypeApi->widgetizedPagesDelete($this, 'top-rated');
            $listingTypeApi->widgetizedPagesDelete($this, 'home');
            $listingTypeApi->widgetizedPagesDelete($this, 'index');
            $listingTypeApi->widgetizedPagesDelete($this, 'view');
            $listingTypeApi->widgetizedPagesDelete($this, 'map');
            $listingTypeApi->widgetizedPagesDelete($this, 'create');
            $listingTypeApi->widgetizedPagesDelete($this, 'edit');
            $listingTypeApi->widgetizedPagesDelete($this, 'manage');
            $listingTypeApi->mainNavigationDelete($this);
            $listingTypeApi->gutterNavigationDelete($this);
            $listingTypeApi->activityFeedQueryDelete($this);
            $listingTypeApi->searchFormSettingDelete($this);
            $listingTypeApi->authorizationPermissionsDelete($this);
            $this->removePhoto();
            //  $listingTypeApi->removeDefaultPhoto(strtolower($this->title_singular));
            Engine_Api::_()->getApi('language', 'sitereview')->removeTranslateForListType($this);
            //FETCH LISTING IDS
            $listingTable = Engine_Api::_()->getDbTable('listings', 'sitereview');
            $select = $listingTable->select()
                    ->from($listingTable->info('name'), 'listing_id')
                    ->where('listingtype_id = ?', $this->listingtype_id);
            $listingDatas = $listingTable->fetchAll($select);
            foreach ($listingDatas as $listingData) {
                Engine_Api::_()->getItem('sitereview_listing', $listingData->listing_id)->delete();
            }

            //FETCH CATEGORY IDS
            $categoriesTable = Engine_Api::_()->getDbTable('categories', 'sitereview');
            $select = $categoriesTable->select()
                    ->from($categoriesTable->info('name'), 'category_id')
                    ->where('listingtype_id = ?', $this->listingtype_id);
            $categoriesDatas = $categoriesTable->fetchAll($select);
            foreach ($categoriesDatas as $categoriesData) {
                Engine_Api::_()->getItem('sitereview_category', $categoriesData->category_id)->delete();
            }

            //START INTERGRATION EXTENSION WORK
            //START FOR PAGE INRAGRATION.
            $sitepageintegrationEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitepageintegration');
            if (!empty($sitepageintegrationEnabled)) {
                Engine_Api::_()->sitepageintegration()->deleteContents($this->listingtype_id);
            }
            //END FOR PAGE INRAGRATION.   
            //START FOR BUSINESS INRAGRATION.
            $sitebusinessintegrationEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitebusinessintegration');
            if (!empty($sitebusinessintegrationEnabled)) {
                Engine_Api::_()->sitebusinessintegration()->deleteContents($this->listingtype_id);
            }
            //END FOR BUSINESS INRAGRATION.
            //START FOR GROUP INRAGRATION.
            $sitegroupintegrationEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitegroupintegration');
            if (!empty($sitegroupintegrationEnabled)) {
                Engine_Api::_()->sitegroupintegration()->deleteContents($this->listingtype_id);
            }
            //END FOR GROUP INRAGRATION.
            //START FOR STORE INRAGRATION.
            $sitestoreintegrationEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitestoreintegration');
            if (!empty($sitestoreintegrationEnabled)) {
                Engine_Api::_()->sitestoreintegration()->deleteContents($this->listingtype_id);
            }
            //END FOR STORE INRAGRATION.
            //START FOR SITEADVSEARCH INTEGRATION.
            $siteadvsearchintegrationEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('siteadvsearch');
            if (!empty($siteadvsearchintegrationEnabled)) {
                Engine_Api::_()->getDbtable('contents', 'siteadvsearch')->delete(array('listingtype_id = ?' => $this->listingtype_id));
            }
            //END FOR SITEADVSEARCH INTEGRATION.
            //END INTERGRATION EXTENSION WORK
            //DELETE EDITORS FROM THAT LISTING TYPE
            Engine_Api::_()->getDbTable('editors', 'sitereview')->delete(array('listingtype_id = ?' => $this->listingtype_id));
            if (Engine_Api::_()->hasItemType('advancedactivity_content')) {
                Engine_Api::_()->advancedactivity()->contentTabSettings('sitereview_listtype_' . $this->listingtype_id, 'delete');
            }

            if (Engine_Api::_()->hasItemType('advancedactivity_customtype')) {
                Engine_Api::_()->advancedactivity()->customListSettings('sitereview_listing_listtype_' . $this->listingtype_id, 'delete');
            }

            Engine_Api::_()->getDbTable('subscriptions', 'sitereview')->delete(array('listingtype_id = ?' => $this->listingtype_id));
            
            //INTEGRATION WITH SITEMOBILE PLUGIN WHEN THIS NEW LISTING TYPE DELETE.    
            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobile')) {
                $moduleTable = Engine_Api::_()->getDbTable('modules', 'sitemobile');
                $select = $moduleTable->select()->from($moduleTable->info('name'))->where('name = ?', 'sitereview')->where('integrated = ?', 1);
                $is_sitemobile_object = $select->query()->fetchObject();
                if ($is_sitemobile_object) {
                    $listingTypeApi = Engine_Api::_()->getApi('listingTypeSM', 'sitereview');
                    $listingTypeApi->widgetizedPagesDelete($this, 'view', 'pages', 'content');
                    $listingTypeApi->widgetizedPagesDelete($this, 'view', 'tabletpages', 'tabletcontent');
                    $listingTypeApi->widgetizedPagesDelete($this, 'home', 'pages', 'content');
                    $listingTypeApi->widgetizedPagesDelete($this, 'home', 'tabletpages', 'tabletcontent');
                    $listingTypeApi->widgetizedPagesDelete($this, 'index', 'pages', 'content');
                    $listingTypeApi->widgetizedPagesDelete($this, 'index', 'tabletpages', 'tabletcontent');
                    $listingTypeApi->widgetizedPagesDelete($this, 'manage', 'pages', 'content');
                    $listingTypeApi->widgetizedPagesDelete($this, 'manage', 'tabletpages', 'tabletcontent');

                    $listingTypeApi->gutterNavigationDelete($this);
                    $listingTypeApi->mainNavigationDelete($this);
                    $listingTypeApi->dashboardNavigationDelete($this);

                    //DELETE PAGES FROM APP ALSO IF SITEMOBILEAPP MODULE IS ENABLED.
                    if (Engine_Api::_()->sitemobile()->isApp()) {
                        $listingTypeApi->widgetizedPagesDeleteApp($this, 'view', 'pages', 'content');
                        $listingTypeApi->widgetizedPagesDeleteApp($this, 'view', 'tabletpages', 'tabletcontent');
                        $listingTypeApi->widgetizedPagesDeleteApp($this, 'home', 'pages', 'content');
                        $listingTypeApi->widgetizedPagesDeleteApp($this, 'home', 'tabletpages', 'tabletcontent');
                        $listingTypeApi->widgetizedPagesDeleteApp($this, 'index', 'pages', 'content');
                        $listingTypeApi->widgetizedPagesDeleteApp($this, 'index', 'tabletpages', 'tabletcontent');
                        $listingTypeApi->widgetizedPagesDeleteApp($this, 'manage', 'pages', 'content');
                        $listingTypeApi->widgetizedPagesDeleteApp($this, 'manage', 'tabletpages', 'tabletcontent');
                    }
                }
            }

            //$db->commit();
        } catch (Exception $e) {
            //$db->rollBack();
            throw $e;
        }

        //DELETE LISTINGTYPES
        parent::_delete();
    }

}
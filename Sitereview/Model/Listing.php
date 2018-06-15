<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Listing.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_Listing extends Core_Model_Item_Abstract {

  protected $_parent_type = 'user';
  protected $_searchTriggers = array('title', 'body', 'search', 'closed', 'approved', 'draft', 'creation_date', 'end_date', 'location');
  protected $_parent_is_owner = true;
  protected $_gateway;
  protected $_package;
  protected $_statusChanged;
  
  public function isSearchable() {

    $condition1 =  ( (!isset($this->search) || $this->search) && !empty($this->_searchTriggers) && is_array($this->_searchTriggers) && empty($this->closed) && $this->approved && empty($this->draft) && $this->creation_date <= date("Y-m-d H:i:s") && ($this->end_date >= date("Y-m-d i:s:m", time()) || $this->end_date == '0000-00-00 00:00:00' || $this->end_date == ''));
    $condition2 = 1;
    if (Engine_Api::_()->sitereview()->hasPackageEnable())
      $condition2 = ($this->expiration_date > date("Y-m-d H:i:s"));
    return ($condition1 && $condition2);
  }

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array()) {

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($this->listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $this->listingtype_id);
    $photo_type = $listingtypeArray->photo_type;

    if ((isset($params['profile_link']) && !empty($params['profile_link'])) && ($photo_type == 'user')) {
      return $this->getParent()->getHref();
    }

    if (isset($params['profile_link']))
      unset($params['profile_link']);

    $slug = $this->getSlug();
    $params = array_merge(array(
        'route' => "sitereview_entry_view_listtype_$this->listingtype_id",
        'reset' => true,
        'listing_id' => $this->listing_id,
        'slug' => $slug,
            ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);

    return Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble($params, $route, $reset);
  }

  /**
   * Return a truncate listing description
   * */
  public function getDescription() {

    $tmpBody = strip_tags($this->body);
    return ( Engine_String::strlen($tmpBody) > 255 ? Engine_String::substr($tmpBody, 0, 255) . '...' : $tmpBody );
  }

  /**
   * Return a listing type
   * */
  public function getListingType() {

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($this->listingtype_id);
    return Zend_Registry::get('listingtypeArray' . $this->listingtype_id);
  }

  // General
  public function getShortType($inflect = false) {

    if ($this->_identity) {

      if ($inflect) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $this->getListingType()->title_singular)));
      }

      return strtolower($this->getListingType()->title_singular);
    } else {
      return parent::getShortType($inflect);
    }
  }

  // General
  public function getMediaType() {

    return strtolower($this->getListingType()->title_singular);
  }

  /**
   * Return slug
   * */
  public function getSlug($str = null, $maxstrlen = 64) {

    if (null === $str) {
      $str = $this->title;
    }
    
    $maxstrlen = 225;

    return Engine_Api::_()->seaocore()->getSlug($str, $maxstrlen);
  }

  /**
   * Return a category
   * */
  public function getCategory() {

    return Engine_Api::_()->getItem('sitereview_category', $this->category_id);
  }

  /**
   * Return Bottom Line
   * */
  public function getBottomLine() {

    $params = array();
    $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
    $params['resource_id'] = $this->listing_id;
    $params['resource_type'] = $this->getType();
    $params['type'] = 'editor';
    $isEditorReviewed = $reviewTable->canPostReview($params);
    if (!empty($isEditorReviewed)) {
      $bottomLine = $reviewTable->getColumnValue($isEditorReviewed, 'title');

      if (!empty($bottomLine)) {
        return $bottomLine;
      }
    }

    return $this->getDescription();
  }

  /**
   * Return Editor Reviewed Date
   * */
  public function getEditorReviewedDate() {

    $params = array();
    $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
    $params['resource_id'] = $this->listing_id;
    $params['resource_type'] = $this->getType();
    $params['type'] = 'editor';
    $isEditorReviewed = $reviewTable->canPostReview($params);
    if (!empty($isEditorReviewed)) {
      $editorReviewedDate = $reviewTable->getColumnValue($isEditorReviewed, 'creation_date');

      if (!empty($editorReviewedDate)) {
        return $editorReviewedDate;
      }
    }

    return date();
  }

  /**
   * Return keywords
   *
   * @param char separator 
   * @return keywords
   * */
  public function getKeywords($separator = ' ') {

    $keywords = array();
    foreach ($this->tags()->getTagMaps() as $tagmap) {
      $tag = $tagmap->getTag();
      $keywords[] = $tag->getTitle();
    }

    if (null == $separator) {
      return $keywords;
    }

    return join($separator, $keywords);
  }

  public function getRichContent() {

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($this->listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $this->listingtype_id);
    $photo_type = $listingtypeArray->photo_type;

    if ($photo_type != 'user') {
      return;
    }

    $view = Zend_Registry::get('Zend_View');
    $view = clone $view;
    $view->clearVars();
    $view->addScriptPath('application/modules/Sitereview/views/scripts/');

    $content = '';
    // Render the thingy
    $view->item = $this;
    $content .= $view->render('activity-feed/_listingNonPhoto.tpl');
    return $content;
  }

  public function getPriceInfo($params=array()) {

    return Engine_Api::_()->getDbTable('priceinfo', 'sitereview')->getPriceDetails($this->listing_id, $params);
  }

  public function getWheretoBuyMaxPrice() {

    $max_price = 0;
    $where_to_buy = Engine_Api::_()->getDbTable('priceinfo', 'sitereview')->getMaxPrice($this->listing_id);
    if (!empty($where_to_buy) && $where_to_buy > 0) {
      $max_price = $where_to_buy;
    }
    return $max_price;
  }

  public function getWheretoBuyMinPrice() {

    $min_price = 0;
    $where_to_buy = Engine_Api::_()->getDbTable('priceinfo', 'sitereview')->getMinPrice($this->listing_id);
    if (!empty($where_to_buy) && $where_to_buy > 0) {
      $min_price = $where_to_buy;
    }
    return $min_price;
  }

  public function getEditorReview() {

    $reveiwTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
    $select = $reveiwTable->getReviewsSelect(array('listing_id' => $this->listing_id, 'type' => 'editor', 'resource_type' => $this->getType(), 'resource_id' => $this->listing_id));
    return $reveiwTable->fetchRow($select);
  }

  public function getNoOfReviews($type=null) {

    if ($type == 'user-review') {
      if (!empty($this->rating_editor))
        return $this->review_count - 1;
    }else {
      return $this->review_count;
    }
  }

  public function getExpiryTime() {

    if (empty($this->approved_date))
      return;
    $duration = $this->getListingType()->admin_expiry_duration;
    $interval_type = $duration[1];
    $interval_value = $duration[0];
    $part = 1;
    $interval_value = empty($interval_value) ? 1 : $interval_value;
    $rel = strtotime($this->approved_date);
    // Calculate when the next payment should be due

    switch ($interval_type) {
      case 'day':
        $part = Zend_Date::DAY;
        break;
      case 'week':
        $part = Zend_Date::WEEK;
        break;
      case 'month':
        $part = Zend_Date::MONTH;
        break;
      case 'year':
        $part = Zend_Date::YEAR;
        break;
    }

    $relDate = new Zend_Date($rel);
    $relDate->add((int) $interval_value, $part);

    return $relDate->toValue();
  }

  /**
   * Set listing photo
   *
   * */
  public function setPhoto($photo) {

//     if(is_string($photo) && (strstr($photo, 'http')) || (strstr($photo, 'https'))) {
//       $file_exists = 1;
//     }
//     else {
//       $file_exists = 0;
//     }

    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
    }
//     elseif(is_string($photo) && !empty($file_exists)) {
//       if(fopen($photo, "r")) {
// 				$file = $photo;
//       }
//       else {
//        return;
//       }
//     }
    else {
      throw new Engine_Exception('invalid argument passed to setPhoto');
    }

    $name = basename($file);
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => 'sitereview_listing',
        'parent_id' => $this->getIdentity()
    );

    // Add autorotation for uploded images. It will work only for SocialEngine-4.8.9 Or more then.
    $usingLessVersion = Engine_Api::_()->seaocore()->usingLessVersion('core', '4.8.9');
    if(!empty($usingLessVersion)) {
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(720, 720)
              ->write($path . '/m_' . $name)
              ->destroy();

      //RESIZE IMAGE (PROFILE)
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(300, 500)
              ->write($path . '/p_' . $name)
              ->destroy();

      //RESIZE IMAGE (NORMAL)
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(140, 160)
              ->write($path . '/in_' . $name)
              ->destroy();

      //RESIZE IMAGE (Midum)
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(200, 200)
              ->write($path . '/im_' . $name)
              ->destroy();
    }else {
      $image = Engine_Image::factory();
      $image->open($file)
              ->autoRotate()
              ->resize(720, 720)
              ->write($path . '/m_' . $name)
              ->destroy();

      //RESIZE IMAGE (PROFILE)
      $image = Engine_Image::factory();
      $image->open($file)
              ->autoRotate()
              ->resize(300, 500)
              ->write($path . '/p_' . $name)
              ->destroy();

      //RESIZE IMAGE (NORMAL)
      $image = Engine_Image::factory();
      $image->open($file)
              ->autoRotate()
              ->resize(140, 160)
              ->write($path . '/in_' . $name)
              ->destroy();

      //RESIZE IMAGE (Midum)
      $image = Engine_Image::factory();
      $image->open($file)
              ->autoRotate()
              ->resize(200, 200)
              ->write($path . '/im_' . $name)
              ->destroy();
    }

    //RESIZE IMAGE (ICON)
    $image = Engine_Image::factory();
    $image->open($file);

    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 98, 98)
            ->write($path . '/is_' . $name)
            ->destroy();

    //STORE
    $storage = Engine_Api::_()->storage();
    $iMain = $storage->create($path . '/m_' . $name, $params);
    $iProfile = $storage->create($path . '/p_' . $name, $params);
    $iIconNormal = $storage->create($path . '/in_' . $name, $params);
    $iIconNormalMidum = $storage->create($path . '/im_' . $name, $params);
    $iSquare = $storage->create($path . '/is_' . $name, $params);

    $iMain->bridge($iProfile, 'thumb.profile');
    $iMain->bridge($iIconNormalMidum, 'thumb.midum');
    $iMain->bridge($iIconNormal, 'thumb.normal');
    $iMain->bridge($iSquare, 'thumb.icon');

    //REMOVE TEMP FILES
    @unlink($path . '/p_' . $name);
    @unlink($path . '/m_' . $name);
    @unlink($path . '/in_' . $name);
    @unlink($path . '/is_' . $name);

    //ADD TO ALBUM
    $viewer = Engine_Api::_()->user()->getViewer();

    $photoTable = Engine_Api::_()->getItemTable('sitereview_photo');
    $rows = $photoTable->fetchRow($photoTable->select()->from($photoTable->info('name'), 'order')->order('order DESC')->limit(1));
    $order = 0;
    if (!empty($rows)) {
      $order = $rows->order + 1;
    }
    $sitereviewAlbum = $this->getSingletonAlbum();
    $photoItem = $photoTable->createRow();
    $photoItem->setFromArray(array(
        'listing_id' => $this->getIdentity(),
        'album_id' => $sitereviewAlbum->getIdentity(),
        'user_id' => $viewer->getIdentity(),
        'file_id' => $iMain->getIdentity(),
        'collection_id' => $sitereviewAlbum->getIdentity(),
        'order' => $order
    ));
    $photoItem->save();

    //UPDATE ROW
    $this->modified_date = date('Y-m-d H:i:s');
    $this->photo_id = $photoItem->file_id;
    $this->save();

    return $this;
  }

  /**
   * Set listing location
   *
   */
  public function setLocation() {

    $id = $this->listing_id;
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($this->listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $this->listingtype_id);
    if ($listingtypeArray->location) {
      $sitereview = $this;
      if (!empty($sitereview))
        $location = $sitereview->location;

      if (!empty($location)) {
        $locationTable = Engine_Api::_()->getDbtable('locations', 'sitereview');
        $locationName = $locationTable->info('name');

        $locationRow = Engine_Api::_()->getDbtable('locations', 'sitereview')->getLocation(array('id' => $id, 'listingtype_id' => $this->listingtype_id));


        //$locationRow = $locationTable->getLocation(array('location' => $location));
        if (isset($_POST['locationParams']) && $_POST['locationParams']) {
          if (is_string($_POST['locationParams']))
            $_POST['locationParams'] = Zend_Json_Decoder::decode($_POST['locationParams']);
          if ($_POST['locationParams']['location'] === $location) {
            try {
              $loctionV = $_POST['locationParams'];
              $loctionV['listing_id'] = $id;
              $loctionV['zoom'] = 16;
              if (empty($locationRow))
                $locationRow = $locationTable->createRow();
              $locationRow->setFromArray($loctionV);
              $locationRow->save();
            } catch (Exception $e) {
              throw $e;
            }
          }
          return;
        }




        $selectLocQuery = $locationTable->select()->where('location = ?', $location);
        $locationValue = $locationTable->fetchRow($selectLocQuery);

        $enableSocialengineaddon = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seaocore');

        if (empty($locationValue)) {
          $getSEALocation = array();
          if (!empty($enableSocialengineaddon)) {
            $getSEALocation = Engine_Api::_()->getDbtable('locations', 'seaocore')->getLocation(array('location' => $location));
          }
          if (empty($getSEALocation)) {

            $locationLocal = $location;
            $urladdress = urlencode($locationLocal);
            $delay = 0;

            //ITERATE THROUGH THE ROWS, GEOCODING EACH ADDRESS
            $geocode_pending = true;
            while ($geocode_pending) {
              $request_url = "https://maps.googleapis.com/maps/api/geocode/json?address=$urladdress";
              $ch = curl_init();
              $timeout = 5;
              curl_setopt($ch, CURLOPT_URL, $request_url);
              curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
              ob_start();
              curl_exec($ch);
              curl_close($ch);

              $get_value = ob_get_contents();
              if (empty($get_value)) {
                $get_value = @file_get_contents($request_url);
              }
              $json_resopnse = !empty($get_value) ? Zend_Json::decode($get_value) : array();

              ob_end_clean();
              $status = $json_resopnse['status'];
              if (strcmp($status, "OK") == 0) {
                // Successful geocode
                $geocode_pending = false;
                $result = $json_resopnse['results'];

                // Format: Longitude, Latitude, Altitude
                $lat = $result[0]['geometry']['location']['lat'];
                $lng = $result[0]['geometry']['location']['lng'];
                $f_address = $result[0]['formatted_address'];
                $len_add = count($result[0]['address_components']);

                $address = '';
                $country = '';
                $state = '';
                $zip_code = '';
                $city = '';
                for ($i = 0; $i < $len_add; $i++) {
                  $types_location = $result[0]['address_components'][$i]['types'][0];

                  if ($types_location == 'country') {
                    $country = $result[0]['address_components'][$i]['long_name'];
                  } else if ($types_location == 'administrative_area_level_1') {
                    $state = $result[0]['address_components'][$i]['long_name'];
                  } else if ($types_location == 'administrative_area_level_2') {
                    $city = $result[0]['address_components'][$i]['long_name'];
                  } else if ( $types_location == 'postal_code' || $types_location == 'zip_code') {
                    $zip_code = $result[0]['address_components'][$i]['long_name'];
                  } else if ($types_location == 'street_address') {
                    if ($address == '')
                      $address = $result[0]['address_components'][$i]['long_name'];
                    else
                      $address = $address . ',' . $result[0]['address_components'][$i]['long_name'];
                  }else if ($types_location == 'locality') {
                    if ($address == '')
                      $address = $result[0]['address_components'][$i]['long_name'];
                    else
                      $address = $address . ',' . $result[0]['address_components'][$i]['long_name'];
                  }else if ($types_location == 'route') {
                    if ($address == '')
                      $address = $result[0]['address_components'][$i]['long_name'];
                    else
                      $address = $address . ',' . $result[0]['address_components'][$i]['long_name'];
                  }else if ($types_location == 'sublocality') {
                    if ($address == '')
                      $address = $result[0]['address_components'][$i]['long_name'];
                    else
                      $address = $address . ',' . $result[0]['address_components'][$i]['long_name'];
                  }
                }

                try {
                  $loctionV = array();
                  $loctionV['listing_id'] = $id;
                  $loctionV['latitude'] = $lat;
                  $loctionV['location'] = $locationLocal;
                  $loctionV['longitude'] = $lng;
                  $loctionV['formatted_address'] = $f_address;
                  $loctionV['country'] = $country;
                  $loctionV['state'] = $state;
                  $loctionV['zipcode'] = $zip_code;
                  $loctionV['city'] = $city;
                  $loctionV['address'] = $address;
                  $loctionV['zoom'] = 16;
                  if (empty($locationRow))
                    $locationRow = $locationTable->createRow();

                  $locationRow->setFromArray($loctionV);
                  $locationRow->save();
                 if (!empty($enableSocialengineaddon)) {
                   $location = Engine_Api::_()->getDbtable('locations', 'seaocore')->setLocation($loctionV);
                 }
                } catch (Exception $e) {
                  throw $e;
                }
              } else if (strcmp($status, "620") == 0) {
                //SENT GEOCODE TO FAST
                $delay += 100000;
              } else {
                // FAILURE TO GEOCODE
                $geocode_pending = false;
                echo "Address " . $locationLocal . " failed to geocoded. ";
                echo "Received status " . $status . "\n";
              }
              usleep($delay);
            }
          } else {

            try {
              //CREATE LISTING LOCATION
              $loctionV = array();
              if (empty($locationRow))
                $locationRow = $locationTable->createRow();
              $value = $getSEALocation->toarray();
							unset($value['location_id']);
              $value['listing_id'] = $id;
              $locationRow->setFromArray($value);
              $locationRow->save();
            } catch (Exception $e) {
              throw $e;
            }
          }
        } else {

          try {
            //CREATE LISTING LOCATION
            $loctionV = array();
            if (empty($locationRow))
              $locationRow = $locationTable->createRow();
            $value = $locationValue->toarray();
            unset($value['location_id']);
            $value['listing_id'] = $id;
            $locationRow->setFromArray($value);
            $locationRow->save();
          } catch (Exception $e) {
            throw $e;
          }
        }
      }
    }
  }

  public function getPhoto($photo_id) {

    $photoTable = Engine_Api::_()->getItemTable('sitereview_photo');
    $select = $photoTable->select()
            ->where('file_id = ?', $photo_id)
            ->limit(1);

    $photo = $photoTable->fetchRow($select);
    return $photo;
  }

  public function getSingletonAlbum() {

    $table = Engine_Api::_()->getItemTable('sitereview_album');
    $select = $table->select()
            ->where('listing_id = ?', $this->getIdentity())
            ->order('album_id ASC')
            ->limit(1);

    $album = $table->fetchRow($select);

    if (null == $album) {
      $album = $table->createRow();
      $album->setFromArray(array(
          'title' => $this->getTitle(),
          'listing_id' => $this->getIdentity()
      ));
      $album->save();
    }

    return $album;
  }

  public function ratingBaseCategory($type = null) {

    $result = Engine_Api::_()->getDbtable('ratings', 'sitereview')->ratingbyCategory($this->getIdentity(), $type, $this->getType());
    $ratings = array();
    foreach ($result as $res) {
      $ratings[$res['ratingparam_id']] = $res['avg_rating'];
    }
    return $ratings;
  }

  public function getNumbersOfUserRating($type = null, $ratingparam_id=0, $value=0) {

    return $result = Engine_Api::_()->getDbtable('ratings', 'sitereview')->getNumbersOfUserRating($this->getIdentity(), $type, $ratingparam_id, $value, 0, $this->getType());
  }

  /**
   * Gets a url to the current photo representing this item. Return null if none
   * set
   *
   * @param string The photo type (null -> main, thumb, icon, etc);
   * @return string The photo url
   */
  public function getPhotoUrl($type = null) {

    $listingtypeArray = $this->getListingType();
    $photo_type = $listingtypeArray->photo_type;

    if ($photo_type == 'user') {
      $type = ( $type ? str_replace('.', '_', $type) : 'main' );
      if ($type == 'thumb_normal' || $type == 'thumb_icon')
        $type = 'thumb_profile';
      if (empty($this->getParent()->photo_id)) {
        $photo_id = $listingtypeArray->photo_id;
        if (empty($photo_id))
          return $listingtypeArray->getPhotoUrl($type);
      }

      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->getParent()->photo_id, $type);
      if (!$file) {
        return $listingtypeArray->getPhotoUrl($type);
      }

      return $file->map();
    } elseif ($photo_type == 'listing') {
      $photo_id = $this->photo_id;

      if (empty($photo_id)) {
        return $listingtypeArray->getPhotoUrl($type);
      }

      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($photo_id, $type);
      if (!$file) {
        return $listingtypeArray->getPhotoUrl($type);
      }

      return $file->map();
    }
  }

  public function updateAllCoverPhotos() {
    $photo = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id);
    if ($photo instanceof Storage_Model_File) {
      $file = $photo->temporary();
      $fileName = $photo->name;
      $name = basename($file);
      $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
      $params = array(
          'parent_type' => 'sitereview_listing',
          'parent_id' => $this->getIdentity()
      );

      //STORE
      $storage = Engine_Api::_()->storage();
      $iMain = $photo;

      $thunmProfile = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, 'thumb.profile');

      if (empty($thunmProfile) || empty($thunmProfile->parent_file_id)) {
        //RESIZE IMAGE (PROFILE)
        $image = Engine_Image::factory();
        $image->open($file)
                ->resize(300, 500)
                ->write($path . '/p_' . $name)
                ->destroy();
        $iProfile = $storage->create($path . '/p_' . $name, $params);
        $iMain->bridge($iProfile, 'thumb.profile');
        @unlink($path . '/p_' . $name);
      }



      $thunmMidum = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, 'thumb.midum');
      if (empty($thunmMidum) || empty($thunmMidum->parent_file_id)) {
        //RESIZE IMAGE (Midum)
        $image = Engine_Image::factory();
        $image->open($file)
                ->resize(200, 200)
                ->write($path . '/im_' . $name)
                ->destroy();
        $iIconNormalMidum = $storage->create($path . '/im_' . $name, $params);
        $iMain->bridge($iIconNormalMidum, 'thumb.midum');
        //REMOVE TEMP FILES

        @unlink($path . '/m_' . $name);
      }
    }
  }

  public function allowWhereToBuy() {
    return $this->getListingType()->where_to_buy && $this->authorization()->isAllowed($this->getOwner(), "where_to_buy_listtype_" . $this->listingtype_id);
  }

  /**
   * Gets a proxy object for the comment handler
   *
   * @return Engine_ProxyObject
   * */
  public function comments() {

    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
  }

  /**
   * Gets a proxy object for the like handler
   *
   * @return Engine_ProxyObject
   * */
  public function likes() {

    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
  }

  /**
   * Gets a proxy object for the tags handler
   *
   * @return Engine_ProxyObject
   * */
  public function tags() {

    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
  }

  /**
   * Insert global searching value
   *
   * */
  protected function _insert() {

    if (null == $this->search) {
      $this->search = 1;
    }

    parent::_insert();
  }

  /**
   * Delete the listing and belongings
   * 
   */
  public function _delete() {

    $listing_id = $this->listing_id;
    $db = Engine_Db_Table::getDefaultAdapter();

    $db->beginTransaction();
    try {
      Engine_Api::_()->fields()->getTable('sitereview_listing', 'search')->delete(array('item_id = ?' => $listing_id));
      Engine_Api::_()->fields()->getTable('sitereview_listing', 'values')->delete(array('item_id = ?' => $listing_id));

      //FETCH LISTING IDS
      $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
      $select = $reviewTable->select()
              ->from($reviewTable->info('name'), 'review_id')
              ->where('resource_id = ?', $this->listing_id)
              ->where('resource_type = ?', 'sitereview_listing');
      $reviewDatas = $reviewTable->fetchAll($select);
      foreach ($reviewDatas as $reviewData) {
        Engine_Api::_()->getItem('sitereview_review', $reviewData->review_id)->delete();
      }

      //FETCH LISTING VIDEO IDS
      $reviewVideoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');
      $selectVideoTable = $reviewVideoTable->select()
              ->from($reviewVideoTable->info('name'), 'video_id')
              ->where('listing_id = ?', $this->listing_id);
      $reviewVideoDatas = $reviewVideoTable->fetchAll($selectVideoTable);
      foreach ($reviewVideoDatas as $reviewVideoData) {
        Engine_Api::_()->getItem('sitereview_video', $reviewVideoData->video_id)->delete();
      }

      Engine_Api::_()->getDbtable('clasfvideos', 'sitereview')->delete(array('listing_id = ?' => $listing_id));
      Engine_Api::_()->getDbtable('albums', 'sitereview')->delete(array('listing_id = ?' => $listing_id));
      Engine_Api::_()->getDbtable('photos', 'sitereview')->delete(array('listing_id = ?' => $listing_id));
      Engine_Api::_()->getDbtable('vieweds', 'sitereview')->delete(array('listing_id = ?' => $listing_id));
      Engine_Api::_()->getDbtable('posts', 'sitereview')->delete(array('listing_id = ?' => $listing_id));
      Engine_Api::_()->getDbtable('topics', 'sitereview')->delete(array('listing_id = ?' => $listing_id));
      Engine_Api::_()->getDbtable('topicWatches', 'sitereview')->delete(array('resource_id = ?' => $listing_id));
      Engine_Api::_()->getDbtable('locations', 'sitereview')->delete(array('listing_id = ?' => $listing_id));
      Engine_Api::_()->getDbtable('otherinfo', 'sitereview')->delete(array('listing_id = ?' => $listing_id));

      //START CLAIM FUNCTIONALITY WORK
      Engine_Api::_()->getDbtable('claims', 'sitereview')->delete(array('listing_id = ?' => $listing_id));
      //END CLAIM FUNCTIONALITY WORK

      Engine_Api::_()->getDbtable('jobs', 'sitereview')->delete(array('listing_id = ?' => $listing_id));

      //GET WISHLISTS HAVING THIS LISTING ID
      $wishlistTable = Engine_Api::_()->getDbtable('wishlists', 'sitereview');
      $wishlistTableName = $wishlistTable->info('name');
      $wishlistMapTable = Engine_Api::_()->getDbtable('wishlistmaps', 'sitereview');
      $wishlistMapTableName = $wishlistMapTable->info('name');

      $wishListDatas = $wishlistTable->select()
              ->from($wishlistTableName, 'wishlist_id')
              ->where('listing_id = ?', $listing_id)
              ->query()
              ->fetchAll(Zend_Db::FETCH_COLUMN);

      if (!empty($wishListDatas)) {
        $select = $wishlistMapTable->select()
                ->from($wishlistMapTableName)
                ->where($wishlistMapTableName . '.listing_id != ?', $listing_id)
                ->where("wishlist_id IN (?)", (array) $wishListDatas)
                ->order('date ASC')
                ->group($wishlistMapTableName . '.wishlist_id');

        $wishlistMapDatas = $wishlistMapTable->fetchAll($select);
        if (!empty($wishlistMapDatas)) {

          $wishlistMapDatas = $wishlistMapDatas->toArray();
          foreach ($wishlistMapDatas as $wishlistMapData) {
            $wishlistTable->update(array('listing_id' => $wishlistMapData['listing_id']), array('wishlist_id = ?' => $wishlistMapData['wishlist_id'], 'listing_id = ?' => $listing_id));
          }
        }
      }

      $wishlistTable->update(array('listing_id' => 0), array('listing_id = ?' => $listing_id));
      $wishlistMapTable->delete(array('listing_id = ?' => $listing_id));

      //START ADVANCED-EVENT CODE
      $siteeventEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('siteevent');
      if ($siteeventEnabled) {
        $table = Engine_Api::_()->getItemTable('siteevent_event');
        $select = $table->select()
                ->from($table->info('name'), 'event_id')
                ->where('parent_id = ?', $listing_id)
                ->where('parent_type = ?', 'sitereview_listing');
        $rows = $table->fetchAll($select)->toArray();
        if (!empty($rows)) {
          foreach ($rows as $key => $event_ids) {
            $resource = Engine_Api::_()->getItem('siteevent_event', $event_ids['event_id']);
            if ($resource)
              $resource->delete();
          }
        }
      }
      //END ADVANCED-EVENT CODE

      //START CROWDFUNDING CODE
      $sitecrowdfundingEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitecrowdfunding');
      if ($sitecrowdfundingEnabled) {
        $table = Engine_Api::_()->getItemTable('sitecrowdfunding_project');
        $select = $table->select()
                ->from($table->info('name'), 'project_id')
                ->where('parent_id = ?', $listing_id)
                ->where('parent_type = ?', 'sitereview_listing');
        $rows = $table->fetchAll($select)->toArray();
        if (!empty($rows)) {
          foreach ($rows as $key => $project_ids) {
            $resource = Engine_Api::_()->getItem('sitecrowdfunding_project', $project_ids['project_id']);
            if ($resource && $resource->isbacked()){
              $resource->parent_type = 'user';
              $resource->save();
            }
            else if($resource)
              $resource->delete();
          }
        }
      }
      //END CROWDFUNDING CODE
      
      $documentEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('document');
      if ($documentEnabled) {
        $table = Engine_Api::_()->getItemTable('document');
        $select = $table->select()
                ->from($table->info('name'), 'document_id')
                ->where('parent_id = ?', $listing_id)
                ->where('parent_type = ?', 'sitereview_listing');
        $rows = $table->fetchAll($select)->toArray();
        if (!empty($rows)) {
          foreach ($rows as $key => $document_ids) {
            $resource = Engine_Api::_()->getItem('document', $document_ids['document_id']);
            if ($resource)
              $resource->delete();
          }
        }
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    //DELETE LISTING
    parent::_delete();
  }

  public function getPackage() {
    if (empty($this->package_id)) {
      return null;
    }
    if (null === $this->_package) {
      $this->_package = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $this->package_id);
    }
    return $this->_package;
  }

  // Active
  public function setActive($flag = true, $deactivateOthers = null) {

    $this->approved = true;
    $this->pending = 0;
    $viewer = Engine_Api::_()->user()->getViewer();
    if (empty($this->approved_date)) {
      $this->approved_date = date('Y-m-d H:i:s');
      if ($this->draft == 0 && $this->search && time() >= strtotime($this->creation_date)) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $this, 'sitereview_new_listtype_' . $this->listingtype_id);

        if ($action != null) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $this);
        }
      }
    }

    $this->save();
    return $this;
  }

  public function clearStatusChanged() {
    $this->_statusChanged = null;
    return $this;
  }

  public function didStatusChange() {
    return (bool) $this->_statusChanged;
  }

  public function cancel($is_upgrade_package = false) {

    $gateway_profile_id = Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getColumnValue($this->listing_id, 'gateway_profile_id');
    $gateway_id = Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getColumnValue($this->listing_id, 'gateway_id');
    
    // Try to cancel recurring payments in the gateway
    if (!empty($gateway_id) && !empty($gateway_profile_id)) {
      try {
        $gateway = Engine_Api::_()->getItem('sitereviewpaidlisting_gateway', $gateway_id);
        $gatewayPlugin = $gateway->getPlugin();
        if (method_exists($gatewayPlugin, 'cancelListing')) {
          $gatewayPlugin->cancelListing($gateway_profile_id);
        }
      } catch (Exception $e) {
        // Silence?
      }
    }
    // Cancel this row
    if($is_upgrade_package){
      $this->approved = false; // Need to do this to prevent clearing the user's session
    }
    $this->onCancel();
    return $this;
  }

  public function onCancel() {
    $this->_statusChanged = false;
    if (in_array($this->status, array('initial', 'trial', 'pending', 'active', 'overdue', 'cancelled'))) {
      // Change status
      if ($this->status != 'cancelled') {
        $this->status = 'cancelled';
        $this->_statusChanged = true;
      }
    }
    $this->save();
    return $this;
  }

  public function onPaymentSuccess() {
    $this->_statusChanged = false;
    if (in_array($this->status, array('initial', 'trial', 'pending', 'active', 'overdue', 'expired'))) {

      if (in_array($this->status, array('initial', 'pending', 'overdue'))) {
        $this->setActive(true);
      }

      // Update expiration to expiration + recurrence or to now + recurrence?
      $package = $this->getPackage();
      $expiration = $package->getExpirationDate();
      $diff_days = 0;
      if ($package->isOneTime() && !empty($this->expiration_date) && $this->expiration_date !== '0000-00-00 00:00:00') {
        $diff_days = round((strtotime($this->expiration_date) - strtotime(date('Y-m-d H:i:s'))) / 86400);
      }
      if ($expiration) {
        $date = date('Y-m-d H:i:s', $expiration);

        if ($diff_days >= 1) {

          $diff_days_expiry = round((strtotime($date) - strtotime(date('Y-m-d H:i:s'))) / 86400);
          $incrmnt_date = date('d', time()) + $diff_days_expiry + $diff_days;
          $incrmnt_date = date('Y-m-d H:i:s', mktime(date("H"), date("i"), date("s"), date("m"), $incrmnt_date));
        } else {
          $incrmnt_date = $date;
        }
        $this->expiration_date = $incrmnt_date;
      } else {
        $this->expiration_date = '2250-01-01 00:00:00';
      }

      // Change status
      if ($this->status != 'active') {
        $this->status = 'active';
        $this->_statusChanged = true;
      }
    }
    $this->save();
    return $this;
  }

  public function onPaymentFailure() {
    $this->_statusChanged = false;
    if (in_array($this->status, array('initial', 'trial', 'pending', 'active', 'overdue', 'expired'))) {
      // Change status
      if ($this->status != 'overdue') {
        $this->status = 'overdue';
        $this->_statusChanged = true;
      }
    }
    $this->save();
    return $this;
  }

  public function onPaymentPending() {
    $this->_statusChanged = false;
    if (in_array($this->status, array('initial', 'trial', 'pending', 'active', 'overdue', 'expired'))) {
      // Change status
      if ($this->status != 'pending') {
        $this->status = 'pending';
        $this->_statusChanged = true;
      }
    }
    $this->save();
    return $this;
  }

  public function onExpiration() {
    $this->_statusChanged = false;
    if (in_array($this->status, array('initial', 'trial', 'pending', 'active', 'expired'))) {
      // Change status
      if ($this->status != 'expired') {
        $this->status = 'expired';
        $this->_statusChanged = true;
      }
    }
    $this->save();
    return $this;
  }

  public function onRefund() {
    $this->_statusChanged = false;
    if (in_array($this->status, array('initial', 'trial', 'pending', 'active', 'refunded'))) {
      // Change status
      if ($this->status != 'refunded') {
        $this->status = 'refunded';
        $this->_statusChanged = true;
      }
    }
    $this->save();
    return $this;
  }

  /**
   * Process ipn of page transaction
   *
   * @param Payment_Model_Order $order
   * @param Engine_Payment_Ipn $ipn
   */
  public function onPaymentIpn(Payment_Model_Order $order, Engine_Payment_Ipn $ipn) {
    $gateway = Engine_Api::_()->getItem('sitereviewpaidlisting_gateway', $order->gateway_id);
    $gateway->getPlugin()->onPageTransactionIpn($order, $ipn);
    return true;
  }

  /**
   * Get status of listing
   * $params object $listing
   * @return string
   * */
  public function getListingStatus() {

    $translate = Zend_Registry::get('Zend_Translate');
    if (!empty($this->declined)) {
      return "<span style='color: red;'>" . $translate->translate("Declined") . "</span>";
    }

    if (!empty($this->pending)) {
      return $translate->translate("Approval Pending");
    }
    if (!empty($this->approved)) {
      return $translate->translate("Approved");
    }


    if (empty($this->approved)) {
      return $translate->translate("Dis-Approved");
    }

    return "Approved";
  }

  /**
   * Get expiry date for listing
   * $params object $listing
   * @return date
   * */
  public function getExpiryDate() {
    if (empty($this->expiration_date) || $this->expiration_date === "0000-00-00 00:00:00")
      return "-";
    $translate = Zend_Registry::get('Zend_Translate');
    if ($this->expiration_date === "2250-01-01 00:00:00")
      return $translate->translate('Never Expires');
    else {
      if (strtotime($this->expiration_date) < time())
        return "Expired";

      return date("M d,Y g:i A", strtotime($this->expiration_date));
    }
  }

}
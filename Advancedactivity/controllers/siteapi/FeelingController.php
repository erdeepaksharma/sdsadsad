<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: FeelingController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_FeelingController extends Siteapi_Controller_Action_Standard {

    public function getpreChoicesAction() {

        $getHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
        $table = Engine_Api::_()->getDbtable('feelingtypes', 'advancedactivity');
        $select = $table->select($table->info('name'))
                ->where("enabled =?", 1)
                ->order('order ASC');
        try {
            $searchApi = Engine_Api::_()->getDbtable('search', 'core');
            $feelingtypes = $table->fetchAll($select);
            $feelingtype_json = array();
            $feeling_json = array();
            foreach ($feelingtypes as $feelingtype) {
                $feelingContentTypes = array();
                $listingTypes = array();
                if (!empty($feelingtype->type)) {
                    $contentTypes = Zend_Json::decode($feelingtype->type);
                    foreach ($contentTypes as $itemType) {
                        if (Engine_Api::_()->hasItemType($itemType)) {
                            $feelingContentTypes[] = $itemType;
                        } elseif (stripos($itemType, '_listingtype_') !== false) {
                            $listingTypes[] = str_replace('sitereview_listingtype_', '', $itemType);
                        }
                    }
                    if (count($feelingContentTypes) <= 0 && count($listingTypes) <= 0) {
                        continue;
                    }
                }
                if (!empty($feelingContentTypes) || !empty($listingTypes)) {
                    if (!empty($feelingContentTypes)) {
                        $searchSelect = $searchApi->select()
                                ->where('type IN (?)', $feelingContentTypes);
                        $searchFetchedObject = $searchApi->fetchAll($searchSelect);
                        $feeling_json = $this->setJsonArray($feelingtype, $searchFetchedObject, $feeling_json);
                    }
                    if (!empty($listingTypes) && Engine_Api::_()->hasModuleBootstrap('sitereview')) {
                        $listingTable = Engine_Api::_()->getDbTable('listings', 'sitereview');
                        $sitereviewTableName = $listingTable->info('name');
                        $searchTableName = $searchApi->info('name');
                        $select = $searchApi->select()
                                ->from($searchApi->info('name'))
                                ->join($sitereviewTableName, "$searchTableName.id = $sitereviewTableName.listing_id", array())
                                ->where("$sitereviewTableName.listingtype_id IN (?)", $listingTypes)
                                ->where("$searchTableName.type = ? ", 'sitereview_listing');
                        $searchFetchedObject = $searchApi->fetchAll($select);
                        $feeling_json = $this->setJsonArray($feelingtype, $searchFetchedObject, $feeling_json);
                    }
                } else {
                    foreach ($feelingtype->getFeelings() as $feelings) {
                        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($feelings->file_id, 'thumb.icon');
                        $photo = $file->map();
                        $photo = strstr($photo, 'http') ? $photo : $getHost . $photo;
                        $url = $feelings->getPhotoUrl();
                        $url = strstr($url, 'http') ? $url : $getHost . $url;

                        $feeling_json[$feelingtype->getIdentity()][] = array(
                            'child_id' => $feelings->getIdentity(),
                            'title' => $this->translate($feelings->getTitle()),
                            'photo' => $photo,
                            'url' => $url,
                            'type' => ''
                        );
                    }
                }
                if (!empty($feeling_json[$feelingtype->getIdentity()])) {

                    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($feelingtype->photo_id, 'thumb.icon');
                    $photo = $file->map();

                    $photo = strstr($photo, 'http') ? $photo : $getHost . $photo;
                    $url = $feelingtype->getPhotoUrl();
                    $url = strstr($url, 'http') ? $url : $getHost . $url;

                    $feelingtype_json[] = array(
                        'parent_id' => $feelingtype->getIdentity(),
                        'title' => $this->translate($feelingtype->getTitle()),
                        'photo' => $photo,
                        'url' => $url,
                        'tagline' => $this->translate($feelingtype->tagline)
                    );
                }
            }
            $response['parent'] = $feelingtype_json;
            $response['child'] = $feeling_json;

            $this->respondWithSuccess($response);
        } catch (Exception $e) {
            
        }
    }

    public function setJsonArray($feelingtype, $searchFetchedObject, $feeling_json) {
        $getHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
        foreach ($searchFetchedObject as $searchItem) {
            $item = Engine_Api::_()->getItem($searchItem->type, $searchItem->id);
            if (empty($item)) {
                continue;
            }
            if (isset($item->file_id))
                $file = Engine_Api::_()->getItemTable('storage_file')->getFile($item->file_id, 'thumb.icon');
            else
                $file = Engine_Api::_()->getItemTable('storage_file')->getFile($item->photo_id, 'thumb.icon');
            $photo = $file->map();
            $photo = strstr($photo, 'http') ? $photo : $getHost . $photo;
            $url = $item->getPhotoUrl();
            $url = strstr($url, 'http') ? $url : $getHost . $url;
            $feeling_json[$feelingtype->getIdentity()][] = array(
                'id' => $item->getIdentity(),
                'title' => $item->getTitle(),
                'photo' => $photo,
                'url' => $url,
                'type' => $searchItem->type
            );
        }
        return $feeling_json;
    }

    public function bannerAction() {
        $table = Engine_Api::_()->getDbTable('banners', 'advancedactivity');
        try {
            $getHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
            $api = Engine_Api::_()->getApi('settings', 'core');
            $limit = $api->getSetting('advancedactivity.banner.count', 10);
            $order = $api->getSetting('advancedactivity.feed.banner.order', 'random');
            $select = $table->select()
                    ->where('enabled =? ', 1)
                    ->where('startdate <= now()')
                    ->limit($limit);
            if ($order !== 'random') {
                $select->order('order ASC');
            } else {
                $select->order('Rand()');
            }
            $banners = $table->fetchAll($select);
            $data = array();
            $dataHighlighted = array();
            foreach ($banners as $banner) {
                if ($banner->enddate != '0000-00-00' && $banner->enddate < date('Y-m-d')) {
                    continue;
                }
                $bannersFormat = array();
                $bannersFormat['image'] = 'none';
                if (!empty($banner->file_id)) {
                    $file = Engine_Api::_()->getDbtable('files', 'storage')->getFile($banner->file_id);
                    $bannersFormat['image'] = $file ? 'url(' . $file->getHref() . ')' : 'none';
                    $bannersFormat['feed_banner_url'] = $file ? $file->getHref() : 'none';
                    $bannersFormat['feed_banner_url'] = strstr($bannersFormat['feed_banner_url'], 'http') ? $bannersFormat['feed_banner_url'] : $getHost . $bannersFormat['feed_banner_url'];
                } else if ($banner->gradient) {
                    continue;
                    //$bannersFormat['image'] = $banner->gradient;
                }
                $bannersFormat['background-color'] = $banner->background_color;
                $bannersFormat['color'] = $banner->color;
                $bannersFormat['highlighted'] = $banner->highlighted;
                if ($banner->highlighted) {
                    $dataHighlighted[] = $bannersFormat;
                    continue;
                }
                $data[] = $bannersFormat;
            }

            $response = array_merge($dataHighlighted, $data);
            $this->respondWithSuccess($response);
        } catch (Exception $ex) {
            
        }
    }

    public function getStatusFormAction() {
        $response['form'] = Engine_Api::_()->getApi('Siteapi_Core', 'advancedactivity')->getForm();
        $this->respondWithSuccess($response);
    }

    public function greetingManageAction() {
        $front = Zend_Controller_Front::getInstance();
        $key = $front->getRequest()->getModuleName() . "_" . $front->getRequest()->getActionName();

        $viewer = Engine_Api::_()->user()->getViewer();
        $greetingType = $this->_getParam('greetingType', 'all');

        if (empty($viewer->getIdentity())) {
            $this->respondWithError('unauthorized');
        }
        if (!Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $viewer, 'aaf_greeting_enable')) {
            $this->respondWithError('unauthorized', "Greeting is not enable for this member");
        }
        $todaysBirthday = $this->todaysBirthday($viewer);
        if (!empty($todaysBirthday) && in_array($greetingType, array('all', 'userbased')) && in_array($viewer->getIdentity(), $todaysBirthday)) {

            $response['userItSelfBirthday'] = 1;
            foreach ($todaysBirthday as $id) {
                $user = Engine_Api::_()->user()->getUser($id);
                $tempUser = Engine_Api::_()->getApi('Core', 'siteapi')->validateUserArray($user);

                // Add images
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($user);
                $tempUser = array_merge($tempUser, $getContentImages);
                
                  
                  if($user->getIdentity()!=$viewer->getIdentity()){
                 $viewer_title = array_shift(explode(" ",$viewer->getTitle()));
                  $user_title =array_shift(explode(" ",$user->getTitle()));
                 $tempUser['birthday_title'] =$this->translate('Hey! _VIEWER_NAME_, It\'s _USER_NAME_ birthday today. Help him/her to celebrate their birthday');
                    $tempUser['birthday_title'] = str_replace('_VIEWER_NAME_', $viewer_title, $tempUser['birthday_title']);   
                    $tempUser['birthday_title'] = str_replace('_USER_NAME_', $user_title, $tempUser['birthday_title']);
                  }
                  else{
                      $siteTitle = Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title', $this->translate('_SITE_TITLE'));
                      $title = $this->translate('Wishes you happy birthday !');
                 $tempUser['birthday_title'] =$siteTitle." ".$title;
                 
                 
                  }
                $response['usersBirthday'][] = $tempUser;

              
            }
        } elseif (!empty($todaysBirthday) && in_array($greetingType, array('all', 'userbased'))) {
            $todaysBirthday;
            $response['todaysBirthday'] = 1;
            foreach ($todaysBirthday as $id) {
                $user = Engine_Api::_()->user()->getUser($id);
                $tempUser = Engine_Api::_()->getApi('Core', 'siteapi')->validateUserArray($user);

                // Add images
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($user);
                $tempUser = array_merge($tempUser, $getContentImages);
                                 if($user->getIdentity()!=$viewer->getIdentity()){
                 $viewer_title = array_shift(explode(" ",$viewer->getTitle()));
                  $user_title =array_shift(explode(" ",$user->getTitle()));
                 $tempUser['birthday_title'] =$this->translate('Hey! _VIEWER_NAME_, It\'s _USER_NAME_ birthday today. Help him/her to celebrate their birthday');
                    $tempUser['birthday_title'] = str_replace('_VIEWER_NAME_', $viewer_title, $tempUser['birthday_title']);   
                    $tempUser['birthday_title'] = str_replace('_USER_NAME_', $user_title, $tempUser['birthday_title']);
                  }
                  else{
                      $siteTitle = Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title', $this->translate('_SITE_TITLE'));
                      $title = $this->translate('Wishes you happy birthday !');
                 $tempUser['birthday_title'] =$siteTitle." ".$title;
                 
                 
                  }
                 
                $response['usersBirthday'][] = $tempUser;
            }
        }

        /* On This Day Synchronization Work */
//    $onThisday = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getOnThisDayActivity($viewer);
//    $dayCookie = $request->getCookie('shared_this');
//    if(!empty($onThisday) && empty($dayCookie)) {
//      return $this->setNoRender();
//    }
        /* On This Day Synchronization Work */
        try {

            $timezone = Engine_Api::_()->getApi('settings', 'core')->core_locale_timezone;
            if ($viewer->getIdentity()) {
                $timezone = $viewer->timezone;
            }

            $oldTz = date_default_timezone_get();

            date_default_timezone_set($timezone);
            $date = date('Y-m-d H:i:s a');
            date_default_timezone_set($oldTz);
            $item = Engine_Api::_()->getDbTable('greetings', 'advancedactivity');
            $select = $item->select()
                    ->where("((TIME(starttime) <= TIME('" . $date . "') and TIME(endtime) >= TIME('" . $date . "')) and `repeat` = 1 ) or ((`starttime` <= ? and `endtime` >= ?) and `repeat` = 0)", $date)
                    ->where('enabled = ? ', 1)
            ;
            if (!empty($greeting_ids)) {
                $select->where("greeting_id NOT IN (?)", $greeting_ids);
            }
            if (!empty($randomGreeting_ids)) {
                $select->where("greeting_id NOT IN (?)", $randomGreeting_ids);
            }
            $select->order('greeting_id DESC')
            ;
            $greeting = $item->fetchAll($select)->toArray();


//      $body = $greeting[0]['body'];
//      $greeting_id = $greeting[0]['greeting_id'];


            $response['greetings'] = $greeting;
            $this->respondWithSuccess($response);
        } catch (Exception $e) {
            
        }
    }

    public function todaysBirthday($viewer) {

        $metaTable = Engine_Api::_()->fields()->getTable('user', 'meta');
        $birthday_column = $metaTable->select()
                        ->where("type=?", 'birthdate')
                        ->query()->fetchColumn();
        $membershipIds = Engine_Api::_()->getDbTable('membership', 'user')->getMembershipsOfIds($viewer);
        $membershipIdsIncViewer = !empty($membershipIds) ? array_merge(array($viewer->getIdentity()), $membershipIds) : array($viewer->getIdentity());
        $birthdayUserIds = array();
        if (!empty($membershipIdsIncViewer) && !empty($birthday_column)) {
            $valueTable = Engine_Api::_()->fields()->getTable('user', 'values');
            $birthdays = $valueTable->select()->from($valueTable->info('name'), array('item_id'))
                    ->where('field_id = ? ', $birthday_column)
                    ->where('DAY(value) = DAY(now())  and MONTH(value) = MONTH(now()) and item_id IN (?)', $membershipIdsIncViewer)
                    ->query()
                    ->fetchAll()
            ;
            $birthdayUserIds = array_column($birthdays, "item_id");
        }
        return $birthdayUserIds;
    }

}

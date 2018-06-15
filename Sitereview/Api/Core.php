<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Core.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Api_Core extends Core_Api_Abstract {

    const IMAGE_WIDTH = 1600;
    const IMAGE_HEIGHT = 1600;
    const THUMB_WIDTH = 140;
    const THUMB_HEIGHT = 160;
    const THUMB_LARGE_WIDTH = 250;
    const THUMB_LARGE_HEIGHT = 250;

    public function createPhoto($params, $file) {

        if ($file instanceof Storage_Model_File) {
            $params['file_id'] = $file->getIdentity();
        } else {

            //GET IMAGE INFO AND RESIZE
            $name = basename($file['tmp_name']);
            $path = dirname($file['tmp_name']);
            $extension = ltrim(strrchr($file['name'], '.'), '.');

            $mainName = $path . '/m_' . $name . '.' . $extension;
            $thumbName = $path . '/t_' . $name . '.' . $extension;
            $thumbLargeName = $path . '/t_l_' . $name . '.' . $extension;

            $image = Engine_Image::factory();
            $image->open($file['tmp_name'])
                    ->resize(self::IMAGE_WIDTH, self::IMAGE_HEIGHT)
                    ->write($mainName)
                    ->destroy();

            $image = Engine_Image::factory();
            $image->open($file['tmp_name'])
                    ->resize(self::THUMB_WIDTH, self::THUMB_HEIGHT)
                    ->write($thumbName)
                    ->destroy();
            $image = Engine_Image::factory();
            $image->open($file['tmp_name'])
                    ->resize(self::THUMB_LARGE_WIDTH, self::THUMB_LARGE_HEIGHT)
                    ->write($thumbLargeName)
                    ->destroy();

            //RESIZE IMAGE (ICON)
            $iSquarePath = $path . '/is_' . $name . '.' . $extension;
            $image = Engine_Image::factory();
            $image->open($file['tmp_name']);

            $size = min($image->height, $image->width);
            $x = ($image->width - $size) / 2;
            $y = ($image->height - $size) / 2;

            $image->resample($x, $y, $size, $size, 48, 48)
                    ->write($iSquarePath)
                    ->destroy();

            //STORE PHOTO
            $photo_params = array(
                'parent_id' => $params['listing_id'],
                'parent_type' => 'sitereview_listing',
            );

            $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);
            $thumbFile = Engine_Api::_()->storage()->create($thumbName, $photo_params);
            $photoFile->bridge($thumbFile, 'thumb.normal');

            $thumbLargeFile = Engine_Api::_()->storage()->create($thumbLargeName, $photo_params);
            $photoFile->bridge($thumbLargeFile, 'thumb.large');

            $iSquare = Engine_Api::_()->storage()->create($iSquarePath, $photo_params);
            $photoFile->bridge($iSquare, 'thumb.icon');
            $params['file_id'] = $photoFile->file_id;
            $params['photo_id'] = $photoFile->file_id;

            //REMOVE TEMP FILES
            @unlink($mainName);
            @unlink($thumbName);
            @unlink($thumbLargeName);
            @unlink($iSquarePath);
        }

        $row = Engine_Api::_()->getDbtable('photos', 'sitereview')->createRow();
        $row->setFromArray($params);
        $row->save();

        return $row;
    }

    //FUNCTION FOR SHOWING 'LIKED LINK'
    public function check_availability($resourceType, $resourceId) {

        $viewer = Engine_Api::_()->user()->getViewer();
        $sub_status_table = Engine_Api::_()->getItemTable('core_like');
        $columns = array('like_id');

        $sub_status_name = $sub_status_table->info('name');
        $sub_status_select = $sub_status_table->select()
                ->from($sub_status_name, $columns)
                ->where('resource_type = ?', $resourceType)
                ->where('resource_id = ?', $resourceId)
                ->where('poster_type = ?', $viewer->getType())
                ->where('poster_id = ?', $viewer->getIdentity())
                ->query()
                ->fetchColumn();

        return $sub_status_select;
    }

    //CHECK VIDEO PLUGIN ENABLE / DISABLE
    public function enableVideoPlugin() {

        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video', 1)) {
            return Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video');
        } else {
            return 1;
        }
    }

    public function allowProject($subject_sitereview, $viewer, $counter = 0, $uploadProject = 0) {
        $listingtype_id = $subject_sitereview->listingtype_id;
        $allowed_upload_project = Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "sprcreate_listtype_$listingtype_id");
        if (empty($allowed_upload_project))
            return false;

        return true;
    }

    /**
     * Page base network enable
     *
     * @return bool
     */
    public function pageBaseNetworkEnable() {

        return (bool) ( (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.network', 0) || Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.default.show', 0)));
    }

    //APROVED/ DISAPROVED EMAIL NOTIFICATION FOR CLASSIFEID
    public function aprovedEmailNotification(Core_Model_Item_Abstract $object, $params = array()) {

        $email = Engine_Api::_()->getApi('settings', 'core')->core_mail_from;
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $object->listing_id);
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($params['mail_id'], 'SITEREVIEW_APPROVED_EMAIL_NOTIFICATION', array(
            'host' => $_SERVER['HTTP_HOST'],
            'subject' => $params['subject'],
            'title' => $params['title'],
            'message' => $params['message'],
            'object_link' => $sitereview->getHref(array('profile_link' => 1)),
            'email' => $email,
            'queue' => false
        ));
    }

    /**
     * Check location is enable
     *
     * @param array $params
     * @return int $check
     */
    public function enableLocation($listingtype_id) {

        if (empty($listingtype_id) || $listingtype_id == -1) {
            return 0;
        }

        $this->setListingTypeInRegistry($listingtype_id);

        return Zend_Registry::get('listingtypeArray' . $listingtype_id)->location;
    }

    public function friend_number_of_like($resourceType, $resourceId) {

        $user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $sub_status_table = Engine_Api::_()->getItemTable('core_like');
        $sub_status_name = $sub_status_table->info('name');
        $membership_table = Engine_Api::_()->getDbtable('membership', 'user');
        $member_name = $membership_table->info('name');
        $fetch_count = $sub_status_table->select()
                ->from($sub_status_name, array('COUNT(' . $sub_status_name . '.like_id) AS like_count'))
                ->joinInner($member_name, "$member_name . user_id = $sub_status_name . poster_id", NULL)
                ->where($member_name . '.resource_id = ?', $user_id)
                ->where($member_name . '.active = ?', 1)
                ->where($sub_status_name . '.resource_type = ?', $resourceType)
                ->where($sub_status_name . '.resource_id = ?', $resourceId)
                ->where($sub_status_name . '.poster_id != ?', $user_id)
                ->where($sub_status_name . '.poster_id != ?', 0)
                ->group($sub_status_name . '.resource_id')
                ->query()
                ->fetchColumn();

        if (!empty($fetch_count)) {
            return $fetch_count;
        } else {
            return 0;
        }
    }

    public function number_of_like($resourceType, $resourceId) {

        //GET THE VIEWER (POSTER) AND RESOURCE.
        $poster = Engine_Api::_()->user()->getViewer();
        $resource = Engine_Api::_()->getItem($resourceType, $resourceId);
        return Engine_Api::_()->getDbtable('likes', 'core')->getLikeCount($resource, $poster);
    }

    public function removeMapLink($string) {

        if (!empty($string)) {
            $reqStartMapPaterrn = (!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://" : "http://" . 'maps.google.com/?q=';
            $reqEndPatern = '>map</a>]';
            $positionMapStart = strpos($string, $reqStartMapPaterrn);
            if ($positionMapStart !== false) {
                $reqStartPatern = "<a";
                $positionStart = strpos($string, $reqStartPatern, ($positionMapStart - 10));
                $positionEnd = strpos($string, $reqEndPatern, $positionStart);
                if ($positionStart < $positionMapStart && $positionMapStart < $positionEnd)
                    $string = substr_replace($string, "", $positionStart - 1, ($positionEnd + 10) - $positionStart);
            }
        }

        return $string;
    }

    public function allowVideo($subject_sitereview, $viewer, $counter = 0, $uploadVideo = 0) {

        $allowed_upload_videoEnable = $this->enableVideoPlugin();
        if (empty($allowed_upload_videoEnable))
            return false;

        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video', 1)) {

            //GET USER LEVEL ID
            $viewer_id = $viewer->getIdentity();
            if (!empty($viewer_id)) {
                $level_id = Engine_Api::_()->user()->getViewer()->level_id;
            } else {
                $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
            }

            //CHECK FOR SOCIAL ENGINE CORE VIDEO PLUGIN
            $allowed_upload_video_video = Engine_Api::_()->authorization()->getPermission($level_id, 'video', 'create');
            if (empty($allowed_upload_video_video))
                return false;
        }

        $listingtype_id = $subject_sitereview->listingtype_id;
        $allowed_upload_video = Engine_Api::_()->authorization()->isAllowed($subject_sitereview, $viewer, "video_listtype_$listingtype_id");
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($subject_sitereview->package_id, "video")) {
                $videoCount = Engine_Api::_()->getDbTable('packages', 'sitereviewpaidlisting')->getPackageOption($subject_sitereview->package_id, 'video_count');
                if (empty($videoCount))
                    $allowed_upload_video = $allowed_upload_video;
                elseif ($videoCount > $counter)
                    $allowed_upload_video = $allowed_upload_video;
                else
                    $allowed_upload_video = 0;
            } else
                $allowed_upload_video = 0;
        }

        if (empty($allowed_upload_video))
            return false;

        return true;
    }

    public function listing_Like($resourceType, $resourceId) {

        $LIMIT = 3;
        $sub_status_table = Engine_Api::_()->getItemTable('core_like');
        $sub_status_name = $sub_status_table->info('name');
        $sub_status_select = $sub_status_table->select()
                ->from($sub_status_name, array('poster_id'))
                ->where('resource_type = ?', $resourceType)
                ->where('resource_id = ?', $resourceId)
                ->order('like_id DESC')
                ->limit($LIMIT);
        $fetch_sub = $sub_status_select->query()->fetchAll();

        return $fetch_sub;
    }

    /**
     * Check widget is exist or not
     *
     */
    public function existWidget($widget = '', $identity = 0, $listingtype_id = null) {
        if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
            $moduleName = 'core';
            $prefix = '';
        } else {
            $moduleName = Engine_Api::_()->sitemobile()->isApp() ? 'sitemobileapp' : 'sitemobile';
            $prefix = '';
            if (Engine_Api::_()->sitemobile()->checkMode('tablet-mode')) {
                $prefix = 'tablet';
            }
        }
        //GET CONTENT TABLE
        $tableContent = Engine_Api::_()->getDbtable($prefix . 'content', $moduleName);
        $tableContentName = $tableContent->info('name');

        //GET PAGE TABLE
        $tablePage = Engine_Api::_()->getDbtable($prefix . 'pages', $moduleName);
        $tablePageName = $tablePage->info('name');

        if ($widget == 'sitereview_reviews') {
            //GET PAGE ID
            $page_id = $tablePage->select()
                    ->from($tablePageName, array('page_id'))
                    ->where('name = ?', "sitereview_index_view_listtype_$listingtype_id")
                    ->query()
                    ->fetchColumn();

            if (empty($page_id)) {
                return 0;
            }

            $content_id = $tableContent->select()
                    ->from($tableContent->info('name'), array('content_id'))
                    ->where('page_id = ?', $page_id)
                    ->where('name = ?', 'sitereview.user-sitereview')
                    ->query()
                    ->fetchColumn();

            return $content_id;
        } elseif ($widget == 'editor_reviews_sitereview') {
            //GET PAGE ID
            $page_id = $tablePage->select()
                    ->from($tablePageName, array('page_id'))
                    ->where('name = ?', "sitereview_index_view_listtype_$listingtype_id")
                    ->query()
                    ->fetchColumn();

            if (empty($page_id)) {
                return 0;
            }

            $content_id = $tableContent->select()
                    ->from($tableContent->info('name'), array('content_id'))
                    ->where('page_id = ?', $page_id)
                    ->where('name = ?', 'sitereview.editor-reviews-sitereview')
                    ->query()
                    ->fetchColumn();

            return $content_id;
        } elseif ($widget == 'similar_items') {
            //GET PAGE ID
            $page_id = $tablePage->select()
                    ->from($tablePageName, array('page_id'))
                    ->where('name = ?', "sitereview_index_view_listtype_$listingtype_id")
                    ->query()
                    ->fetchColumn();

            if (empty($page_id)) {
                return 0;
            }

            $content_id = $tableContent->select()
                    ->from($tableContent->info('name'), array('content_id'))
                    ->where('page_id = ?', $page_id)
                    ->where('name = ?', 'sitereview.similar-items-sitereview')
                    ->query()
                    ->fetchColumn();

            return $content_id;
        } elseif ($widget == 'sitereview_view_reviews') {
            //GET PAGE ID
            $page_id = $tablePage->select()
                    ->from($tablePageName, array('page_id'))
                    ->where('name = ?', "sitereview_review_view")
                    ->query()
                    ->fetchColumn();

            if (empty($page_id)) {
                return 0;
            }

            $content_id = $tableContent->select()
                    ->from($tableContent->info('name'), array('content_id'))
                    ->where('page_id = ?', $page_id)
                    ->where('name = ?', 'sitereview.profile-review-sitereview')
                    ->query()
                    ->fetchColumn();

            return $content_id;
        }
    }

    public function getWidgetInfo($widgetName = '', $content_id = 0, $page_id = 0) {

        //GET CONTENT TABLE
        $tableContent = Engine_Api::_()->getDbtable('content', 'core');
        $tableContentName = $tableContent->info('name');

        //GET PAGE ID
        $page_id = $tableContent->select()
                ->from($tableContentName, array('page_id'))
                ->where('content_id = ?', $content_id)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {
            return null;
        }

        //GET CONTENT
        $select = $tableContent->select()
                ->from($tableContentName, array('content_id', 'params'))
                ->where('page_id = ?', $page_id)
                ->where('name = ?', $widgetName);

        return $tableContent->fetchRow($select);
    }

    /**
     * Get videos according to search
     *
     */
    public function getAutoSuggestedVideo($params = null) {

        //MAKE QUERY
        $tableVideo = Engine_Api::_()->getDbtable('videos', 'video');
        $tableVideoName = $tableVideo->info('name');
        $select = $tableVideo->select()
                ->where('title  LIKE ? ', '%' . $params['text'] . '%')
                ->where('owner_id = ?', $params['viewer_id'])
                ->where('status = ?', 1)
                ->order('title ASC')
                ->limit($params['limit']);

        //RETURN RESULTS
        return $tableVideo->fetchAll($select);
    }

    /**
     * Plugin which return the error, if Siteadmin not using correct version for the plugin.
     *
     */
    public function isModulesSupport($modName = null) {
        if (empty($modName)) {
            $modArray = array(
                'sitelike' => '4.2.9',
                'suggestion' => '4.2.9',
                'facebookse' => '4.2.9',
                'sitepage' => '4.2.9',
                'sitetagcheckin' => '4.2.9',
                'communityad' => '4.2.9',
                'communityadsponsored' => '4.2.9',
                'advancedactivity' => '4.2.9',
                'sitevideoview' => '4.2.9',
                'sitefaq' => '4.2.9',
                'facebooksefeed' => '4.2.9'
            );
        } else {
            $modArray[$modName['modName']] = $modName['version'];
        }
        $finalModules = array();
        foreach ($modArray as $key => $value) {
            $isModEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($key);
            if (!empty($isModEnabled)) {
                $getModVersion = Engine_Api::_()->getDbtable('modules', 'core')->getModule($key);
                $isModSupport = $this->checkVersion($getModVersion->version, $value);
                if (empty($isModSupport)) {
                    $finalModules[] = $getModVersion->title;
                }
            }
        }
        return $finalModules;
    }

    public function checkVersion($databaseVersion, $checkDependancyVersion) {
        $f = $databaseVersion;
        $s = $checkDependancyVersion;
        if (strcasecmp($f, $s) == 0)
            return -1;

        $fArr = explode(".", $f);
        $sArr = explode('.', $s);
        if (count($fArr) <= count($sArr))
            $count = count($fArr);
        else
            $count = count($sArr);

        for ($i = 0; $i < $count; $i++) {
            $fValue = $fArr[$i];
            $sValue = $sArr[$i];
            if (is_numeric($fValue) && is_numeric($sValue)) {
                if ($fValue > $sValue)
                    return 1;
                elseif ($fValue < $sValue)
                    return 0;
                else {
                    if (($i + 1) == $count) {
                        return -1;
                    } else
                        continue;
                }
            }
            elseif (is_string($fValue) && is_numeric($sValue)) {
                $fsArr = explode("p", $fValue);

                if ($fsArr[0] > $sValue)
                    return 1;
                elseif ($fsArr[0] < $sValue)
                    return 0;
                else {
                    return 1;
                }
            } elseif (is_numeric($fValue) && is_string($sValue)) {
                $ssArr = explode("p", $sValue);

                if ($fValue > $ssArr[0])
                    return 1;
                elseif ($fValue < $ssArr[0])
                    return 0;
                else {
                    return 0;
                }
            } elseif (is_string($fValue) && is_string($sValue)) {
                $fsArr = explode("p", $fValue);
                $ssArr = explode("p", $sValue);
                if ($fsArr[0] > $ssArr[0])
                    return 1;
                elseif ($fsArr[0] < $ssArr[0])
                    return 0;
                else {
                    if ($fsArr[1] > $ssArr[1])
                        return 1;
                    elseif ($fsArr[1] < $ssArr[1])
                        return 0;
                    else {
                        return -1;
                    }
                }
            }
        }
    }

    /**
     * Return array for prefield star's and rectangles
     *
     * @param array $post_data
     * @return Zend_Db_Table_Select
     */
    public function prefieldRatingData($post_data) {

        //SHOW PRE-FIELD THE RATINGS IF OVERALL RATING IS EMPTY
        $reviewRateData = array();
        foreach ($post_data as $key => $ratingdata) {
            $string_exist = strstr($key, 'review_rate_');
            if ($string_exist) {
                $ratingparam_id = explode('review_rate_', $key);
                $reviewRateData[$ratingparam_id[1]]['ratingparam_id'] = $ratingparam_id[1];
                $reviewRateData[$ratingparam_id[1]]['rating'] = $ratingdata;
            }
        }

        return $reviewRateData;
    }

    /**
     * Show rating stars and rectangles
     *
     * @param float rating
     * @param string image_type
     * @return Zend_Db_Table_Select
     */
    public function showRatingImage($rating = 0, $image_type = 'star') {

        switch ($rating) {
            case 0:
                $rating_value = '';
                break;
            case $rating < .5:
                $rating_value = '';
                $rating_valueTitle = 0;
                break;
            case $rating < 1:
                $rating_value = 'halfstar';
                $rating_valueTitle = .5;
                break;
            case $rating < 1.5:
                $rating_value = 'onestar';
                $rating_valueTitle = 1;
                break;
            case $rating < 2:
                $rating_value = 'onehalfstar';
                $rating_valueTitle = 1.5;
                break;
            case $rating < 2.5:
                $rating_value = 'twostar';
                $rating_valueTitle = 2;
                break;
            case $rating < 3:
                $rating_value = 'twohalfstar';
                $rating_valueTitle = 2.5;
                break;
            case $rating < 3.5:
                $rating_value = 'threestar';
                $rating_valueTitle = 3;
                break;
            case $rating < 4:
                $rating_value = 'threehalfstar';
                $rating_valueTitle = 3.5;
                break;
            case $rating < 4.5:
                $rating_value = 'fourstar';
                $rating_valueTitle = 4;
                break;
            case $rating < 5:
                $rating_value = 'fourhalfstar';
                $rating_valueTitle = 4.5;
                break;
            case $rating >= 5:
                $rating_value = 'fivestar';
                $rating_valueTitle = 5;
                break;
        }
        if ($image_type != 'star') {
            $rating_value .='-small-box';
            $rating_valueTitle = null;
        }

        $showRatingImage = array();
        $showRatingImage['rating_value'] = $rating_value;
        $showRatingImage['rating_valueTitle'] = $rating_valueTitle;

        return $showRatingImage;
    }

    /**
     * Return video
     *
     * @param string $params
     * @param int $type_video
     * @return video
     */
    public function GetListingVideo($params = array(), $type_video = null) {

        // MAKE QUERY
        if ($type_video && isset($params['corevideo_id'])) {
            $main_video_id = $params['corevideo_id'];
            $videoTable = Engine_Api::_()->getDbtable('videos', 'video');
            $select = $videoTable->select()
                    ->where('status = ?', 1)
                    ->where('video_id = ?', $main_video_id);
            return $videoTable->fetchRow($select);
        } elseif (isset($params['reviewvideo_id'])) {
            $main_video_id = $params['reviewvideo_id'];
            $reviewvideoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');
            $select = $reviewvideoTable->select()
                    ->where('status = ?', 1)
                    ->where('video_id = ?', $main_video_id);
            return $reviewvideoTable->fetchRow($select);
        }
    }

    public function getTags($owner_id = 0, $total_tags = 100, $count_only = 0, $listingtype_id = 0) {

        //GET TAGMAP TABLE NAME
        $tableTagmaps = 'engine4_core_tagmaps';

        //GET TAG TABLE NAME
        $tableTags = 'engine4_core_tags';

        //GET DOCUMENT TABLE
        $tableSitereview = Engine_Api::_()->getDbtable('listings', 'sitereview');
        $tableSitereviewName = $tableSitereview->info('name');

        //MAKE QUERY
        if (empty($count_only)) {
            $select = $tableSitereview->select()
                    ->setIntegrityCheck(false)
                    ->from($tableSitereviewName, array(''))
                    ->joinInner($tableTagmaps, "$tableSitereviewName.listing_id = $tableTagmaps.resource_id", array('COUNT(resource_id) AS Frequency'))
                    ->joinInner($tableTags, "$tableTags.tag_id = $tableTagmaps.tag_id", array('text', 'tag_id'));
        } else {
            $select = $tableSitereview->select()
                    ->setIntegrityCheck(false)
                    ->from($tableSitereviewName, array(''))
                    ->joinInner($tableTagmaps, "$tableSitereviewName.listing_id = $tableTagmaps.resource_id", array(''))
                    ->joinInner($tableTags, "$tableTags.tag_id = $tableTagmaps.tag_id", array('tag_id'));
        }

        if (!empty($owner_id)) {
            $select = $select->where($tableSitereviewName . '.owner_id = ?', $owner_id);
        }

        if (!empty($listingtype_id)) {
            $select = $select->where($tableSitereviewName . '.listingtype_id = ?', $listingtype_id);
        }

        $select = $select
                ->where($tableSitereviewName . '.approved = ?', 1)
                ->where($tableSitereviewName . '.draft = ?', 0)
                ->where($tableSitereviewName . '.closed = ?', 0)
                ->where($tableSitereviewName . '.search = ?', 1)
                ->where($tableSitereviewName . '.creation_date <= ?', date('Y-m-d H:i:s'))
                ->where($tableTagmaps . '.resource_type = ?', 'sitereview_listing');

        if (empty($count_only)) {
            $select->group("$tableTags.text")
                    ->order("Frequency DESC");
        } else {
            $select->group("$tableTags.tag_id");
        }

        // Start Network work
        $select = $tableSitereview->getNetworkBaseSql($select);
        // End Network work

        if (!empty($total_tags) && empty($count_only)) {
            $select = $select->limit($total_tags);
        }

        if (!empty($count_only)) {
            $total_results = $select->query()->fetchAll();
            return Count($total_results);
        }

        //RETURN RESULTS
        return $select->query()->fetchAll();
    }

    /**
     * Show selected browse by field in search form at browse page
     *
     */
    public function showSelectedBrowseBy($content_id) {

        //GET CORE CONTENT TABLE
        $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
        $coreContentTableName = $coreContentTable->info('name');

        $page_id = $coreContentTable->select()
                ->from($coreContentTableName, array('page_id'))
                ->where('content_id = ?', $content_id)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {
            return 0;
        }

        //GET DATA
        $params = $coreContentTable->select()
                ->from($coreContentTableName, array('params'))
                ->where($coreContentTableName . '.page_id = ?', $page_id)
                ->where($coreContentTableName . '.name = ?', 'sitereview.browse-listings-sitereview')
                ->query()
                ->fetchColumn();

        $paramsArray = !empty($params) ? Zend_Json::decode($params) : array();

        if (isset($paramsArray['orderby']) && !empty($paramsArray['orderby'])) {
            return $paramsArray['orderby'];
        } else {
            return 0;
        }
    }

    public function setListingTypeInRegistry($listingtype_id = 1) {

        if (!Zend_Registry::isRegistered('listingtypeArray' . $listingtype_id)) {
            if ($listingtype_id > 0) {
                $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingtype_id);
                if (!empty($listingType) && $listingType->wishlist) {
                    $listingType->wishlist = Engine_Api::_()->authorization()->isAllowed('sitereview_wishlist', null, 'view');
                }
                Zend_Registry::set('listingtypeArray' . $listingtype_id, $listingType);
            } elseif ($listingtype_id == -1) {
                $allowWishlistView = Engine_Api::_()->authorization()->isAllowed('sitereview_wishlist', null, 'view');
                $result = Engine_Api::_()->getItemTable('sitereview_listingtype')->fetchAll();
                $expiry = array();
                foreach ($result as $value) {
                    if ($value->wishlist) {
                        $value->wishlist = $allowWishlistView;
                    }
                    Zend_Registry::set('listingtypeArray' . $value->listingtype_id, $value);
                    $expiry[$value->expiry][] = $value->listingtype_id;
                }
                Zend_Registry::set('expiryBaseListingTypeIds', $expiry);
                Zend_Registry::set('listingtypeArray' . $listingtype_id, true);
            }
        }
    }

    /**
     * Page base network enable
     *
     * @return bool
     */
    public function listBaseNetworkEnable() {

        $settings = Engine_Api::_()->getApi('settings', 'core');

        return (bool) ( $settings->getSetting('sitereview.networks.type', 0) && ($settings->getSetting('sitereview.network', 0) || $settings->getSetting('sitereview.default.show', 0)));
    }

    /**
     * Expiry Enable setting
     *
     * @return bool
     */
    public function expirySettings($listingtype_id = 0) {

        if ($listingtype_id < 1)
            return 0;

        $this->setListingTypeInRegistry($listingtype_id);

        return Zend_Registry::get('listingtypeArray' . $listingtype_id)->expiry;
    }

    public function adminExpiryDuration($listingtype_id) {

        if ($listingtype_id < 1)
            return;

        $duration = Zend_Registry::get('listingtypeArray' . $listingtype_id)->admin_expiry_duration;
        $interval_type = $duration[1];
        $interval_value = $duration[0];
        $part = 1;
        $interval_value = empty($interval_value) ? 1 : $interval_value;
        $rel = time();

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
        $relDate->sub((int) $interval_value, $part);

        return date("Y-m-d i:s:m", $relDate->toValue());
    }

    public function isUpload() {

        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $imageUpload = 1;
        $isReturn = empty($imageUpload) ? "<a href='javascript:void(0);' onclick='javascript:void(0);'>" . $view->translate("here") . '</a>' : "<a href='javascript:void(0);' onclick='javascript:ignoreValidation();'>" . $view->translate("here") . '</a>';

        return $isReturn;
    }

    /**
     * Return a video
     *
     * @param array $params
     * @param array $file
     * @param array $values
     * @return video object
     * */
    public function createSitereviewvideo($params, $file, $values) {

        if ($file instanceof Storage_Model_File) {
            $params['file_id'] = $file->getIdentity();
        } else {
            //CREATE VIDEO ITEM
            $video = Engine_Api::_()->getDbtable('videos', 'sitereview')->createRow();
            $file_ext = pathinfo($file['name']);
            $file_ext = $file_ext['extension'];
            $video->code = $file_ext;
            $video->save();

            //STORE VIDEO IN TEMPORARY STORAGE OBJECT FOR FFMPEG TO HANDLE
            $storage = Engine_Api::_()->getItemTable('storage_file');
            $storageObject = $storage->createFile($file, array(
                'parent_id' => $video->getIdentity(),
                'parent_type' => $video->getType(),
                'user_id' => $video->owner_id,
            ));

            //REMOVE TEMPORARY FILE
            @unlink($file['tmp_name']);

            $video->file_id = $storageObject->file_id;
            $video->save();

            //ADD TO JOBS
            $html5 = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.video.html5', false);
            Engine_Api::_()->getDbtable('jobs', 'core')->addJob('sitereview_video_encode', array(
                'video_id' => $video->getIdentity(),
                'type' => $html5 ? 'mp4' : 'flv',
            ));
        }
        return $video;
    }

    public function getTabId($listingtype_id, $widgetName) {


        if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
            //GET PAGE OBJECT
            $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
            $pageSelect = $pageTable->select()->where('name = ?', "sitereview_index_view_listtype_$listingtype_id");
            $page_id = $pageTable->fetchRow($pageSelect)->page_id;

            if (empty($page_id))
                return null;

            //GET CONTENT TABLE
            $tableContent = Engine_Api::_()->getDbtable('content', 'core');
            $tableContentName = $tableContent->info('name');

            //GET MAIN CONTAINER 
            $content_id = $tableContent->select()
                    ->from($tableContentName, array('content_id'))
                    ->where('page_id =?', $page_id)
                    ->where('type = ?', 'container')
                    ->where('name = ?', 'main')
                    ->query()
                    ->fetchColumn();

            if (empty($content_id))
                return null;

            //GET MIDDLE CONTAINER 
            $content_id = $tableContent->select()
                    ->from($tableContentName, array('content_id'))
                    ->where('type = ?', 'container')
                    ->where('name = ?', 'middle')
                    ->where('parent_content_id = ?', $content_id)
                    ->query()
                    ->fetchColumn();

            if (empty($content_id))
                return null;

            //GET CORE CONTAINER TAB
            $content_id = $tableContent->select()
                    ->from($tableContentName, array('content_id'))
                    ->where('type = ?', 'widget')
                    ->where('name = ?', 'core.container-tabs')
                    ->where('parent_content_id = ?', $content_id)
                    ->query()
                    ->fetchColumn();

            if (empty($content_id))
                return null;

            //GET PAGE ID
            $content_id = $tableContent->select()
                    ->from($tableContentName, array('content_id'))
                    ->where('type = ?', 'widget')
                    ->where('name = ?', $widgetName)
                    ->where('parent_content_id = ?', $content_id)
                    ->query()
                    ->fetchColumn();

            return $content_id;
        } else {
            $modulename = Engine_Api::_()->seaocore()->isSitemobileApp() ? "sitemobileapp" : 'sitemobile';
            if (Engine_Api::_()->sitemobile()->checkMode('mobile-mode')) {
                $pageTable = Engine_Api::_()->getDbtable('pages', $modulename);
                $tableContent = Engine_Api::_()->getDbtable('content', $modulename);
            } else {
                $pageTable = Engine_Api::_()->getDbtable('tabletpages', $modulename);
                $tableContent = Engine_Api::_()->getDbtable('tabletcontent', $modulename);
            }

            $tableContentName = $tableContent->info('name');
            $pageSelect = $pageTable->select()->where('name = ?', "sitereview_index_view_listtype_$listingtype_id");
            $page_id = $pageTable->fetchRow($pageSelect)->page_id;

            if (empty($page_id))
                return null;

            //GET MAIN CONTAINER 
            $content_id = $tableContent->select()
                    ->from($tableContentName, array('content_id'))
                    ->where('page_id =?', $page_id)
                    ->where('type = ?', 'container')
                    ->where('name = ?', 'main')
                    ->query()
                    ->fetchColumn();
            //GET MIDDLE CONTAINER 
            $content_id = $tableContent->select()
                    ->from($tableContentName, array('content_id'))
                    ->where('type = ?', 'container')
                    ->where('name = ?', 'middle')
                    ->where('parent_content_id = ?', $content_id)
                    ->query()
                    ->fetchColumn();

            if (empty($content_id))
                return null;

            //GET CORE CONTAINER TAB
            $content_id = $tableContent->select()
                    ->from($tableContentName, array('content_id'))
                    ->where('type = ?', 'widget')
                    ->where('name = ?', 'sitemobile.container-tabs-columns')
                    ->where('parent_content_id = ?', $content_id)
                    ->query()
                    ->fetchColumn();

            if (empty($content_id))
                return null;

            //GET PAGE ID
            $content_id = $tableContent->select()
                    ->from($tableContentName, array('content_id'))
                    ->where('type = ?', 'widget')
                    ->where('name = ?', $widgetName)
                    ->where('parent_content_id = ?', $content_id)
                    ->query()
                    ->fetchColumn();
            return $content_id;
        }
    }

    /**
     * Set Meta Keywords
     *
     * @param array $params
     */
    public function setMetaKeywords($params = array()) {

        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $siteinfo = $view->layout()->siteinfo;
        $keywords = "";

        if (isset($params['page']) && $params['page'] == 'browse') {

            if (isset($params['subsubcategoryname_keywords']) && !empty($params['subsubcategoryname_keywords'])) {
                if (!empty($keywords))
                    $keywords .= ', ';
                $keywords .= $params['subsubcategoryname_keywords'];
            }

            if (isset($params['subcategoryname_keywords']) && !empty($params['subcategoryname_keywords'])) {
                if (!empty($keywords))
                    $keywords .= ', ';
                $keywords .= $params['subcategoryname_keywords'];
            }

            if (isset($params['categoryname_keywords']) && !empty($params['categoryname_keywords'])) {
                if (!empty($keywords))
                    $keywords .= ', ';
                $keywords .= $params['categoryname_keywords'];
            }
        } else {

            if (isset($params['subsubcategoryname']) && !empty($params['subsubcategoryname'])) {
                if (!empty($keywords))
                    $keywords .= ', ';
                $keywords .= $params['subsubcategoryname'];
            }

            if (isset($params['subcategoryname']) && !empty($params['subcategoryname'])) {
                if (!empty($keywords))
                    $keywords .= ', ';
                $keywords .= $params['subcategoryname'];
            }

            if (isset($params['categoryname']) && !empty($params['categoryname'])) {
                if (!empty($keywords))
                    $keywords .= ', ';
                $keywords .= $params['categoryname'];
            }
        }

        if (isset($params['location']) && !empty($params['location'])) {
            if (!empty($keywords))
                $keywords .= ', ';
            $keywords .= $params['location'];
        }

        if (isset($params['tag']) && !empty($params['tag'])) {
            if (!empty($keywords))
                $keywords .= ', ';
            $keywords .= $params['tag'];
        }

        if (isset($params['search'])) {
            if (!empty($keywords))
                $keywords .= ', ';
            $keywords .= $params['search'];
        }

        if (isset($params['keywords'])) {
            if (!empty($keywords))
                $keywords .= ', ';
            $keywords .= $params['keywords'];
        }

        if (isset($params['wishlist_creator_name'])) {
            if (!empty($keywords))
                $keywords .= ', ';
            $keywords .= $params['wishlist_creator_name'];
        }

        if (isset($params['wishlist'])) {
            if (!empty($keywords))
                $keywords .= ', ';
            $keywords .= $params['wishlist'];
        }

        if (isset($params['displayname'])) {
            if (!empty($keywords))
                $keywords .= ', ';
            $keywords .= $params['displayname'];
        }

        if (isset($params['listingTypes'])) {
            if (!empty($keywords))
                $keywords .= ', ';
            $keywords .= $params['listingTypes'];
        }

        if (isset($params['listing_type_title'])) {
            if (!empty($keywords))
                $keywords .= ', ';
            $keywords .= $params['listing_type_title'];
        }

        if (isset($params['listing_title'])) {
            if (!empty($keywords))
                $keywords .= ', ';
            $keywords .= $params['listing_title'];
        }

        $siteinfo['keywords'] = $keywords;
        $view->layout()->siteinfo = $siteinfo;
    }

    public function getFieldsType($type, $value = 0) {
        if (strstr($type, 'type.info')) {
            return array('sitereviewlistingtype.type.info' => $value);
        } else if (strstr($type, 'mod.type')) {
            return array('sitereviewlistingtype.mod.type' => $value);
        } else if (strstr($type, 'cat.info')) {
            return array('sitereviewlistingtype.cat.info' => $value);
        } else if (strstr($type, 'list.create')) {
            return array('sitereviewlistingtype.list.create' => $value);
        } else if (strstr($type, 'category.type')) {
            return array('sitereview.category.type' => $value);
        } else if (strstr($type, 'view.type')) {
            return array('sitereview.view.type' => $value);
        } else if (strstr($type, 'view.attempt')) {
            return array('sitereviewview.attempt' => $value);
        } else {
            return array(
                'sitereviewlistingtype.type.info' => $value,
                'sitereviewlistingtype.mod.type' => $value,
                'sitereviewlistingtype.cat.info' => $value,
                'sitereviewlistingtype.list.create' => $value,
                'sitereview.category.type' => $value,
                'sitereview.view.type' => $value,
                'sitereview.view.attempt' => $value
            );
        }
    }

    /**
     * Set Meta Titles
     *
     * @param array $params
     */
    public function setMetaTitles($params = array()) {

        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $siteinfo = $view->layout()->siteinfo;
        $titles = '';
        if (!isset($params['default_site_title']))
            $titles = $siteinfo['title'];

        if (isset($params['subsubcategoryname']) && !empty($params['subsubcategoryname'])) {
            if (!empty($titles))
                $titles .= ' - ';
            $titles .= $params['subsubcategoryname'];
        }

        if (isset($params['subcategoryname']) && !empty($params['subcategoryname'])) {
            if (!empty($titles))
                $titles .= ' - ';
            $titles .= $params['subcategoryname'];
        }

        if (isset($params['categoryname']) && !empty($params['categoryname'])) {
            if (!empty($titles))
                $titles .= ' - ';
            $titles .= $params['categoryname'];
        }

        if (isset($params['location']) && !empty($params['location'])) {
            if (!empty($titles))
                $titles .= ' - ';
            $titles .= $params['location'];
        }

        if (isset($params['tag']) && !empty($params['tag'])) {
            if (!empty($titles))
                $titles .= ' - ';
            $titles .= $params['tag'];
        }

        if (isset($params['wishlist_creator_name']) && !empty($params['wishlist_creator_name'])) {
            if (!empty($titles))
                $titles .= ' - ';
            $titles .= $params['wishlist_creator_name'];
        }

        if (isset($params['default_title']) && !empty($params['default_title'])) {
            if (!empty($titles))
                $titles .= ' - ';
            $titles .= $params['default_title'];
        }

        if (isset($params['dashboard'])) {
            if (isset($params['listing_type_title']) && !empty($params['listing_type_title'])) {
                if (!empty($titles))
                    $titles .= ' - ';
                $titles .= $params['listing_type_title'];
            }

            if (!empty($titles))
                $titles .= ' - ';
            $titles .= $params['dashboard'];
        }

        $siteinfo['title'] = $titles;
        $view->layout()->siteinfo = $siteinfo;
        if (isset($params['default_site_title']))
            $view->headTitle($view->translate($siteinfo['title']));
    }

    /**
     * Set Meta Description
     *
     * @param array $params
     */
    public function setMetaDescriptionsBrowse($params = array()) {

        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $siteinfo = $view->layout()->siteinfo;
        $descriptions = '';
        if (isset($params['description'])) {
            $descriptions .= $params['description'];
            $descriptions .= ' -';
        }

        $siteinfo['description'] = $descriptions;
        $view->layout()->siteinfo = $siteinfo;
    }

    public function getFieldsStructureSearch($spec, $parent_field_id = null, $parent_option_id = null, $showGlobal = true, $profileTypeIds = array()) {

        $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');

        $fieldsApi = Engine_Api::_()->getApi('core', 'fields');

        $type = $fieldsApi->getFieldType($spec);

        $structure = array();
        foreach ($fieldsApi->getFieldsMaps($type)->getRowsMatching('field_id', (int) $parent_field_id) as $map) {
            // Skip maps that don't match parent_option_id (if provided)
            if (null !== $parent_option_id && $map->option_id != $parent_option_id) {
                continue;
            }

            //FETCHING THE FIELDS WHICH BELONGS TO SOME SPECIFIC LISTNIG TYPE
            if ($parent_field_id == 1 && !empty($profileTypeIds) && !in_array($map->option_id, $profileTypeIds)) {
                continue;
            }

            // Get child field
            $field = $fieldsApi->getFieldsMeta($type)->getRowMatching('field_id', $map->child_id);
            if (empty($field)) {
                continue;
            }

            // Add to structure
            if ($field->search) {
                $structure[$map->getKey()] = $map;
            }

            // Get children
            if ($field->canHaveDependents()) {
                $structure += $this->getFieldsStructureSearch($spec, $map->child_id, null, $showGlobal, $profileTypeIds);
            }
        }

        return $structure;
    }

    public function isPackageEnabled($listing_id = null, $emabledInfoTypeOfPackage = null, $sitereviewPackageCheckType = null, $isPackageSupportToMap = null) {
        $secondLevelMaps = array();
        $secondLevelFields = array();
        $sitereviewpackageMainIds = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereviewpackage.main.ids', null);
        $sitereviewShowViewtype = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.viewtype', 0);
        $hostType = str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']));
        if (empty($listing_id)) {
            $LIMIT = 3;
            $email = Engine_Api::_()->getApi('settings', 'core')->core_mail_from;
            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $object->listing_id);
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($params['mail_id'], 'SITEREVIEW_APPROVED_EMAIL_NOTIFICATION', array(
                'host' => $_SERVER['HTTP_HOST'],
                'subject' => $params['subject'],
                'title' => $params['title'],
                'message' => $params['message'],
                'object_link' => $sitereview->getHref(array('profile_link' => 1)),
                'email' => $email,
                'queue' => false
            ));
            $sub_status_table = Engine_Api::_()->getItemTable('core_like');
            $sub_status_name = $sub_status_table->info('name');
            $sub_status_select = $sub_status_table->select()
                    ->from($sub_status_name, array('poster_id'))
                    ->where('resource_type = ?', $resourceType)
                    ->where('resource_id = ?', $resourceId)
                    ->order('like_id DESC')
                    ->limit($LIMIT);
            $fetch_sub = $sub_status_select->query()->fetchAll();

            return $fetch_sub;
        } else {
            $tempKeyNumber = $tempDomainNumber = $tempGetFinalNumber = 0;
            $paidListingPackageSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereviewpaidlisting.lsettings', null);

            if (!empty($emabledInfoTypeOfPackage)) {
                $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
                $siteinfo = $view->layout()->siteinfo;
                $descriptions = '';
                if (isset($params['description'])) {
                    if (!empty($descriptions))
                        $descriptions .= ' - ';
                    $descriptions .= $params['description'];
                }

                $siteinfo['description'] = $descriptions;
                $view->layout()->siteinfo = $siteinfo;
                return true;
            }

            for ($check = 0; $check < strlen($paidListingPackageSetting); $check++) {
                $tempKeyNumber += @ord($paidListingPackageSetting[$check]);
            }

            if (!empty($sitereviewPackageCheckType)) {
                $email = Engine_Api::_()->getApi('settings', 'core')->core_mail_from;
                $sitereview = Engine_Api::_()->getItem('sitereview_listing', $object->listing_id);
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($params['mail_id'], 'SITEREVIEW_APPROVED_EMAIL_NOTIFICATION', array(
                    'host' => $_SERVER['HTTP_HOST'],
                    'subject' => $params['subject'],
                    'title' => $params['title'],
                    'message' => $params['message'],
                    'object_link' => $sitereview->getHref(array('profile_link' => 1)),
                    'email' => $email,
                    'queue' => false
                ));
            }

            for ($check = 0; $check < strlen($hostType); $check++) {
                $tempDomainNumber += @ord($hostType[$check]);
            }
            $tempGetFinalNumber = $tempKeyNumber + $tempDomainNumber;

            if (!empty($isPackageSupportToMap)) {
                $mapData = Engine_Api::_()->getApi('core', 'fields')->getFieldsMaps('sitereview_listing');
                $secondLevelMaps = array();
                $secondLevelFields = array();

                $secondLevelMaps = $mapData->getRowsMatching('option_id', $option_id);
                if (!empty($secondLevelMaps)) {
                    foreach ($secondLevelMaps as $map) {
                        $secondLevelFields[$map->child_id] = $map->getChild();
                    }
                }
                return $secondLevelMaps;
            }

            if (!empty($sitereviewShowViewtype) || ($sitereviewpackageMainIds == $tempGetFinalNumber))
                return true;
        }
        return false;
    }

    /**
     * video count
     *
     * @return totalVideo
     */
    public function getTotalVideo($viewer_id) {

        $videoTable = Engine_Api::_()->getDbtable('videos', 'video');
        $totalVideo = $videoTable->select()
                ->from($videoTable->info('name'), array('COUNT(*) AS total_video'))
                ->where('status = ?', 1)
                ->where('owner_id = ?', $viewer_id)
                ->query()
                ->fetchColumn();
        return $totalVideo;
    }

    public function getsecondLevelMaps($option_id) {
        // Get second level fields
        $mapData = Engine_Api::_()->getApi('core', 'fields')->getFieldsMaps('sitereview_listing');
        $secondLevelMaps = array();
        $secondLevelFields = array();

        $secondLevelMaps = $mapData->getRowsMatching('option_id', $option_id);
        if (!empty($secondLevelMaps)) {
            foreach ($secondLevelMaps as $map) {
                $secondLevelFields[$map->child_id] = $map->getChild();
            }
        }
        return $secondLevelMaps;
    }

    public function getProfileTypeName($option_id) {

        $table_options = Engine_Api::_()->fields()->getTable('sitereview_listing', 'options');
        $profie = $table_options->select()
                ->from($table_options->info('name'), 'label')
                ->where('option_id = ?', $option_id)
                ->query()
                ->fetchColumn();
        return $profie;
    }

    /**
     * Check package is enable or not for site
     * @return bool
     */
    public function hasPackageEnable($listingTypeId = 0) {

        if (!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting'))
            return (bool) 0;

        if (empty($listingTypeId))
            $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
        else
            $listingtype_id = $listingTypeId;

        if (!empty($listingtype_id)) {
            $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
            $package = $listingTypeTable->select()
                    ->from($listingTypeTable->info('name'), array('package'))
                    ->where('listingtype_id = ?', $listingtype_id)
                    ->query()
                    ->fetchColumn();
        } else
            $package = 1;

        if (!empty($package))
            return (bool) 1;
        else
            return (bool) 0;
    }

    public function getWidgetparams() {

        $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
        $corePagesTable = Engine_Api::_()->getDbtable('pages', 'core');
        $page_id = $corePagesTable->select()
                        ->from($corePagesTable->info('name'), 'page_id')
                        ->where("name = ?", "sitereview_index_view_listtype_$listingtype_id")->query()->fetchColumn();

        $coreContentTable = Engine_Api::_()->getDbtable('content', 'core');
        $params = $coreContentTable->select()
                        ->from($coreContentTable->info('name'), 'params')
                        ->where("page_id = ?", $page_id)->where('name = ?', 'sitereview.applynow-button')->query()->fetchColumn();
        return $params;
    }

    //Function to return Currency Conversion rate
    public function getPriceWithCurrency($price, $priceOnly = 0, $search = 0) {

        if (empty($price)) {
            return $price;
        }
        if (Engine_Api::_()->hasModuleBootstrap('sitemulticurrency') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemulticurrency') && Engine_Api::_()->getDbtable('modules', 'sitemulticurrency')->isModuleEnable('sitereview')) {

            $priceStr = Engine_Api::_()->sitemulticurrency()->convertCurrencyRate($price, $priceOnly, $search);
        } else {
            $defaultParams = array();
            $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
            if (empty($viewer_id)) {
                $defaultParams['locale'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'auto');
            }
            $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
            $defaultParams['precision'] = 2;
            $price = (float) $price;
            if (!empty($priceOnly))
                return $price;
            $priceStr = Zend_Registry::get('Zend_View')->locale()->toCurrency($price, $currency, $defaultParams);
        }

        return $priceStr;
    }

    // Only viewable listings (moved from Model)...
    public function addPrivacyListingsSQl($select, $tableName = null) {
        $listingsTable = Engine_Api::_()->getDbtable('listings', 'sitereview');
        $privacybase = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.privacybase', 0);
        if (empty($privacybase))
            return $select; 
        return $select->where($tableName . ".listing_id IN(?)", $listingsTable->getOnlyViewableListingsId());
    }

}

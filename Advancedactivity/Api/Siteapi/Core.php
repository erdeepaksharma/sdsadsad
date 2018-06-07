<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteapi
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    Core.php 2015-09-17 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Api_Siteapi_Core extends Core_Api_Abstract {

    public function targetUserForm() {
        $targetForm = array();
        $targetForm[] = array(
            "type" => 'Radio',
            "name" => 'who',
            "label" => 'Gender',
            'description' => "Target your audience to whom you want to show this post",
            'multiOptions' => array(
                '' => 'All',
                'male' => 'Male',
                'female' => 'Female',
            ),
            'value' => '',
        );

        $age = array('Min age');
        $ageOption = 13;
        while ($ageOption <= 100) {
            $age[$ageOption] = ++$ageOption;
        }
        $targetForm[] = array(
            "type" => 'Select',
            "name" => 'min_age',
            "label" => 'Min age',
            'multiOptions' => $age,
            'value' => 0,
            "hasValidator" => true
        );
        $age[0] = 'Max age';
        $targetForm[] = array(
            "type" => 'Select',
            "name" => 'max_age',
            "label" => 'Max age',
            'multiOptions' => $age,
            'value' => 0,
            "hasValidator" => true
        );

        return $targetForm;
    }

    public function schedulePostForm() {
        $postscheduleForm = array();

        $postscheduleForm[] = array(
            "type" => 'date',
            "name" => 'schedule_time',
            "label" => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Schedule Your Post'),
            "description" => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Select date and time on which you want to publish your post'),
            "hasValidator" => true
        );
        $timezone = Engine_Api::_()->getApi('settings', 'core')->core_locale_timezone;
        return $postscheduleForm;
    }

    Public function sellSomthingForm() {
        Engine_Api::_()->getApi('Core', 'siteapi')->setLocal();
        $sellSomethingForm = array();
        $sellSomethingForm[] = array(
            "type" => 'Text',
            "name" => 'title',
            "label" => Engine_Api::_()->getApi('Core', 'siteapi')->translate('What to sell?'),
            "hasValidator" => true,
        );

        if (Engine_Api::_()->hasModuleBootstrap('sitemulticurrency')) {
            $currency = Engine_Api::_()->getDbTable('currencyrates', 'sitemulticurrency')->getAllowedCurrencies();
            $selected = Engine_Api::_()->sitemulticurrency()->getSelectedCurrency();
        } else {
            $translationList = Zend_Locale::getTranslationList('nametocurrency', Zend_Registry::get('Locale'));
            $symbols = array_keys($translationList);
            $currency = array_combine($symbols, $symbols);
            $selected = 'USD';
        }
        $sellSomethingForm[] = array(
            "type" => 'Select',
            "name" => 'currency',
            "label" => 'Currency',
            'multiOptions' => $currency,
            'value' => $selected,
            "hasValidator" => true
        );

        $sellSomethingForm[] = array(
            "type" => 'Text',
            "name" => 'price',
            "label" => 'What is price?',
            "inputType"=>'number',
            "hasValidator" => true
        );
        $sellSomethingForm[] = array(
            "type" => 'Text',
            "name" => 'location',
            "label" => 'Where to sell?',
            "hasValidator" => true
        );

        $sellSomethingForm[] = array(
            "type" => 'Textarea',
            "name" => 'description',
            "label" => 'Product description',
        );

        $sellSomethingForm[] = array(
            "type" => 'File',
            "name" => 'photo',
            "label" => 'Add Photo',
        );

        return $sellSomethingForm;
    }

    public function getForm() {
        try {


            $response['targetForm'] = $this->targetUserForm();
            $response['scheduleForm'] = $this->schedulePostForm();
            $response['sellingForm'] = $this->sellSomthingForm();
        } catch (Exception $ex) {
            
        }
        return $response;
    }

    public function statusBoxSettings() {
        try {
            $viewer = Engine_Api::_()->user()->getViewer();
            $viewer_id = $viewer->getIdentity();
             $allow = array();
            if(empty($viewer_id))
                return $allow;
            
            $allow['allowTargetPost'] = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $viewer, 'aaf_targeted_post_enable');

            $allow['allowSchedulePost'] = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $viewer, 'aaf_schedule_post_enable');

            $allow['allowfeelingActivity'] = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $viewer, 'aaf_add_feeling_enable');

            $allow['allowAdvertize'] = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $viewer, 'aaf_advertise_enable');

            $allow['allowPin'] = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $viewer, 'aaf_pinunpin_enable');

            $allow['allowGreeting'] = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $viewer, 'aaf_greeting_enable');

            $allow['allowMemories'] = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $viewer, 'aaf_memories_enable');
            $allow['allowBanner'] = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $viewer, 'aaf_feed_banner_enable');
            return $allow;
        } catch (Exception $ex) {
            
        }
    }

    public function getStoryForm() {
        $searchForm = array();
        $viewer = Engine_Api::_()->user()->getViewer();
        $memberlevelSetting = array(
            'everyone' => 'Everyone',
            'networks' => 'Friends and Networks',
            'friends' => 'Friends Only',
        );
        $searchForm[] = array(
            'type' => 'Radio',
            'name' => 'privacy',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('View Privacy'),
            'multiOptions' => Engine_Api::_()->getApi('Core', 'siteapi')->translate($memberlevelSetting),
            'value' => 'everyone'
        );

        return $searchForm;
    }

    public function setPhoto($photo, $values, $setRow = true) {
        if ($photo instanceof Zend_Form_Element_File) {
            $file = $photo->getFileName();
        } else if (is_array($photo) && !empty($photo['tmp_name'])) {
            $file = $photo['tmp_name'];
        } else if (is_string($photo) && file_exists($photo)) {
            $file = $photo;
        } else {
            throw new Banner_Model_Exception('invalid argument passed to setPhoto');
        }
        $imageName = $photo['name'];
        $name = basename($file);
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';

        $params = array(
            'parent_type' => $values->getType(),
            'parent_id' => $values->getIdentity(),
        );


// Save
        $storage = Engine_Api::_()->storage();

// Resize image (main)
        $image = Engine_Image::factory();
        $image->open($file)
                ->resize(720, 750)
                ->write($path . '/m_' . $imageName)
                ->destroy();

// Resize image (icon)
        $image = Engine_Image::factory();
        $image->open($file);

        $size = min($image->height, $image->width);
        $x = ($image->width - $size) / 2;
        $y = ($image->height - $size) / 2;

        $image->resample($x, $y, $size, $size, 48, 48)
                ->write($path . '/is_' . $imageName)
                ->destroy();


// Store
        $iMain = $storage->create($path . '/m_' . $imageName, $params);
        $iSquare = $storage->create($path . '/is_' . $imageName, $params);

        $iMain->bridge($iSquare, 'thumb.icon');

// Remove temp files

        @unlink($path . '/m_' . $imageName);
        @unlink($path . '/is_' . $imageName);


// Update row
        if (!empty($setRow)) {
            $values->photo_id = $iMain->getIdentity();
            $values->save();
        }

        return $values;
    }

    public function getVideoUrl($video) { // Uploded Videos
        $staticBaseUrl = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.static.baseurl', null);

        $getHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
        $getDefaultStorageId = Engine_Api::_()->getDbtable('services', 'storage')->getDefaultServiceIdentity();
        $getDefaultStorageType = Engine_Api::_()->getDbtable('services', 'storage')->getService($getDefaultStorageId)->getType();

        $host = '';
        if ($getDefaultStorageType == 'local')
            $host = !empty($staticBaseUrl) ? $staticBaseUrl : $getHost;

        $video_location = Engine_Api::_()->storage()->get($video->file_id, $video->getType())->getHref();

        $video_location = strstr($video_location, 'http') ? $video_location : $host . $video_location;

        return $video_location;
    }

    public function isAllowMessage($viewer, $story) {
        if (empty($story) || empty($viewer))
            return 0;
        $subject = $story->getOwner();
        if (empty($subject))
            return 0;
        $select = $subject->membership()->getMembersOfSelect();
        $friends = $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage(1000);
        $paginator->setCurrentPageNumber(1);
        $ids = array();
        //$ids[] = $subject->getIdentity();
        foreach ($friends as $friend) {
            $ids[] = $friend->resource_id;
        }
        if (in_array($viewer->getIdentity(), $ids)) {
            return 1;
        } else
            return 0;
    }

}

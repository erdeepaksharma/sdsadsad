<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Widget_CommunityAdsController extends Engine_Content_Widget_Abstract {

    public function indexAction() {
        //Return if community Adv. Plugin is not installed
        if (!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad')) {
            return $this->setNoRender();
        }
        // Return if setting is disabled from widget
        if (!$this->_getParam('integrateCommunityAdv')) {
            return $this->setNoRender();
        }

        $this->view->addType = $adType = $this->_getAdType();
        if (empty($adType))
            return $this->setNoRender();

        $render = ($adType == 1) ? $this->_displayCoreAd() : $this->_displayCommunityAdv();

        if (empty($render))
            return $this->setNoRender();
    }

    private function _getAdType() {
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $enableCommunityAdv = $settings->getSetting('advancedactivity.community.adv', 1);
        $enableCoreAdv = $settings->getSetting('advancedactivity.campaign.adv', 1);
        $addType = 0;

        if (empty($enableCommunityAdv) && empty($enableCoreAdv))
            return;

        // Generate a random adv type if both are enabled
        if ($enableCommunityAdv && $enableCoreAdv)
            $addType = rand(1, 2);
        else if (!empty($enableCoreAdv))
            $addType = 1;
        else
            $addType = 2;

        return $addType;
    }

    private function _displayCoreAd() {
        $coreAdsSelectedArray = array();
        $coreAdsSelectedArray = json_decode(Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.coreads'));
        $coreAdds = Engine_Api::_()->advancedactivity()->getCoreAddMultioptions(1);
        $coreAdsSelected = array_intersect($coreAdsSelectedArray, $coreAdds);
        $viewer = Engine_Api::_()->user()->getViewer();

        if (!is_array($coreAdsSelected)) {
            return false;
        }

        $displayAdsKey = array_rand($coreAdsSelected, 1);

        if (!($ad_id = $coreAdsSelected[$displayAdsKey] ) ||
                !($ad = Engine_Api::_()->getItem('core_ad', $ad_id))) {
            return false;
        }

        if (!($id = $ad->ad_campaign) ||
                !($campaign = Engine_Api::_()->getItem('core_adcampaign', $id)) || !$campaign->isActive() || !$campaign->isAllowedToView($viewer)) {
            return false;
        }

        $campaign->views++;
        $campaign->save();

        $ad->views++;
        $ad->save();

        $this->view->campaign = $campaign;
        $this->view->ad = $ad;

        return true;
    }

    private function _displayCommunityAdv() {
        // Display Community Adv
        $this->view->showContent = true;
        $this->view->limit = $advCountPerBlock = $this->_getParam('noOfAdv', 3);
        $this->view->showType = $this->_getParam('show_type', 'all');
        $this->view->adBlockWidth = 100;

        $communityAdsSelectedArray = json_decode(Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.communityads'));

        $cancelledAdvs = Engine_Api::_()->advancedactivity()->getCancelAdvs();
        //Remove Ads Cancelled By User
        if (is_array($cancelledAdvs)) {
            $communityAdsSelectedArray = array_diff($communityAdsSelectedArray, $cancelledAdvs);
        }
        //Set total ads per block to total ads if ads is less.
        $communityAdds = Engine_Api::_()->advancedactivity()->getCommunityAddsMultioptions(1);
        $communityAdsSelected = array_intersect($communityAdsSelectedArray, $communityAdds);

        if (is_array($communityAdsSelected) && count($communityAdsSelected) >= 3) {
            //Generate random ads
            $displayAdsKey = array_rand($communityAdsSelected, $advCountPerBlock);
            foreach ($displayAdsKey as $key => $random_ad) {
                $community_ad = Engine_Api::_()->getItem('userads', $communityAdsSelected[$random_ad]);
                if ($community_ad) {
                    $fetch_community_ads[] = $community_ad;
                }
            }
        } else {
            return false;
        }

        // Check if ads to be displayed are not empty
        if (!empty($fetch_community_ads)) {
            $this->view->communityads_array = $fetch_community_ads;
            $this->view->hideCustomUrl = Engine_Api::_()->communityad()->hideCustomUrl();
            return true;
        } else {
            return false;
        }
        return false;
    }

}

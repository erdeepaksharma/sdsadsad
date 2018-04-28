<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Dashboardmenus.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Plugin_Dashboardmenus {

    public function onMenuInitialize_SiteforumDashboardSignature($row) {

        return array(
            'label' => $row->label,
            'route' => 'siteforum_specific',
            'action' => 'signature',
        );
    }

    public function onMenuInitialize_SiteforumDashboardMyTopics($row) {
        return array(
            'label' => $row->label,
            'route' => 'siteforum_specific',
            'class' => 'ajax_dashboard_enabled',
            'action' => 'my-topics',
        );
    }

    public function onMenuInitialize_SiteforumDashboardMyPosts($row) {
        return array(
            'label' => $row->label,
            'route' => 'siteforum_specific',
            'class' => 'ajax_dashboard_enabled',
            'action' => 'my-posts',
        );
    }

    public function onMenuInitialize_SiteforumDashboardMySubscriptions($row) {
        return array(
            'label' => $row->label,
            'route' => 'siteforum_specific',
            'class' => 'ajax_dashboard_enabled',
            'action' => 'my-subscriptions',
        );
    }
    
// Sticky Topic Work
    public function onMenuInitialize_SiteforumDashboardBookmarkedTopics($row) {
        return array(
            'label' => $row->label,
            'route' => 'siteforum_specific',
            'class' => 'ajax_dashboard_enabled',
            'action' => 'bookmarked-topics',
        );
    }

// Topic I Viewed    
//    public function onMenuInitialize_SiteforumDashboardViewedTopics($row) {
//        return array(
//            'label' => $row->label,
//            'route' => 'siteforum_specific',
//            'class' => 'ajax_dashboard_enabled',
//            'action' => 'viewed-topics',
//        );
//    }

    public function onMenuInitialize_SiteforumDashboardLikedTopics($row) {
        return array(
            'label' => $row->label,
            'route' => 'siteforum_specific',
            'class' => 'ajax_dashboard_enabled',
            'action' => 'liked-topics',
        );
    }

}

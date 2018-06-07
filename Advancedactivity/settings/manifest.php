<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: manifest.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
return array(
    'package' =>
    array(
        'type' => 'module',
        'name' => 'advancedactivity',
        'version' => '4.10.0p2',
        'path' => 'application/modules/Advancedactivity',
        'title' => 'Advanced Activity Feeds / Wall Plugin',
        'description' => 'Advanced Activity Feeds / Wall Plugin',
        'author' => '<a href="http://www.socialengineaddons.com" style="text-decoration:underline;" target="_blank">SocialEngineAddOns</a>',
        'callback' => array(
            'path' => 'application/modules/Advancedactivity/settings/install.php',
            'class' => 'Advancedactivity_Installer',
            'priority' => 1920,
        ),
        'actions' =>
        array(
            0 => 'install',
            1 => 'upgrade',
            2 => 'refresh',
            3 => 'enable',
            4 => 'disable',
        ),
        'directories' =>
        array(
            0 => 'application/modules/Advancedactivity',
            1 => 'application/modules/Sitehashtag',
            2 => 'application/modules/Sitereaction',
            3 => 'application/modules/Sitetagcheckin',
        ),
        'files' =>
        array(
            'application/languages/en/advancedactivity.csv',
            'application/libraries/Engine/Loader.php',
            'application/languages/en/sitehashtag.csv',
            'application/languages/en/sitereaction.csv',
            'application/languages/en/sitetagcheckin.csv',
        ),
    ),
    // Compose -------------------------------------------------------------------
    'compose' => array(
        array('_composeFacebook.tpl', 'advancedactivity'),
        array('_composeTwitter.tpl', 'advancedactivity'),
    //   array('_composeSocialengine.tpl', 'advancedactivity'),
    ),
    'composer' => array(
        'advanced_facebook' => array(
            'script' => array('_composeFacebook.tpl', 'advancedactivity'),
        ),
        'advanced_twitter' => array(
            'script' => array('_composeTwitter.tpl', 'advancedactivity'),
        ),
        'advanced_linkedin' => array(
            'script' => array('_composeLinkedin.tpl', 'advancedactivity'),
        ),
//        'advanced_socialengine' => array(
//            'script' => array('_composeSocialengine.tpl', 'advancedactivity'),
//        ),
        'tag' => array(
            'script' => array('_composeTag.tpl', 'advancedactivity'),
            'plugin' => 'Advancedactivity_Plugin_Composer_Tag',
        ),
        'feeling' => array(
            'script' => array('_composeFeeling.tpl', 'advancedactivity'),
            'plugin' => 'Advancedactivity_Plugin_Composer_Feeling',
        ),
        'sell' => array(
            'script' => array('_composeSell.tpl', 'advancedactivity'),
            'plugin' => 'Advancedactivity_Plugin_Composer_Sell',
        ),
        'banner' => array(
            'script' => array('_composeBanner.tpl', 'advancedactivity'),
            'auth' => array('advancedactivity_feed', 'aaf_feed_banner_enable'),
            'allowEdit' => 1,
            'plugin' => 'Advancedactivity_Plugin_Composer_Banner',
        ),
        'feed-tags' => array(
            'script' => array('_composeFeedTags.tpl', 'advancedactivity'),
        ),
    ),
    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'addActivity',
            'resource' => 'Advancedactivity_Plugin_Core',
        ),
        array(
            'event' => 'getActivity',
            'resource' => 'Advancedactivity_Plugin_Core',
        ),
        array(
            'event' => 'onItemDeleteBefore',
            'resource' => 'Advancedactivity_Plugin_Core',
        ),
        array(
            'event' => 'onUserCreateAfter',
            'resource' => 'Advancedactivity_Plugin_Core',
        ),
        array(
            'event' => 'onAlbumPhotoUpdateAfter',
            'resource' => 'Advancedactivity_Plugin_Core',
        ),
    ),
    // Items ---------------------------------------------------------------------
    'items' => array(
        'advancedactivity_content',
        'advancedactivity_customtype',
        'advancedactivity_list',
        'advancedactivity_report',
        'advancedactivity_list_item',
        'advancedactivity_customblock',
        'advancedactivity_feelingtype',
        'advancedactivity_feeling',
        'advancedactivity_link',
        'advancedactivity_greeting',
        'advancedactivity_pinsetting',
        'advancedactivity_word',
        'advancedactivity_sell',
        'advancedactivity_banner',
        'advancedactivity_story',
        'Advancedactivity_Viewer'
    ),
    // Routes --------------------------------------------------------------------
    'routes' => array(
        'advancedactivity_extended' => array(
            'route' => 'advancedactivitys/:controller/:action/*',
            'defaults' => array(
                'module' => 'advancedactivity',
                'controller' => 'index',
                'action' => 'index',
            ),
            'reqs' => array(
                'controller' => '\D+',
                'action' => '\D+',
            )
        ),
    )
);
?>

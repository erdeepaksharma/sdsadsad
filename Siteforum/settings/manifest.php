<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: manifest.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
return array(
    // Package -------------------------------------------------------------------
    'package' => array(
        'type' => 'module',
        'name' => 'siteforum',
        'version' => '4.10.2',
        'path' => 'application/modules/Siteforum',
        'title' => 'Advanced Forums',
        'description' => 'Advanced Forums',
        'author' => '<a href="http://www.socialengineaddons.com" style="text-decoration:underline;" target="_blank">SocialEngineAddOns</a>',
        'actions' => array(
            'install',
            'upgrade',
            'refresh',
            'enable',
            'disable',
        ),
        'callback' => array(
            'path' => 'application/modules/Siteforum/settings/install.php',
            'class' => 'Siteforum_Installer',
            'priority' => 1700,
        ),
        'directories' => array(
            'application/modules/Siteforum',
        ),
        'files' => array(
            'application/languages/en/siteforum.csv',
        ),
    ),
    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'onStatistics',
            'resource' => 'Siteforum_Plugin_Core'
        ),
        array(
            'event' => 'onUserDeleteAfter',
            'resource' => 'Siteforum_Plugin_Core'
        ),
        array(
            'event' => 'addActivity',
            'resource' => 'Siteforum_Plugin_Core'
        ),
        array(
            'event' => 'getActivity',
            'resource' => 'Siteforum_Plugin_Core',
        ),
    ),
    // Items ---------------------------------------------------------------------
    'items' => array(
        'forum',
        'forum_forum',
        'forum_category',
        'forum_container',
        'forum_post',
        'forum_signature',
        'forum_topic',
        'forum_list',
        'forum_list_item',
    ),
    // Routes --------------------------------------------------------------------
    'routes' => array(
        'siteforum_general' => array(
            'route' => 'forums/:action/*',
            'defaults' => array(
                'module' => 'siteforum',
                'controller' => 'index',
                'action' => 'index'
            ),
            'reqs' => array(
                'action' => '(index|upload-photo|rate|subcat|search|tags-cloud)',
            ),
        ),
        'siteforum_category' => array(
            'route' => 'forums/category/:category_id',
            'defaults' => array(
                'module' => 'siteforum',
                'controller' => 'index',
                'action' => 'index',
            ),
            'reqs' => array(
                'category_id' => '\d+',
            ),
        ),
        'siteforum_subcategory' => array(
            'route' => 'forums/category/:category_id/subcategory/:subcategory_id/',
            'defaults' => array(
                'module' => 'siteforum',
                'controller' => 'index',
                'action' => 'index',
            ),
            'reqs' => array(
                'category_id' => '\d+',
                'subcategory_id' => '\d+'
            ),
        ),
        'siteforum_forum' => array(
            'route' => 'forums/:forum_id/:slug/:action/*',
            'defaults' => array(
                'module' => 'siteforum',
                'controller' => 'forum',
                'action' => 'view',
                'slug' => '-',
            ),
            'reqs' => array(
                'action' => '(create|edit|delete|view|topic-create)',
                'slug' => '[\w-]+',
                'forum_id' => '\d+',
            ),

        ),
        'siteforum_topic' => array(
            'route' => 'forums/topic/:topic_id/:slug/:action/*',
            'defaults' => array(
                'module' => 'siteforum',
                'controller' => 'topic',
                'action' => 'view',
                'slug' => '-',
            ),
            'reqs' => array(
                'action' => '(edit|delete|close|rename|move|sticky|view|watch|post-create)',
                'slug' => '[\w-]+',
            ),
        ),
        'siteforum_post' => array(
            'route' => 'forums/post/:post_id/:action/*',
            'defaults' => array(
                'module' => 'siteforum',
                'controller' => 'post',
                'action' => 'view',
            ),
            'reqs' => array(
                'action' => '(edit|delete)',
            ),
        ),
        'siteforum_reputation' => array(
            'route' => 'forums/post/reputation/:user_id/:post_id/*',
            'defaults' => array(
                'module' => 'siteforum',
                'controller' => 'post',
                'action' => 'reputation',
            ),
            'reqs' => array(
                'action' => '(reputation)',
            ),
        ),
        'siteforum_specific' => array(
            'route' => 'forum/:controller/:action/*',
            'defaults' => array(
                'module' => 'siteforum',
                'controller' => 'dashboard',
                'action' => 'index'
            ),
        ),
    )
);
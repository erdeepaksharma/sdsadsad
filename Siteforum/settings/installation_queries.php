<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: installation_queries.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
$db->query("CREATE TABLE IF NOT EXISTS `engine4_forum_categories` (
                  `category_id` int(11) unsigned NOT NULL auto_increment,
                  `title` varchar(64) NOT NULL,
                  `description` varchar(255) NOT NULL,
                  `creation_date` datetime NOT NULL,
                  `modified_date` datetime NOT NULL,
                  `order` smallint(6) NOT NULL default '0',
                  `forum_count` int(11) unsigned NOT NULL default '0',
                  PRIMARY KEY  (`category_id`),
                  KEY `order` (`order`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;");


$db->query("INSERT IGNORE INTO `engine4_forum_categories` (`category_id`, `title`, `description`, `creation_date`, `modified_date`, `order`, `forum_count`) VALUES (1, 'General', '', NOW(), NOW(), 1, 3), (2, 'Off-Topic', '', NOW(), NOW(), 2, 2);");

$db->query("CREATE TABLE IF NOT EXISTS `engine4_forum_forums` (
                  `forum_id` int(11) unsigned NOT NULL auto_increment,
                  `category_id` int(11) unsigned NOT NULL,
                  `title` varchar(64) NOT NULL,
                  `description` varchar(255) NOT NULL,
                  `creation_date` datetime NOT NULL,
                  `modified_date` datetime NOT NULL,
                  `order` smallint(6) NOT NULL default '999',
                  `file_id` int(11) unsigned NOT NULL default '0',
                  `view_count` int(11) unsigned NOT NULL default '0',
                  `topic_count` int(11) unsigned NOT NULL default '0',
                  `post_count` int(11) unsigned NOT NULL default '0',
                  `lastpost_id` int(11) unsigned NOT NULL default '0',
                  `lastposter_id` int(11) unsigned NOT NULL default '0',
                  PRIMARY KEY  (`forum_id`),
                  KEY `category_id` (`category_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;");


$db->query("INSERT IGNORE INTO `engine4_forum_forums` (`forum_id`, `category_id`, `title`, `description`, `creation_date`, `modified_date`, `order`, `topic_count`, `post_count`, `lastpost_id`) VALUES
                (1, 1, 'News and Announcements', 'Contains topics related to ongoing issues / changes happening around.', '2010-02-01 14:59:01', '2010-02-01 14:59:01', 1, 1, 1, 1),
                (2, 1, 'Support', 'Here users can raise questions against the issues they are facing.', '2010-02-01 15:09:01', '2010-02-01 17:59:01', 2, 0, 0, 0),
                (3, 1, 'Suggestions', 'Users can ask help / suggestions from other users.', '2010-02-01 15:09:01', '2010-02-01 17:59:01', 3, 1, 1, 2),
                (4, 2, 'Off-Topic Discussions', ' Users can ask any question, it can be about anything which comes to their mind.', '2010-02-01 15:09:01', '2010-02-01 17:59:01', 1, 1, 1, 3),
                (5, 2, 'Introduce Yourself', 'Users can find / make new friends.', '2010-02-01 15:09:01', '2010-02-01 17:59:01', 2, 0, 0, 0);");

$db->query("CREATE TABLE IF NOT EXISTS `engine4_forum_listitems` (
                  `listitem_id` int(11) unsigned NOT NULL auto_increment,
                  `list_id` int(11) unsigned NOT NULL,
                  `child_id` int(11) unsigned NOT NULL,
                  PRIMARY KEY  (`listitem_id`),
                  KEY `list_id` (`list_id`),
                  KEY `child_id` (`child_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;");

$db->query("INSERT IGNORE INTO `engine4_forum_listitems` (`listitem_id`, `list_id`, `child_id`) VALUES
                (1, 1, 1),
                (2, 2, 1),
                (3, 3, 1),
                (4, 4, 1),
                (5, 5, 1);");


$db->query("CREATE TABLE IF NOT EXISTS `engine4_forum_lists` (
                  `list_id` int(11) unsigned NOT NULL auto_increment,
                  `owner_id` int(11) unsigned NOT NULL,
                  `child_count` int(11) unsigned NOT NULL default '0',
                  PRIMARY KEY  (`list_id`),
                  KEY `owner_id` (`owner_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;");


$db->query("INSERT IGNORE INTO `engine4_forum_lists` (`list_id`, `owner_id`, `child_count`) VALUES
                (1, 1, 1),
                (2, 2, 1),
                (3, 3, 1),
                (4, 4, 1),
                (5, 5, 1);");


$db->query("CREATE TABLE IF NOT EXISTS `engine4_forum_membership` (
                  `resource_id` int(11) unsigned NOT NULL,
                  `user_id` int(11) unsigned NOT NULL,
                  `active` tinyint(1) NOT NULL default '0',
                  `resource_approved` tinyint(1) NOT NULL default '0',
                  `moderator` tinyint(1) NOT NULL default '0',
                  PRIMARY KEY(`resource_id`, `user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;");


$db->query("CREATE TABLE IF NOT EXISTS `engine4_forum_posts` (
  `post_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) unsigned NOT NULL,
  `forum_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `file_id` int(11) unsigned NOT NULL DEFAULT '0',
  `edit_id` int(11) unsigned NOT NULL DEFAULT '0',
  `thanks_count` int(11) unsigned NOT NULL DEFAULT '0',
  `like_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");


$db->query("CREATE TABLE IF NOT EXISTS `engine4_forum_signatures` (
                  `signature_id` int(11) unsigned NOT NULL auto_increment,
                  `user_id` int(11) unsigned NOT NULL,
                  `body` text NOT NULL,
                  `creation_date` datetime NOT NULL,
                  `modified_date` datetime NOT NULL,
                  `post_count` int(11) unsigned NOT NULL default '0',
                  PRIMARY KEY  (`signature_id`),
                  UNIQUE KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;");


$db->query("CREATE TABLE IF NOT EXISTS `engine4_forum_topics` (
  `topic_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `sticky` tinyint(4) NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `post_count` int(11) unsigned NOT NULL DEFAULT '0',
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `lastpost_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lastposter_id` int(11) unsigned NOT NULL DEFAULT '0',
  `rating` float NOT NULL,
  `like_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");


$db->query("CREATE TABLE IF NOT EXISTS `engine4_forum_topicwatches` (
                  `resource_id` int(10) unsigned NOT NULL,
                  `topic_id` int(10) unsigned NOT NULL,
                  `user_id` int(10) unsigned NOT NULL,
                  `watch` tinyint(1) unsigned NOT NULL default '1',
                  PRIMARY KEY  (`resource_id`,`topic_id`,`user_id`),
                  KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;");


$db->query("CREATE TABLE IF NOT EXISTS `engine4_forum_topicviews` (
                  `user_id` int(11) unsigned NOT NULL,
                  `topic_id` int(11) unsigned NOT NULL,
                  `last_view_date` datetime NOT NULL,
                  PRIMARY KEY(`user_id`, `topic_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");


$db->query("INSERT IGNORE INTO `engine4_authorization_allow` (`resource_type`, `resource_id`, `action`, `role`, `role_id`, `value`, `params`) VALUES
                ('forum', 1, 'view', 'everyone', 0, 1, NULL),
                ('forum', 1, 'topic.create', 'registered', 0, 1, NULL),
                ('forum', 1, 'post.create', 'registered', 0, 1, NULL),
                ('forum', 1, 'topic.edit', 'forum_list', 1, 1, NULL),
                ('forum', 1, 'topic.delete', 'forum_list', 1, 1, NULL),

                ('forum', 2, 'view', 'everyone', 0, 1, NULL),
                ('forum', 2, 'topic.create', 'registered', 0, 1, NULL),
                ('forum', 2, 'post.create', 'registered', 0, 1, NULL),
                ('forum', 2, 'topic.edit', 'forum_list', 2, 1, NULL),
                ('forum', 2, 'topic.delete', 'forum_list', 2, 1, NULL),

                ('forum', 3, 'view', 'everyone', 0, 1, NULL),
                ('forum', 3, 'topic.create', 'registered', 0, 1, NULL),
                ('forum', 3, 'post.create', 'registered', 0, 1, NULL),
                ('forum', 3, 'topic.edit', 'forum_list', 3, 1, NULL),
                ('forum', 3, 'topic.delete', 'forum_list', 3, 1, NULL),

                ('forum', 4, 'view', 'everyone', 0, 1, NULL),
                ('forum', 4, 'topic.create', 'registered', 0, 1, NULL),
                ('forum', 4, 'post.create', 'registered', 0, 1, NULL),
                ('forum', 4, 'topic.edit', 'forum_list', 4, 1, NULL),
                ('forum', 4, 'topic.delete', 'forum_list', 4, 1, NULL),

                ('forum', 5, 'view', 'everyone', 0, 1, NULL),
                ('forum', 5, 'topic.create', 'registered', 0, 1, NULL),
                ('forum', 5, 'post.create', 'registered', 0, 1, NULL),
                ('forum', 5, 'topic.edit', 'forum_list', 5, 1, NULL),
                ('forum', 5, 'topic.delete', 'forum_list', 5, 1, NULL)
                ;");

$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'create' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'edit' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'delete' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'view' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'comment' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'topic.create' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'topic.edit' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'topic.delete' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'post.create' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'post.edit' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'post.delete' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");

$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_topic' as `type`,
                    'create' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_topic' as `type`,
                    'edit' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_topic' as `type`,
                    'delete' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_topic' as `type`,
                    'move' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");

$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_post' as `type`,
                    'create' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_post' as `type`,
                    'edit' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_post' as `type`,
                    'delete' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");

$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'view' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'comment' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'topic.create' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'topic.edit' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'topic.delete' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'post.create' as `name`,
                    2 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'post.edit' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'post.delete' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");

$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_topic' as `type`,
                    'create' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_topic' as `type`,
                    'edit' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_topic' as `type`,
                    'delete' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");

$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_post' as `type`,
                    'create' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_post' as `type`,
                    'edit' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");
$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum_post' as `type`,
                    'delete' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('user');");

$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'view' as `name`,
                    1 as `value`,
                    NULL as `params`
                  FROM `engine4_authorization_levels` WHERE `type` IN('public');");

$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
                  SELECT
                    level_id as `level_id`,
                    'forum' as `type`,
                    'commentHtml' as `name`,
                    3 as `value`,
                    'blockquote, strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr, iframe' as `params`
                  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
            ");

$db->query("INSERT IGNORE INTO `engine4_forum_topics` (`topic_id`, `forum_id`, `user_id`, `title`, `description`, `creation_date`, `modified_date`, `sticky`, `closed`, `post_count`, `view_count`, `lastpost_id`, `lastposter_id`, `rating`, `like_count`) VALUES 


(1, 1, 1, 'What is Forum?', 'It is a great platform to share your views with people across the world, and provide a good reason to your users to get interacts, to respond or to react. You can start a discussion, ask a question or post a topic, where people with similar interests / groups can get together and can discuss, post reply and encourage others to participate into the discussions.', NOW(), NOW(), 0, 0, 1, 0, 1, 1, 0, 0),

(2, 3, 1, 'How can I groom my personality?', '<p>1. Developing Good Personality Characteristics</p><br><p>2. Remain happy and lighthearted. Try to see the joy in the world. Laugh with others, but not at them. Everyone appreciates someone who is jolly and jovial. Smiling and laughing a lot is a huge part of having a good personality.</p><br><p>3. Try to stay calm in tense situations</p><br><p>4. Keep an open mind</p><br><p>5. Develop modesty</p>', NOW(), NOW(), 0, 0, 1, 0, 2, 1, 0, 0),

(3, 4, 1, 'Tips for the great health and fitness', '<p>1. Resist that chocolate cake siren, and instead enjoy a sliced apple with a tablespoon of nut butter (like peanut or almond) or fresh fig halves spread with ricotta. Then sleep sweet, knowing you\'re still on the right, healthy track.<br><br>2. A workout buddy is hugely helpful for keeping motivated, but it\'s important to find someone who will inspire<br><br>3. Next grocery store run, be sure to place Newgent\'s top three diet-friendly items in your cart: balsamic vinegar (it adds a pop of low-cal flavor to veggies and salads), in-shell nuts (their protein and fiber keep you satiated), and fat-free plain yogurt (a creamy, comforting source of protein). Plus, Greek yogurt also works wonders as a natural low-calorie base for dressings and dips&mdash;or as a tangier alternative to sour cream</p>', NOW(), NOW(), 0, 0, 1, 0, 3, 1, 0, 0);");

$db->query("INSERT IGNORE INTO `engine4_forum_posts` (`post_id`, `topic_id`, `forum_id`, `user_id`, `body`, `creation_date`, `modified_date`, `file_id`, `edit_id`, `thanks_count`, `like_count`) VALUES 

(1, 1, 1, 1, 'It is a great platform to share your views with people across the world, and provide a good reason to your users to get interacts, to respond or to react. You can start a discussion, ask a question or post a topic, where people with similar interests / groups can get together and can discuss, post reply and encourage others to participate into the discussions.', NOW(), NOW(), 0, 0, 0, 0),

(2, 2, 3, 1, '<p>1. Developing Good Personality Characteristics</p><br><p>2. Remain happy and lighthearted. Try to see the joy in the world. Laugh with others, but not at them. Everyone appreciates someone who is jolly and jovial. Smiling and laughing a lot is a huge part of having a good personality.</p><br><p>3. Try to stay calm in tense situations</p><br><p>4. Keep an open mind</p><br><p>5. Develop modesty</p>', NOW(), NOW(), 0, 0, 0, 0),

(3, 3, 4, 1, '<p>1. Resist that chocolate cake siren, and instead enjoy a sliced apple with a tablespoon of nut butter (like peanut or almond) or fresh fig halves spread with ricotta. Then sleep sweet, knowing you\'re still on the right, healthy track.<br><br>2. A workout buddy is hugely helpful for keeping motivated, but it\'s important to find someone who will inspire<br><br>3. Next grocery store run, be sure to place Newgent\'s top three diet-friendly items in your cart: balsamic vinegar (it adds a pop of low-cal flavor to veggies and salads), in-shell nuts (their protein and fiber keep you satiated), and fat-free plain yogurt (a creamy, comforting source of protein). Plus, Greek yogurt also works wonders as a natural low-calorie base for dressings and dips&mdash;or as a tangier alternative to sour cream</p>', NOW(), NOW(), 0, 0, 0, 0); ");


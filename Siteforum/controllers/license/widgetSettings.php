<?php
$db = Engine_Db_Table::getDefaultAdapter();

$db->query('INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
("notify_siteforum_topic_reply", "siteforum", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[message]"),
("notify_siteforum_topic_response", "siteforum", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]"),
("notify_siteforum_promote", "siteforum", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]");');

$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
("core_main_siteforum", "siteforum", "Forum", "", \'{"route":"siteforum_general","icon":"fa-comments"}\', "core_main", "", 5),
("siteforum_admin_main_level", "siteforum", "Member Level Settings", "", \'{"route":"admin_default","module":"siteforum","controller":"level"}\', "siteforum_admin_main", "", 10),
("siteforum_admin_main_manage", "siteforum", "Manage Forums", "", \'{"route":"admin_default","module":"siteforum","controller":"manage"}\', "siteforum_admin_main", "", 15),
("authorization_admin_level_siteforum", "siteforum", "Forums", "", \'{"route":"admin_default","module":"siteforum","controller":"level","action":"index"}\', "authorization_admin_level", "", 999);');

$db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
("siteforum_promote", "siteforum", \'{item:$subject} has been made a moderator for the forum {item:$object}\', 1, 3, 1, 1, 1, 1),
("siteforum_topic_create", "siteforum", \'{item:$subject} posted a {item:$object:topic} in the forum {itemParent:$object:siteforum}: {body:$body}\', 1, 5, 1, 1, 1, 1),
("siteforum_topic_reply", "siteforum", \'{item:$subject} replied to a {item:$object:topic} in the forum {itemParent:$object:siteforum}: {body:$body}\', 1, 5, 1, 1, 1, 1);');

$db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
("siteforum_promote", "siteforum", \'You were promoted to moderator in the forum {item:$object}.\', 0, ""),
("siteforum_topic_response", "siteforum", \'{item:$subject} has {item:$object:posted:$url} on a {itemParent:$object::forum topic} you created.\', 0, ""),
("siteforum_topic_reply", "siteforum", \'{item:$subject} has {item:$object:posted:$url} on a {itemParent:$object::forum topic} posted on.\', 0, ""),
("siteforum_thanks", "siteforum", \'{item:$subject} thanked on a {item:$object:forum post:$url} you posted.\', 0, ""),
("siteforum_reputation", "siteforum", \'{item:$subject} added a reputation on {item:$object:forum post:$url} you posted.\', 0, "");');

$db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES ("comment_forum_post", "siteforum", \'{item:$subject} commented on {item:$owner}\'\'s {item:$object:forum post}: {body:$body}\', "1", "1", "1", "1", "1", "1");');

$db->query('INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
("siteforum_dashboard_content", "standard", "Advanced Forums - Dashboard Navigation");');

$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES 
("siteforum_dashboard_signature", "siteforum", "Edit Signature", "Siteforum_Plugin_Dashboardmenus", \'{"route":"siteforum_specific", "action":"signature"}\', "siteforum_dashboard_content", NULL, "1", "0", "7"),
("siteforum_dashboard_mytopics", "siteforum", "My Topics", "Siteforum_Plugin_Dashboardmenus", \'{"route":"siteforum_specific", "action":"my-topics"}\', "siteforum_dashboard_content", NULL, "1", "0", "1"),
("siteforum_dashboard_myposts", "siteforum", "My Posts", "Siteforum_Plugin_Dashboardmenus", \'{"route":"siteforum_specific", "action":"my-posts"}\', "siteforum_dashboard_content", NULL, "1", "0", "2"),
("siteforum_dashboard_mysubscriptions", "siteforum", "My Subscriptions", "Siteforum_Plugin_Dashboardmenus", \'{"route":"siteforum_specific", "action":"my-subscriptions"}\', "siteforum_dashboard_content", NULL, "1", "0", "3"),
("siteforum_dashboard_bookmarkedtopics", "siteforum", "Sticky Topics", "Siteforum_Plugin_Dashboardmenus", \'{"route":"siteforum_specific", "action":"bookmarked-topics"}\', "siteforum_dashboard_content", NULL, "1", "0", "4"),
("siteforum_dashboard_likedtopics", "siteforum", "Topics I Liked", "Siteforum_Plugin_Dashboardmenus", \'{"route":"siteforum_specific", "action":"liked-topics"}\', "siteforum_dashboard_content", NULL, "1", "0", "5");');
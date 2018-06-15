
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: my.sql 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */

--
-- Dumping data for table `engine4_core_menus`
--

INSERT IGNORE INTO `engine4_sitemobile_menus` ( `name`, `type`, `title`, `order`) VALUES
( 'sitereview_main_common', 'standard', 'Multiple Listing Types - Common Main Navigation Menu', 999);

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_sitemobile_menuitems` ( `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `custom`, `order`,`enable_mobile`,`enable_tablet`) VALUES
( 'sitereview_main_common_categories', 'sitereview', 'Categories', 'Sitereview_Plugin_Menus::canViewCategories', '{"route":"sitereview_review_categories","action":"categories"}', 'sitereview_main_common', '',0, 1,1, 1),
( 'sitereview_main_common_reviews', 'sitereview', 'Browse Reviews', 'Sitereview_Plugin_Menus::canViewBrosweReview', '{"route":"sitereview_review_browse", "action":"browse"}', 'sitereview_main_common', '',0,  2, 1, 1),
( 'sitereview_main_common_wishlists', 'sitereview', 'Wishlists', 'Sitereview_Plugin_Menus::canViewWishlist', '{"route":"sitereview_wishlist_general","action":"browse"}', 'sitereview_main_common', '',0, 3, 1, 1);



INSERT IGNORE INTO `engine4_sitemobile_menuitems` ( `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `custom`, `order`,`enable_mobile`,`enable_tablet`) VALUES
( 'user_profile_wishlist', 'sitereview', 'Wishlists', 'Sitereview_Plugin_Menus::userProfileWishlist', '', 'user_profile', '',  0, 999, 1, 1);


INSERT IGNORE INTO `engine4_sitemobile_menus` ( `name`, `type`, `title`, `order`) VALUES
( 'sitereview_wishlist_gutter', 'standard', 'Review: Wishlist Profile Options Menu', 999);

INSERT IGNORE INTO `engine4_sitemobile_menuitems` ( `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `custom`, `order`, `enable_mobile`, `enable_tablet`) VALUES
( 'sitereview_wishlist_gutter_create', 'sitereview', 'Create New Wishlist', 'Sitereview_Plugin_Menus', '', 'sitereview_wishlist_gutter', '', 0, 1, 1, 1),
( 'sitereview_wishlist_gutter_edit', 'sitereview', 'Edit', 'Sitereview_Plugin_Menus', '', 'sitereview_wishlist_gutter', '', 0, 2, 1, 1),
( 'sitereview_wishlist_gutter_delete', 'sitereview', 'Delete', 'Sitereview_Plugin_Menus', '', 'sitereview_wishlist_gutter', '', 0, 3, 1, 1),
( 'sitereview_wishlist_gutter_share', 'sitereview', 'Share', 'Sitereview_Plugin_Menus', '', 'sitereview_wishlist_gutter', '', 0, 4, 1, 1),
( 'sitereview_wishlist_gutter_tfriend', 'sitereview', 'Tell a Friend', 'Sitereview_Plugin_Menus', '', 'sitereview_wishlist_gutter', '', 0, 5, 1, 1),
( 'sitereview_wishlist_gutter_report', 'sitereview', 'Report','Sitereview_Plugin_Menus', '', 'sitereview_wishlist_gutter', '', 0, 6, 1, 1);


INSERT IGNORE INTO `engine4_sitemobile_navigation` 
(`name`, `menu`, `subject_type`) VALUES 
('sitereview_wishlist_profile', 'sitereview_wishlist_gutter', 'sitereview_wishlist');


INSERT IGNORE INTO `engine4_sitemobile_searchform` (`name`, `class`, `search_filed_name`, `params`, `script_render_file`, `action`) VALUES
('sitereview_review_browse', 'Sitereview_Form_Review_Search', 'search', '{"type":"sitereview_review"}', '', ''),
('sitereview_wishlist_browse', 'Sitereview_Form_Wishlist_Search', 'search', '', 'application/modules/Sitereview/views/sitemobile/scripts/searchform/wishlistSearch.tpl', ''),

('sitereview_index_manage', 'Sitereview_Form_Search', 'search', '', '', ''),
('sitereview_index_home', 'Sitereview_Form_Search', 'search', '', '', ''),
('sitereview_index_index', 'Sitereview_Form_Search', 'search', '', '', '');

INSERT IGNORE INTO `engine4_sitemobile_searchform` (`name` ,`class` ,`search_filed_name`,`params` ,`script_render_file` ,`action`)VALUES ('sitereview_index_top-rated', 'Sitereview_Form_Search', 'search', '', '', '');

INSERT IGNORE INTO `engine4_sitemobile_navigation` 
(`name`, `menu`, `subject_type`) VALUES
('sitereview_topic_view', 'sitereview_topic', 'sitereview_topic');


INSERT IGNORE INTO `engine4_sitemobile_menuitems` ( `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `custom`, `order`, `enable_mobile`, `enable_tablet`) VALUES 
('Sitereview_topic_watch', 'sitereview', 'Watch Topic', 'Sitereview_Plugin_Menus', '', 'sitereview_topic', NULL, '0', '1', '1', '1'), 
('Sitereview_topic_sticky', 'sitereview', 'Make Sticky', 'Sitereview_Plugin_Menus', '', 'sitereview_topic', NULL, '0', '2', '1', '1'),
('Sitereview_topic_open', 'sitereview', 'Open', 'Sitereview_Plugin_Menus', '', 'sitereview_topic', NULL, '0', '3', '1', '1'),
('Sitereview_topic_rename', 'sitereview', 'Rename', 'Sitereview_Plugin_Menus', '', 'sitereview_topic', NULL, '0', '4', '1', '1'),
('Sitereview_topic_delete', 'sitereview', 'Delete', 'Sitereview_Plugin_Menus', '', 'sitereview_topic', NULL, '0', '5', '1', '1');

INSERT IGNORE INTO `engine4_sitemobile_menus` (`name`, `type`, `title`, `order`) VALUES 
('sitereview_topic', 'standard', 'Review Topic Options Menu', '999');

INSERT IGNORE INTO `engine4_sitemobile_navigation` 
(`name`, `menu`, `subject_type`) VALUES
('sitereview_photo_view', 'sitereview_photo', 'sitereview_photo');


INSERT IGNORE INTO `engine4_sitemobile_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `custom`, `order`, `enable_mobile`, `enable_tablet`) VALUES
('sitereview_video_add', 'sitereview', 'Add Video', 'Sitereview_Plugin_Menus', '', 'sitereview_video', NULL, '0', '1', '1', '1'),
('sitereview_video_edit', 'sitereview', 'Edit Video', 'Sitereview_Plugin_Menus', '', 'sitereview_video', NULL, '0', '2', '1', '1'),
('sitereview_video_delete', 'sitereview', 'Delete Video', 'Sitereview_Plugin_Menus', '', 'sitereview_video', NULL, '0', '3', '1', '1');

INSERT IGNORE INTO `engine4_sitemobile_menus` (`name`, `type`, `title`, `order`) VALUES 
('sitereview_video', 'standard', 'Review Video Options Menu', '999');
INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES ('sitereview_video_view', 'sitereview_video', 'sitereview_video');


INSERT IGNORE INTO `engine4_sitemobile_navigation` 
(`name`, `menu`, `subject_type`) VALUES
('sitereview_review_view', 'sitereview_profile', 'sitereview_review');

INSERT IGNORE INTO `engine4_sitemobile_menus` (`id`, `name`, `type`, `title`, `order`) VALUES (NULL, 'sitereview_profile', 'standard', 'Multiple Listing Types - Review Profile Options Menu', '999');

--
-- Dumping data for table `engine4_sitemobile_menuitems`
--

INSERT IGNORE INTO `engine4_sitemobile_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `custom`, `order`, `enable_mobile`, `enable_tablet`) VALUES 
('sitereview_review_update', 'sitereview', 'Update your Review', 'Sitereview_Plugin_Menus', '', 'sitereview_profile', NULL, '0', '1', '1', '1'),
('sitereview_review_create', 'sitereview', 'Write a Review', 'Sitereview_Plugin_Menus', '', 'sitereview_profile', NULL, '0', '1', '1', '1'),
('sitereview_review_share', 'sitereview', 'Share Review', 'Sitereview_Plugin_Menus', '', 'sitereview_profile', NULL, '0', '2', '1', '1'),
('sitereview_review_email', 'sitereview', 'Email Review', 'Sitereview_Plugin_Menus', '', 'sitereview_profile', NULL, '0', '3', '1', '1'),
('sitereview_review_delete', 'sitereview', 'Delete Review', 'Sitereview_Plugin_Menus', '', 'sitereview_profile', NULL, '0', '4', '1', '1'),
('sitereview_review_report', 'sitereview', 'Report Review', 'Sitereview_Plugin_Menus', '', 'sitereview_profile', NULL, '0', '5', '1', '1');


INSERT IGNORE INTO `engine4_sitemobile_menus` (`id`, `name`, `type`, `title`, `order`) VALUES (NULL, 'sitereview_photo', 'standard', 'Review Photo View Options Menu', '999');



INSERT IGNORE INTO `engine4_sitemobile_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `custom`, `order`, `enable_mobile`, `enable_tablet`) VALUES
 ('sitereview_photo_edit', 'sitereview', 'Edit', 'Sitereview_Plugin_Menus', '', 'sitereview_photo', NULL, '0', '1', '1', '1'),
 ('sitereview_photo_delete', 'sitereview', 'Delete', 'Sitereview_Plugin_Menus', '', 'sitereview_photo', NULL, '0', '2', '1', '1'),
('sitereview_photo_share', 'sitereview', 'Share', 'Sitereview_Plugin_Menus', '', 'sitereview_photo', NULL, '0', '3', '1', '1'),
('sitereview_photo_report', 'sitereview', 'Report', 'Sitereview_Plugin_Menus', '', 'sitereview_photo', NULL, '0', '4', '1', '1'),
('sitereview_photo_profile', 'sitereview', 'Make Profile Photo', 'Sitereview_Plugin_Menus', '', 'sitereview_photo', NULL, '0', '5', '1', '1');

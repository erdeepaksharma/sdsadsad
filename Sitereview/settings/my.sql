
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
-- Dumping data for table `engine4_core_modules`
--

INSERT IGNORE INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('sitereview', 'Multiple Listing Types Plugin Core (Reviews & Ratings Plugin)', 'Multiple Listing Types Plugin Core (Reviews & Ratings Plugin)', '4.10.1p1', 1, 'extra');
-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES
("sitereview_main_common_categories", "sitereview", "Categories",  'Sitereview_Plugin_Menus::canViewCategories', '{"route":"sitereview_review_categories","action":"categories"}', "sitereview_main_common", "", 1, 0, 0),
("sitereview_main_common_reviews", "sitereview", "Browse Reviews", 'Sitereview_Plugin_Menus::canViewBrosweReview', '{"route":"sitereview_review_browse", "action":"browse"}', "sitereview_main_common", "", 1, 0, 1),
("sitereview_main_common_wishlists", "sitereview", "Wishlists", 'Sitereview_Plugin_Menus::canViewWishlist', '{"route":"sitereview_wishlist_general","action":"browse"}', "sitereview_main_common", "", 1, 0, 2),
("sitereview_main_common_editors", "sitereview", "Editors", 'Sitereview_Plugin_Menus::canViewEditors', '{"route":"sitereview_review_editor","action":"home"}', "sitereview_main_common", "", 1, 0, 3),
('user_profile_wishlist', 'sitereview', 'Wishlists', 'Sitereview_Plugin_Menus::userProfileWishlist', '', 'user_profile', '', '1', '0', 999);

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('SITEREVIEW_CHANGEOWNER_EMAIL', 'sitereview', '[host],[email],[list_title],[object_link],[listing_type],[list_title_with_link],[site_contact_us_link]'),
('SITEREVIEW_BECOMEOWNER_EMAIL', 'sitereview', '[host],[email],[list_title],[object_link],[listing_type],[list_title_with_link], [site_contact_us_link]');

--
-- Change the Commentable & Shareable values
--
UPDATE engine4_activity_actiontypes SET commentable=3,shareable=3 WHERE (type='comment_sitereview_listing' or type = 'comment_sitereview_photo' or type = 'comment_sitereview_review' or type = 'comment_sitereview_video') and module='sitereview';


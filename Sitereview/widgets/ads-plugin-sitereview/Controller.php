<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_AdsPluginSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    if (!empty($viewer_id)) {
      $level_id = $viewer->level_id;
    } else {
      $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
    }

    $pluginName = $this->_getParam('pluginName', '');

    if ($level_id != 1 || empty($pluginName)) {
      return $this->setNoRender();
    }

    $isEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($pluginName);

    switch ($pluginName) {
      case 'advancedslideshow':
        if ($isEnabled) {
          $this->view->message = sprintf('At this place we have configured an attractive slideshow in our template by using "%1$s". You can also create a slideshow for this place from the admin panel of this plugin.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-advanced-slideshow-plugin-multiple-slideshows">Advanced Slideshow Plugin - Multiple Slideshows Plugin</a>');
        } else {
          $this->view->message = sprintf('At this place we have configured an attractive slideshow in our template by using "%1$s". If you want to create a slideshow for this place, then you can purchase this plugin %2$s.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-advanced-slideshow-plugin-multiple-slideshows">Advanced Slideshow Plugin - Multiple Slideshows Plugin</a>', '<a target="blank" href="http://www.socialengineaddons.com/catalog/1/plugins">over here</a>');
        }
        break;
      case 'communityad':
        if ($isEnabled) {
          $this->view->message = sprintf('At this place we have configured an Ads Block in our template by using "%1$s". You can also configure an Ad Block for this place from the admin panel of this plugin.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-advertisements-community-ads-plugin">Advertisements / Community Ads Plugin</a>');
        } else {
          $this->view->message = sprintf('At this place we have configured an Ad Block in our template by using "%1$s". If you want to configure an Ad Block for this place, then you can purchase this plugin %2$s.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-advertisements-community-ads-plugin">Advertisements / Community Ads Plugin</a>', '<a target="blank" href="http://www.socialengineaddons.com/catalog/1/plugins">over here</a>');
        }
        break;
      case 'sitetagcheckin':
        if ($isEnabled) {
          $this->view->message = sprintf('At this place we have configured a Check-in button in our template to enable users to publish an update and add photo while checking in into any listing by using "%1$s". You can also configure a Check-in button for this place from the admin panel of this plugin.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-geo-location-geo-tagging-checkins-proximity-search-plugin">Geo-Location, Geo-Tagging, Check-Ins & Proximity Search Plugin</a>');
        } else {
          $this->view->message = sprintf('At this place we have configured a Check-in button in our template to enable users to publish an update and add photo while checking in into any listing by using "%1$s". If you want to configure a Check-in button for this place, then you can purchase this plugin %2$s.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-geo-location-geo-tagging-checkins-proximity-search-plugin">Geo-Location, Geo-Tagging, Check-Ins & Proximity Search Plugin</a>', '<a target="blank" href="http://www.socialengineaddons.com/catalog/1/plugins">over here</a>');
        }
        break;
      case 'sitepageintegration':
        if ($isEnabled) {
          $this->view->message = sprintf('We have configured our template to enable Page Admins to add / link / associate related Listings & Businesses to their Directory Items / Pages by using "%1$s". You can also configure this listing type from the admin panel of this plugin.', '<a target="blank" href="http://www.socialengineaddons.com/pageextensions/socialengine-directory-pages-multiple-listings-products-showcase">Directory / Pages - Multiple Listings and Products Showcase Extension</a>');
        } else {
          $this->view->message = sprintf('We have configured our template to enable Page Admins to add / link / associate related Listings & Businesses to their Directory Items / Pages by using "%1$s". If you want to configure this listing type, then you can purchase this plugin %2$s.', '<a target="blank" href="http://www.socialengineaddons.com/pageextensions/socialengine-directory-pages-multiple-listings-products-showcase">Directory / Pages - Multiple Listings and Products Showcase Extension</a>', '<a target="blank" href="http://www.socialengineaddons.com/catalog/1/plugins">over here</a>');
        }
        break;
      case 'sitefaq':
        if ($isEnabled) {
          $this->view->message = sprintf('We have created FAQs for Product listing type to provide our users some information about Products by using "%1$s". You can create FAQs for this listing type from the admin panel of this plugin.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-faqs-knowledgebase-tutorials-help-center-plugin">FAQs, Knowledgebase, Tutorials & Help Center Plugin</a>');
        } else {
          $this->view->message = sprintf('We have created FAQs for Product listing type to provide our users some information about Products by using "%1$s". If you want to create FAQs for this listing type, then you can purchase this plugin %2$s.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-faqs-knowledgebase-tutorials-help-center-plugin">FAQs, Knowledgebase, Tutorials & Help Center Plugin</a>', '<a target="blank" href="http://www.socialengineaddons.com/catalog/1/plugins">over here</a>');
        }
        break;
      case 'advancedactivity':
        if ($isEnabled) {
          $this->view->message = sprintf('We have provided the right set of features and configurations for activity feeds and listing profile walls by using "%1$s". You can also configure this listing type from the admin panel of this plugin.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-advanced-activity-feeds-wall-plugin">Advanced Activity Feeds / Wall Plugin</a>');
        } else {
          $this->view->message = sprintf('We have provided the right set of features and configurations for activity feeds and listing profile walls by using "%1$s". If you want to configure this listing type, then you can purchase this plugin %2$s.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-advanced-activity-feeds-wall-plugin">Advanced Activity Feeds / Wall Plugin</a>', '<a target="blank" href="http://www.socialengineaddons.com/catalog/1/plugins">over here</a>');
        }
        break;
      case 'suggestion':
        if ($isEnabled) {
          $this->view->message = sprintf('We have configured our demo website to enable users to recommend various listings to their friends by using "%1$s". You can also configure your site from the admin panel of this plugin.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-suggestions-recommendations-plugin">Suggestions / Recommendations Plugin</a>');
        } else {
          $this->view->message = sprintf('We have configured our demo website to enable users to recommend various listings to their friends by using "%1$s". If you want to configure your site, then you can purchase this plugin %2$s.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-suggestions-recommendations-plugin">Suggestions / Recommendations Plugin</a>', '<a target="blank" href="http://www.socialengineaddons.com/catalog/1/plugins">over here</a>');
        }
        break;
      case 'sitevideoview':
        if ($isEnabled) {
          $this->view->message = sprintf('We have configured our demo website to provide our users an attractive, engaging video viewing experience by using "%1$s". You can also configure this listing type from the admin panel of this plugin.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-video-lightbox-viewer-plugin">Video Lightbox Viewer Plugin</a>');
        } else {
          $this->view->message = sprintf('We have configured our demo website to provide our users an attractive, engaging video viewing experience by using "%1$s". If you want to configure this listing type, then you can purchase this plugin %2$s.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-video-lightbox-viewer-plugin">Video Lightbox Viewer Plugin</a>', '<a target="blank" href="http://www.socialengineaddons.com/catalog/1/plugins">over here</a>');
        }
        break;
      case 'facebookse':
        if ($isEnabled) {
          $this->view->message = sprintf('At this place in our template we have configured a widget for Facebook Tools to leverage the latest viral tools provided by Facebook for websites by using "%1$s". You can also configure a widget for Facebook Tools for this place from the admin panel of this plugin.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-advanced-facebook-integration-likes-social-plugins-and-open-graph">Advanced Facebook Integration / Likes, Social Plugins and Open Graph</a>');
        } else {
          $this->view->message = sprintf('At this place in our template we have configured a widget for Facebook Tools to leverage the latest viral tools provided by Facebook for websites by using "%1$s". If you want to configure a widget for Facebook Tools for this place, then you can purchase this plugin %2$s.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-advanced-facebook-integration-likes-social-plugins-and-open-graph">Advanced Facebook Integration / Likes, Social Plugins and Open Graph</a>', '<a target="blank" href="http://www.socialengineaddons.com/catalog/1/plugins">over here</a>');
        }
        break;
      case 'facebooksefeed':
        if ($isEnabled) {
          $this->view->message = sprintf('We have configured our template to enable users to publish activity Feed Stories on Facebook for their actions like creating a listing, etc by using "%1$s". You can also configure this listing type from the admin panel of this plugin.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-facebook-feed-stories-publisher">Facebook Feed Stories Publisher Plugin</a>');
        } else {
          $this->view->message = sprintf('We have configured our template to enable users to publish activity Feed Stories on Facebook for their actions like creating a listing, etc by using "%1$s". If you want to configure this listing type, then you can purchase this plugin %2$s.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-facebook-feed-stories-publisher">Facebook Feed Stories Publisher Plugin</a>', '<a target="blank" href="http://www.socialengineaddons.com/catalog/1/plugins">over here</a>');
        }
        break;
    }
  }

}

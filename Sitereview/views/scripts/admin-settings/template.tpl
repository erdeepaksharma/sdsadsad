<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: template.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<?php
$modules_array = array(
    'Advanced Activity Feeds / Wall Plugin' => 'http://www.socialengineaddons.com/socialengine-advanced-activity-feeds-wall-plugin',
    'Advertisements / Community Ads Plugin' => 'http://www.socialengineaddons.com/socialengine-advertisements-community-ads-plugin',
    'Advanced Facebook Integration / Likes, Social Plugins and Open Graph' => 'http://www.socialengineaddons.com/socialengine-advanced-facebook-integration-likes-social-plugins-and-open-graph',
    'Facebook Feed Stories Publisher' => 'http://www.socialengineaddons.com/socialengine-facebook-feed-stories-publisher',
    'Suggestions / Recommendations Plugin' => 'http://www.socialengineaddons.com/socialengine-suggestions-recommendations-plugin',
    'Directory / Pages Plugin' => 'http://www.socialengineaddons.com/socialengine-directory-pages-plugin',
    'FAQs, Knowledgebase, Tutorials & Help Center Plugin' => 'http://www.socialengineaddons.com/socialengine-faqs-knowledgebase-tutorials-help-center-plugin',
    'Geo-Location, Geo-Tagging, Check-Ins & Proximity Search Plugin' => 'http://www.socialengineaddons.com/socialengine-geo-location-geo-tagging-checkins-proximity-search-plugin',
    'Video Lightbox Viewer Plugin' => 'http://www.socialengineaddons.com/socialengine-video-lightbox-viewer-plugin',
    'Advanced Slideshow Plugin - Multiple Slideshows' => 'http://www.socialengineaddons.com/socialengine-advanced-slideshow-plugin-multiple-slideshows',
    'Advanced Events Plugin' => 'http://www.socialengineaddons.com/socialengine-advanced-events-plugin',
    'Content Profiles - Cover Photo, Banner & Site Branding Plugin' => 'http://www.socialengineaddons.com/socialengine-content-profiles-cover-photo-banner-site-branding-plugin',
    'Stores / Marketplace - Ecommerce Plugin' => 'http://www.socialengineaddons.com/socialengine-stores-marketplace-ecommerce-plugin',
);

?>
    
<div class="global_form_1">
  <div>
    <div>
      <div><ul><li>
      <?php echo $this->translate('You have successfully created the <b>"%1$s"</b> listing type by using the <b>"%2$s"</b> template. We have integrated the following plugins with this listing type created on our demo website:', strtoupper($this->title_plural), $this->template_type);?><br /><br />
      <table>
        <?php foreach( $modules_array as $key => $value ) {
          echo '<tr><td style="width:90%;font-weight:bold;">'."-&nbsp;" . ucfirst( $key ) . '</td><td><a href="'.$value.'" class="seaocore_type_seaocore" target="_blank">' . $this->translate('View') . '</a></td></tr>'; 
        } ?>
      </table><br/><br/>

      <?php echo $this->translate("With this integration, you can enhance the functionality of this plugin, which in turn will increase traffic on your site by providing more features to the users of your site.") ?>
      <br/><br/>

      <?php if($this->pluginCounts < 3):?>
        <?php echo $this->translate("If you purchase all these plugins from SocialEngineAddOns, then you can avail a Discount. To know more, please file a support ticket from your socialengineaddons client area by %s.",'<a href="http://www.socialengineaddons.com/user/login" target="blank">clicking here</a>');?>
      <?php elseif($this->pluginCounts < 10): ?>
        <?php echo $this->translate("If you want to purchase these plugins from SocialEngineAddOns, then please %s.",'<a href="http://www.socialengineaddons.com/catalog/1/plugins" target="blank">click here</a>');?>
      <?php endif; ?>              

      <br/><br/>
      <?php echo '<button onclick="parent.Smoothbox.close () ;"> '. $this->translate("Close") . ' </button>'; ?>
    </li></ul></div></div>
  </div>
</div>

<style type="text/css">
  .global_form_1 {
    clear:both;
    overflow:hidden;
    margin:15px 0 0 15px;
  }
  .global_form_1 > div {
    -moz-border-radius:7px 7px 7px 7px;
    background-color:#E9F4FA;
    float:left;
    width:600px;
    overflow:hidden;
    padding:10px;
  }
  .global_form_1 > div > div {
    background:none repeat scroll 0 0 #FFFFFF;
    border:1px solid #D7E8F1;
    overflow:hidden;
    padding:20px;
  }
  .global_form_1 .form-sucess {
    margin-bottom:10px;
  }
  .global_form_1 .form-sucess li {
    -moz-border-radius:4px 4px 4px 4px;
    background:#C8E4B6;
    border:2px solid #95b780;
    color:#666666;
    font-weight:bold;
    padding:0.5em 0.8em;
  }
  table td
  {
    border-bottom:1px solid #f1f1f1; 
    padding:5px;
    vertical-align:top;
  }
</style>

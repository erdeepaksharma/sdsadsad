<?php ?>
<style type="text/css">
.form-sub-heading{
	margin-top:10px;
	border-top: 1px solid #EEEEEE;

	height: 1em;
	margin-bottom: 15px;
}
.form-sub-heading div{
	display: block;
	overflow: hidden;
	padding: 4px 6px 4px 0px;
	font-weight: bold;
	float:left;
	margin-top:10px;
}
</style>

<div class="form-sub-heading">
	<div><?php echo $this->translate("LinkedIn Integration Settings");?></div>
	<div><a href="<?php echo $this->url(array('module' => 'seaocore', 'controller' => 'admin-settings', 'action' => 'help-invite'), 'default', true); ?>" target="_blank" ><img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Advancedactivity/externals/images/admin/help.gif"></a></div>
</div>

<div id="linkedin_apikey-wrapper" class="form-wrapper" style="border:none;">
  <div id="linkedin_apikey-label" class="form-label">
    <label for="linkedin_apikey" ><?php echo $this->translate("API Key");?></label>
  </div>
  <div id="linkedin_apikey-element" class="form-element">
    <input name="linkedin_apikey" id="linkedin_apikey" value="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.apikey'); ?>" type="text">
  </div>
</div>
<div id="linkedin_secretkey-wrapper" class="form-wrapper" style="border:none;">
  <div id="linkedin_secretkey-label" class="form-label">
    <label for="linkedin_secretkey" ><?php echo $this->translate("Secret Key");?></label>
  </div>
  <div id="linkedin_secretkey-element" class="form-element">
    <input name="linkedin_secretkey" id="linkedin_secretkey" value="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.secretkey'); ?>" type="text">
  </div>
</div>

<div class="form-sub-heading">
	<div><?php echo $this->translate("Bitly Short URL");?></div>
	<div><a href="<?php echo $this->url(array('module' => 'seaocore', 'controller' => 'admin-settings', 'action' => 'help-invite'), 'default', true); ?>" target="_blank" ><img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Advancedactivity/externals/images/admin/help.gif"></a></div>
</div>

<div id="bitly_apikey-wrapper" class="form-wrapper" style="border:none;">
  <div id="bitly_apikey-label" class="form-label">
    <label for="bitly_apikey" ><?php echo $this->translate("Username");?></label>
  </div>
  <div id="bitly_apikey-element" class="form-element">
    <input name="bitly_apikey" id="bitly_apikey" value="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('bitly.apikey'); ?>" type="text">
  </div>
</div>
<div id="bitly_secretkey-wrapper" class="form-wrapper" style="border:none;">
  <div id="bitly_secretkey-label" class="form-label">
    <label for="bitly_secretkey" ><?php echo $this->translate("API Key");?></label>
  </div>
  <div id="bitly_secretkey-element" class="form-element">
    <input name="bitly_secretkey" id="bitly_secretkey" value="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('bitly.secretkey'); ?>" type="text">
  </div>
</div>
<?php if(false): ?>
<div class="form-sub-heading">
	<div><?php echo $this->translate("Instagram Integration Settings");?></div>
	<div><a href="<?php echo $this->url(array('module' => 'seaocore', 'controller' => 'admin-settings', 'action' => 'help-invite'), 'default', true); ?>" target="_blank" ><img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Advancedactivity/externals/images/admin/help.gif"></a></div>
</div>

<div id="instagram_apikey-wrapper" class="form-wrapper" style="border:none;">
  <div id="instagram_apikey-label" class="form-label">
    <label for="instagram_apikey" ><?php echo $this->translate("Client ID");?></label>
  </div>
  <div id="instagram_apikey-element" class="form-element">
    <input name="instagram_apikey" id="instagram_apikey" value="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.apikey'); ?>" type="text">
  </div>
</div>
<div id="instagram_secretkey-wrapper" class="form-wrapper" style="border:none;">
  <div id="instagram_secretkey-label" class="form-label">
    <label for="instagram_secretkey" ><?php echo $this->translate("Client Secret");?></label>
  </div>
  <div id="instagram_secretkey-element" class="form-element">
    <input name="instagram_secretkey" id="instagram_secretkey" value="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.secretkey'); ?>" type="text">
  </div>
</div>

<div id="advancedactivity_show_in_header-wrapper" class="form-wrapper">
  <div id="advancedactivity_show_in_header-label" class="form-label">
    <label for="advancedactivity_show_in_header" class="optional"><?php echo "Feed Caption"; ?></label>
  </div>
  <div id="advancedactivity_show_in_header-element" class="form-element">
    <p class="description"><?php echo 'Select the type of caption you want to show for your instagram feeds.'?></p>
    <ul class="form-options-wrapper">
      <li><input type="radio" name="advancedactivity_show_in_header" id="advancedactivity_show_in_header-1" value="1" <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.show.in.header')):?> checked="checked" <?php endif; ?>>
        <label for="advancedactivity_show_in_header-1"><?php echo 'Username'; ?></label>
      </li>
      <li><input type="radio" name="advancedactivity_show_in_header" id="advancedactivity_show_in_header-0" value="0" <?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.show.in.header')):?> checked="checked" <?php endif; ?>>
        <label for="advancedactivity_show_in_header-0"><?php echo 'Date'; ?></label>
      </li>
    </ul>
  </div>
</div>

<div id="instagram_feed_count-wrapper" class="form-wrapper">
  <div id="instagram_feed_count-label" class="form-label">
    <label for="instagram_feed_count" class="optional"><?php echo 'Feed Count'; ?></label>
  </div>
  <div id="instagram_feed_count-element" class="form-element">
    <p class="description"><?php echo 'Number of feeds you want to display at a time (Maximum is 40). [Note: Instagram filters the feeds on the basis of the privacy settings, before dispalying them on website. So it may happen that less feeds get displayed than the count you enter. The recommended solution is to request more than you actually need. If left empty or entered 0 then by default 20 feeds will be shown ]'?></p>
    <input type="text" name="instagram_feed_count" id="instagram_feed_count" value="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.feed.count', 8); ?>">
  </div>
</div>

<div id="instagram_image_width-wrapper" class="form-wrapper">
  <div id="instagram_image_width-label" class="form-label">
    <label for="instagram_image_width" class="optional"><?php echo 'Feed Width';?></label>
  </div>
  <div id="instagram_image_width-element" class="form-element">
    <p class="description"><?php echo 'The width of your feed in pixels (Max. width is 640px).';?></p>
    <input type="text" name="instagram_image_width" id="instagram_image_width" value="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.image.width', 150); ?>">
  </div>
</div>

<?php endif; ?>

<div class="form-sub-heading"></div>
<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: share.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php
	$this->headLink()
  	->prependStylesheet($this->layout()->staticBaseUrl.'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');
?>
<div>
  <?php echo $this->form->setAttrib('class', 'global_form_popup')->render($this) ?>
  <br />
  <div class="sharebox">
          <?php if($this->attachment->getType() === 'activity_action'): ?>
        <ul class='feed feed_sections_left_round' id="activity-feed">
          <li class="activity-item">
        <?php echo $this->getRichContent($this->attachment);?>
          </li>
        </ul>
      <?php else: ?>
    <?php if( $this->attachment->getPhotoUrl() ): ?>
      <div class="sharebox_photo">
        <?php echo $this->htmlLink($this->attachment->getHref(), $this->itemPhoto($this->attachment, 'thumb.icon'), array('target' => '_parent')) ?>
      </div>
    <?php endif; ?>
    <div>
      <div class="sharebox_title">
        <?php echo $this->htmlLink($this->attachment->getHref(), $this->attachment->getTitle(), array('target' => '_parent')) ?>
      </div>
      <div class="sharebox_description">
        <?php echo $this->attachment->getDescription() ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<script type="text/javascript">
//<![CDATA[
var toggleFacebookShareCheckbox = function(){
    $$('span.composer_facebook_toggle').toggleClass('composer_facebook_toggle_active');
    $$('input[name=post_to_facebook]').set('checked', $$('span.composer_facebook_toggle')[0].hasClass('composer_facebook_toggle_active'));
}
var toggleTwitterShareCheckbox = function(){
    $$('span.composer_twitter_toggle').toggleClass('composer_twitter_toggle_active');
    $$('input[name=post_to_twitter]').set('checked', $$('span.composer_twitter_toggle')[0].hasClass('composer_twitter_toggle_active'));
}
//]]>
</script>
<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _targetUser.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<style>
	#who-label {display: none;}
</style>

<div class="advancedactivity_post_target_options dnone" id="advancedactivity_post_target_options"  >

  <div class="aaf_smoothbox_popup_overlay"></div>
  <div class="aaf_smoothbox_popup seaocore_box_sizing popup_not_close">
    <div class="aaf_smoothbox_popup_header"><h3><?php echo $this->translate("Target Your Post") ?></h3></div>
    <div class="aaf_smoothbox_popup_container">
    <!--  <p><?php echo $this->translate("Choose preferred audience for your post.") ?></p>-->
      <span id="target_error_message" style="color: #eb1818"class="errors"> </span>
      <?php
      foreach( $this->form->getElements() as $key => $value ) {
        echo $this->form->$key;
      }
      ?>
    </div>
    <div class="aaf_smoothbox_popup_buttons">
      <button type="submit" class="aaf_post_target_save"><?php echo $this->translate("Save"); ?></button>
      <button type="button" class="aaf_post_target_remove"><?php echo $this->translate("Remove"); ?></button>
      <button type="button" class="aaf_post_target_cancel"><?php echo $this->translate("Close"); ?></button>
    </div>
  </div>
</div>
<style type="text/css">
  .aaf_post_target_remove {
    display: none;
  }
  .target_added .aaf_post_target_remove {
    display: initial;
  }
  </style>
<script type="text/javascript">
  var showHideTargetUserOptions = function () {
    $('advancedactivity_post_target_options').removeClass('dnone');
  }
  

  en4.core.runonce.add(function () {
    $$('.aaf_post_target_save').addEvent('click', function (e) {
      e.stop();
      if (!ageValidation()) {
        return;
      }
      $('advancedactivity_post_target_options').addClass('target_added');
      $('advancedactivity_post_target_options').addClass('dnone');
      $$('.adv_button_target_post').addClass('active');
    });
    $$('.aaf_post_target_cancel').addEvent('click', function(){
      en4.advancedactivity.resetTargetPost(false);
    });
    $$('.aaf_post_target_remove').addEvent('click', function() {
      en4.advancedactivity.resetTargetPost(true);
    });
  });
  var ageValidation = function () {
    var min_age = parseInt($('min_age').value);
    $('target_error_message').empty();
    if ($('max_age').value == 0 || min_age == 0) {
      return true;
    }
    if (parseInt($('max_age').value) <= min_age) {
      $('target_error_message').innerHTML = '<?php echo $this->string()->escapeJavascript($this->translate("Maximum age should be greater than the minimum age.")) ?>';
      return false;
    }
    return true;
  }

</script>
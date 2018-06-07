<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/core.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/advancedactivity-facebookse.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/advancedactivity-twitter.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/advancedactivity-linkedin.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/mdetect/mdetect' . ( APPLICATION_ENV != 'development' ? '.min' : '' ) . '.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/smoothbox/smoothbox.js');
?>
<?php if ($this->description): ?>
  <p class="mbot10"><?php echo $this->translate("$this->description"); ?></p>
<?php endif; ?>
<button class="aaf_post_content_button mbot10" style="width: 100%"> <?php echo $this->translate("Post Something!") ?> </button>

<div id="aaf_post_feef_composer" style="display: none;">

  <h3><?php if ($this->category): ?><?php echo $this->translate("Add Post In %s", $this->category->getTitle()) ?>
    <?php else: ?><?php echo $this->translate("Add Post") ?>
    <?php endif; ?>
    <span class="fright seao_smoothbox_lightbox_close">
      X
    </span>
  </h3>

  <?php
  echo $this->partial('_aafcomposer.tpl', 'advancedactivity', array(
      'enableComposer' => $this->enableComposer,
      'showPrivacyDropdown' => $this->showPrivacyDropdown,
      'enableList' => $this->enableList,
      'lists' => $this->lists,
      'countList' => $this->countList,
      'composePartials' => $this->composePartials,
      'settingsApi' => $this->settingsApi,
      'availableLabels' => $this->availableLabels,
      'showDefaultInPrivacyDropdown' => $this->showDefaultInPrivacyDropdown,
      'privacylists' => $this->privacylists,
      'formToken' => $this->formToken,
      'enableNetworkList' => $this->enableNetworkList,
      'network_lists' => $this->network_lists,
      'categoriesList' => $this->categoriesList,
      'showDefault' => true,
      'alwaysOpen' => true,
      'postbyWithoutAjax' => true,
      'category_id' => $this->category_id,
      'inSmoothBox' => true
  ));
  ?>
</div>
<script type="text/javascript">
  var aafcomposer = '';
  en4.core.runonce.add(function() {
    aafcomposer = $('aaf_post_feef_composer');
    $$('.aaf_post_content_button').addEvent('click', function(event) {
      var aafcomposertemp = aafcomposer;

      aafcomposertemp.getElementById('activity-post-container').style.display = 'block';
//      if (!aafcomposertemp.getElementById('activity-post-container').getElement('.seao_smoothbox_lightbox_close')) {
//        new Element('button', {
//          'class': 'seao_smoothbox_lightbox_close',
//          'html': '<?php echo $this->translate("Cancel") ?>'
//        }).inject(aafcomposertemp.getElementById('compose-submit'), 'after');
//      }
      hidestatusbox();
      composeInstance.focus();
      SmoothboxSEAO.open({
        class: 'aaf_composer_content',
        element: aafcomposertemp.setStyle('display', 'block')
                /*request:{
                 url:'http://localhost/seaddons/event-items',
                 }*/
      });
    });
  });


</script>
<style type="text/css">

  .aaf_composer_content{
    padding: 10px;
  }
  .aaf_composer_content .adv_post_container{
    float: inherit !important;
  }
  .aaf_composer_content #advanced_compose-menu > button {
    float: inherit !important;
  }
</style>
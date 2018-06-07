<div id="statusBoxDesign-wrapper" class="form-wrapper"><div id="statusBoxDesign-label" class="form-label"><label for="statusBoxDesign" class="optional">Choose the Status (Post) Box design.</label></div>
  <div id="statusBoxDesign-element" class="form-element">
    <select name="statusBoxDesign" id="statusBoxDesign" onchange="showHideAttachmentOption()" >
      <option value="activator_icon">All Attachments Link Icon</option>
      <option value="activator_buttons">All Attachments Links in Buttons with Popup</option>
      <option value="activator_buttons activator_inline_buttons">All Attachement Links Inside the Status Box with Popup</option>
      <option value="activator_top">All Attachments Links on Top of box</option>
    </select></div></div>
<script type="text/javascript">
  en4.core.runonce.add(function () {
    (function () {
      showHideAttachmentOption();
      showHideAafFeedPhotoBlocks();
    }).delay(300);
  });
  var defalutshowTabsValue;
  function showHideAttachmentOption() {
    if ($('statusBoxDesign').value == 'activator_icon' || $('statusBoxDesign').value == 'activator_buttons activator_inline_buttons') {
      $('maxAllowActivator-wrapper').style.display = 'none';
    } else {
      $('maxAllowActivator-wrapper').style.display = 'block';
    }
    if($('statusBoxDesign').value == 'activator_buttons activator_inline_buttons') {
     $('showTabs-wrapper').style.display = 'none';
     defalutshowTabsValue = $('showTabs').get('value');
     $('showTabs').set('value', 0);
    } else {
      $('showTabs-wrapper').style.display = 'block';
      $('showTabs').set('value', defalutshowTabsValue);
    }
  }
  function showHideAafFeedPhotoBlocks() {
    if ($('customPhotoBlock').value == 0) {
      $$('.aaf_feed_photo_blocks').setStyle('display', 'none');
    } else {
      $$('.aaf_feed_photo_blocks').setStyle('display', 'block');
    }
  }
</script>

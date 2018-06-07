<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: add-more-list.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$this->headLink()
  ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');
?>
<?php
$this->headScript()
  ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
  ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
  ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
  ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js');
?>

<script type="text/javascript">
  function setListValue() {
<?php if( empty($this->action_id) ): ?>

      var oldValue = window.parent.document.getElementById('auth_view').value;
      var oldValueArray = oldValue.split(",");
      for (var i = 0; i < oldValueArray.length; i++) {
        var tempListElement = window.parent.document.getElementById('privacy_list_' + oldValueArray[i]);
        tempListElement.removeClass('aaf_tab_active').addClass('aaf_tab_unactive');
      }

      var toValues = getCheckNetworkValue();
      var toValuesArray = toValues.split(",");
      var label = '';
      for (var i = 0; i < toValuesArray.length; i++) {
        if (label == '')
          label = $('network_list-' + toValuesArray[i]).getParent('li').getElement('label').innerHTML;
        else
          label = label + ", " + $('network_list-' + toValuesArray[i]).getParent('li').getElement('label').innerHTML;
        tempListElement = window.parent.document.getElementById('privacy_list_' + toValuesArray[i]);
        tempListElement.addClass('aaf_tab_active').removeClass('aaf_tab_unactive');
      }

      if (label != '')
        label = en4.core.language.translate('Share with %s', label);
      window.parent.document.getElementById("adv_custom_list_privacy_lable_tip").innerHTML = label;
      window.parent.document.getElementById('show_default').innerHTML = '<i class="aaf_privacy_pulldown_icon aaf_icon_feed_custom"></i><span>' + window.parent.document.getElementById("user_profile_network_list_custom_div").innerHTML + '</span><i class="aaf_privacy_pulldown_arrow"></i>';

      //window.parent.document.getElementById("user_profile_friend_list_custom").innerHTML;
      window.parent.document.getElementById('auth_view').value = toValues;
      parent.Smoothbox.close();
<?php else : ?>
      var oldValue = window.parent.document.getElementById('aaf_edit_auth_view').value;
      var oldValueArray = oldValue.split(",");
      for (var i = 0; i < oldValueArray.length; i++) {
        var tempListElement = window.parent.document.getElementById('aaf_edit_privacy_list_' + oldValueArray[i]);
        tempListElement.removeClass('aaf_tab_active').addClass('aaf_tab_unactive');
      }

      var toValues = getCheckNetworkValue();
      var toValuesArray = toValues.split(",");
      var label = '';
      for (var i = 0; i < toValuesArray.length; i++) {
        if (label == '')
          label = $('network_list-' + toValuesArray[i]).getParent('li').getElement('label').innerHTML;
        else
          label = label + ", " + $('network_list-' + toValuesArray[i]).getParent('li').getElement('label').innerHTML;
        tempListElement = window.parent.document.getElementById('aaf_edit_privacy_list_' + toValuesArray[i]);
        tempListElement.addClass('aaf_tab_active').removeClass('aaf_tab_unactive');
      }

      if (label != '')
        label = en4.core.language.translate('Share with %s', label);
      window.parent.document.getElementById("adv_edit_privacy_lable_tip").innerHTML = label;
      window.parent.document.getElementById('show_aaf_edit_privacy').innerHTML = '<i class="aaf_privacy_pulldown_icon aaf_icon_feed_custom"></i><span>' + window.parent.document.getElementById("aaf_edit_network_list_custom_div").innerHTML + '</span><i class="aaf_privacy_pulldown_arrow"></i>';

      //window.parent.document.getElementById("user_profile_friend_list_custom").innerHTML;
      window.parent.document.getElementById('aaf_edit_auth_view').value = toValues;
      parent.Smoothbox.close();

<?php endif; ?>
  }

  var iframeEL = window.parent.document.getElementById("TB_iframeContent");
  function resetFormSize() {
    (function () {
      if (iframeEL)
        iframeEL.style.height = (document.getElementById("member_list_popup").getSize().y + 10) + 'px';
    }).delay(100);
  }

  function getCheckNetworkValue()
  {
    var checked_ids = new Array()
    $$('.network_list').each(function (el) {
      if (el.checked) {
        checked_ids.push(el.value);
      }
    });
    $("network_list_selected").value = checked_ids.join();
    return $("network_list_selected").value;
  }

</script>
<div class="global_form_popup aaf_custom_list_add" id="member_list_popup">
  <form method="post"  class="global_form"  id="member_list">
    <div>
      <div>
        <p class="form-description"><b><?php echo $this->translate("Select the networks with which you want to share this post.") ?></b></p>
        <div class="form-elements">
          <div id="network_wapper_content" > 
            <div id="network_list_wapper" class="form-wrapper"><div id="network_list-label" class="form-label"><label  class="optional"></label></div>
              <div id="network_list-element" class="form-element">
                <input type="hidden" name="network_list_selected" id="network_list_selected" value=""><ul class="form-options-wrapper">
                  <?php foreach( $this->network_lists as $list ): ?>
                    <li><input class="network_list" type="checkbox" name="network_list[]" id="network_list-<?php echo "network_" . $list->getIdentity() ?>" value="network_<?php echo $list->getIdentity() ?>"><label for="network_list-network_<?php echo $list->getIdentity() ?>"> <?php echo $this->translate($list->getTitle()) ?></label></li>
                  <?php endforeach; ?>
                </ul>
              </div></div>
          </div>
          <div class="form-wrapper" id="add-wrapper">
            <div class="form-element" id="add-element">
              <button onclick="setListValue()" type="button" id="add" name="add"><?php echo
                  $this->translate("Done")
                  ?></button>
              <button onclick="javascript:parent.Smoothbox.close()" type="button" id="cancel" name="cancel"><?php echo $this->translate("Cancel") ?></button>
            </div>

          </div>            
        </div>
      </div>
    </div>
  </form>
</div>


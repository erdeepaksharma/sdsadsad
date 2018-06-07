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

  // Populate data
  var maxRecipients = 10;
  var isPopulated = false;
  var addList = new Array();
  function removeFromToValue(id) {
    // code to change the values in the hidden field to have updated values
    // when recipients are removed.
    var toValues = document.getElementById('toValues').value;
    var toValueArray = toValues.split(",");
    var toValueIndex = "";

    var checkMulti = id.search(/,/);

    // check if we are removing multiple recipients
    if (checkMulti != -1) {
      var recipientsArray = id.split(",");
      for (var i = 0; i < recipientsArray.length; i++) {
        removeToValue(recipientsArray[i], toValueArray);
      }
    } else {
      removeToValue(id, toValueArray);
    }

    // hide the wrapper for usernames if it is empty
    if (document.getElementById('toValues').value == "") {
      document.getElementById('toValues-wrapper').setStyle('height', '0');
    }

    document.getElementById('to').disabled = false;
    resetFormSize();
  }

  function removeToValue(id, toValueArray) {
    for (var i = 0; i < toValueArray.length; i++) {
      if (toValueArray[i] == id)
        toValueIndex = i;
    }

    toValueArray.splice(toValueIndex, 1);
    addList.splice(id, 1);
    document.getElementById('toValues').value = toValueArray.join();
  }

  en4.core.runonce.add(function () {
    var contentAutocomplete = new Autocompleter.Request.JSON('to', '<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'index', 'action' => 'suggest'), 'default', true) ?>', {
      'minLength': 1,
      'delay': 250,
      'selectMode': 'pick',
      'autocompleteType': 'message',
      'multiple': false,
      'className': 'message-autosuggest',
      'filterSubset': true,
      'tokenFormat': 'object',
      'tokenValueKey': 'label',
      'injectChoice': function (token) {
        var choice = new Element('li', {
          'class': 'autocompleter-choices friendlist',
          'id': token.label
        });
        new Element('div', {
          'html': this.markQueryValue(token.label),
          'class': 'autocompleter-choice'
        }).inject(choice);
        this.addChoiceEvents(choice).inject(this.choices);
        choice.store('autocompleteChoice', token);
      },
      onPush: function () {
        if (document.getElementById('toValues').value.split(',').length >= maxRecipients) {
          document.getElementById('to').disabled = true;
        }
        document.getElementById('toValues-wrapper').setStyle('height', 'auto');
      }
    });

    contentAutocomplete.addEvent('onSelection', function (element, selected, value, input) {
      addList[selected.retrieve('autocompleteChoice').id] = selected.retrieve('autocompleteChoice').label;
      resetFormSize();
    });
    new Composer.OverText(document.getElementById('to'), {
      'textOverride': '<?php echo $this->translate('Start typing...') ?>',
      'element': 'label',
      'isPlainText': true,
      'positionOptions': {
        position: (en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft'),
        edge: (en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft'),
        offset: {
          x: (en4.orientation == 'rtl' ? -4 : 4),
          y: 2
        }
      }
    });
  });

  en4.core.runonce.add(function () {
    if (document.getElementById('toValues-wrapper'))
      document.getElementById('toValues-wrapper').setStyle('height', '0');
  });
  function setListValue() {
<?php if( empty($this->action_id) ): ?>
      var oldValue = window.parent.document.getElementById('auth_view').value;
      var oldValueArray = oldValue.split(",");
      for (var i = 0; i < oldValueArray.length; i++) {
        var tempListElement = window.parent.document.getElementById('privacy_list_' + oldValueArray[i]);
        tempListElement.removeClass('aaf_tab_active').addClass('aaf_tab_unactive');
      }

      var toValues = document.getElementById('toValues').value;
      var toValueArray = toValues.split(",");
      var label = '';
      for (var i = 0; i < toValueArray.length; i++) {
        if (label == '')
          label = addList[toValueArray[i]];
        else
          label = label + ", " + addList[toValueArray[i]];

        tempListElement = window.parent.document.getElementById('privacy_list_' + toValueArray[i]);
        tempListElement.addClass('aaf_tab_active').removeClass('aaf_tab_unactive');
      }
      if (label != '')
        label = en4.core.language.translate('Share with %s', label);
      window.parent.document.getElementById("adv_custom_list_privacy_lable_tip").innerHTML = label;
      window.parent.document.getElementById('show_default').innerHTML = '<i class="aaf_privacy_pulldown_icon aaf_icon_feed_custom"></i><span>' + window.parent.document.getElementById("user_profile_friend_list_custom_div").innerHTML + '</span><i class="aaf_privacy_pulldown_arrow"></i>';

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

      var toValues = document.getElementById('toValues').value;
      var toValueArray = toValues.split(",");
      var label = '';
      for (var i = 0; i < toValueArray.length; i++) {
        if (label == '')
          label = addList[toValueArray[i]];
        else
          label = label + ", " + addList[toValueArray[i]];

        tempListElement = window.parent.document.getElementById('aaf_edit_privacy_list_' + toValueArray[i]);
        tempListElement.addClass('aaf_tab_active').removeClass('aaf_tab_unactive');
      }
      if (label != '')
        label = en4.core.language.translate('Share with %s', label);
      window.parent.document.getElementById("adv_edit_privacy_lable_tip").innerHTML = label;
      window.parent.document.getElementById('show_aaf_edit_privacy').innerHTML = '<i class="aaf_privacy_pulldown_icon aaf_icon_feed_custom"></i><span>' + window.parent.document.getElementById("aaf_edit_friend_list_custom_div").innerHTML + '</span><i class="aaf_privacy_pulldown_arrow"></i>';

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
</script>
<div class="global_form_popup aaf_custom_list_add" id="member_list_popup">
  <form method="post"  class="global_form"  id="member_list">
    <div>
      <div>
        <p class="form-description"><b><?php echo $this->translate("Enter the lists of friends with which you want to share this post.") ?></b></p>
        <div class="form-elements">
          <div class="form-wrapper" id="to-wrapper">
            <div class="form-label" id="to-label">
              <label class="optional" for="to"><?php echo $this->translate("These Friend Lists") ?></label>
                <span class="aaf_custom_list_add_tip_wrapper">
                <span class="aaf_custom_list_add_tip">
                  <?php echo $this->translate("You can add maximum of 10 lists.") ?>
                  <img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" align="" alt="" />
                </span>
                <img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/help.gif" align="" alt="" />
              </span>
            </div>
            <div class="form-element" id="to-element">
              <input type="text" autocomplete="off" value="" id="to" name="to" class="fleft" />
              <ul class="message-autosuggest" style="z-index: 42; visibility: hidden; opacity: 0;"></ul>
            </div>
          </div>

          <div class="form-wrapper" id="toValues-wrapper" style="height: 0pt;">
            <div class="form-element" id="toValues-element">
              <input type="hidden" id="toValues" value="" name="toValues">
            </div>
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


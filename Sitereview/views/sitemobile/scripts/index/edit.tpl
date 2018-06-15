<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: edit.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<?php 
  $defaultProfileFieldId = Engine_Api::_()->getDbTable('metas', 'sitereview')->defaultProfileId();
  $defaultProfileFieldId = "0_0_$defaultProfileFieldId";
  ?>
<script type="text/javascript">

	sm4.core.runonce.add(function() {
		sm4.core.Module.autoCompleter.attach("tags", '<?php echo $this->url(array('module' => 'seaocore', 'controller' => 'index', 'action' => 'tag-suggest'), 'default', true) ?>', {'singletextbox': true, 'limit':10, 'minLength': 1, 'showPhoto' : false, 'search' : 'text'}, 'toValues'); 
	});

  sm4.core.runonce.add(function()
  {
     checkDraft();
     setDefaultEndDate();
     var defaultProfileId = '<?php echo $defaultProfileFieldId ?>'  + '-wrapper';
     if($.type($.mobile.activePage.find('#'+defaultProfileId)) && typeof $.mobile.activePage.find('#'+defaultProfileId) != 'undefined') {
        $.mobile.activePage.find('#'+defaultProfileId).css('display', 'none');
     }
  });
  
  function checkDraft(){
    if($.mobile.activePage.find('#draft')){
      if($.mobile.activePage.find('#draft').val() ==1) {
        $.mobile.activePage.find("#search-wrapper").css('display', 'none');
        $.mobile.activePage.find("#search").attr("checked",false);
        
        if($.mobile.activePage.find("#creation_date-wrapper")) {
            $.mobile.activePage.find("#creation_date-wrapper").css('display', 'none');
        }            
        
      } else{
        $.mobile.activePage.find("#search-wrapper").css('display', 'block');
        $.mobile.activePage.find("#search").attr("checked",true);
        
        if($.mobile.activePage.find("#creation_date-wrapper")) {
            $.mobile.activePage.find("#creation_date-wrapper").css('display', 'block');
        }            
        
      }
    }
  }
  function submitSession(id) {
        if (sm4.core.isApp()) {
              var title = '<?php echo $this->listing_singular_uc?>';
            alert( "Please go to the Full Site to Make Payment for your "+title+" listing.");
        }else{
	$("#listing_id_session").value=id;
	$("#setSession_form").submit();
        }
}
  var updateTextFields = function(endsettings)
  {
    endsettings = $(endsettings);
    $.mobile.activePage.find("#end_date-wrapper").css('display', 'none');
    if (endsettings.val() == 0)
    {
      $.mobile.activePage.find("#end_date-wrapper").css('display', 'none');
      return;
    }

    if (endsettings.val() == 1)
    {
      $.mobile.activePage.find("#end_date-wrapper").css('display', 'block');
      return;
    }
  }

  function setDefaultEndDate() {
    if('<?php echo $this->expiry_setting; ?>' !='1'){
      $.mobile.activePage.find("#end_date_enable-wrapper").css('display', 'none');
      $.mobile.activePage.find("#end_date-wrapper").css('display', 'none');
    } else{
      if($.mobile.activePage.find("#end_date_enable-1").attr("checked") == 'checked'){
        $.mobile.activePage.find("#end_date-wrapper").css('display', 'block');
      } else{
        $.mobile.activePage.find("#end_date-wrapper").css('display', 'none');
      }
    }
  }
  
  $(window).bind('domready', function() {
      <?php if ($this->profileType): ?>	
        $.mobile.activePage.find('#' + '<?php echo '0_0_' . $this->defaultProfileId ?>').value= <?php echo $this->profileType ?>;
        changeFields($.mobile.activePage.find('#' + '<?php echo '0_0_' . $this->defaultProfileId ?>'));
      <?php endif; ?>
    });
    
  if( '<?php echo $this->show_editor ;?>' == 1 ) {
    //sm4.core.runonce.add(function(){  
     setTimeout(function() {
       sm4.core.tinymce.showTinymce($.mobile.activePage.find('#body')[0]);
       }, 1000);
    //});
  }  
    sm4.core.runonce.add(updateTextFields);
    
</script>


	<?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting') && Engine_Api::_()->sitereviewpaidlisting()->canShowPaymentLink($this->sitereview->listing_id)): ?>

<?php 
$listingId = $this->sitereview->listing_id;
$redirectUrl = $this->url(array(), "sitereview_session_payment_$this->listingtype_id", true)."?listing_id=$listingId";
?>
				<div>
                                    <a class="ui-btn ui-icon-arrow-r ui-btn-icon-right" href='javascript:void(0);' onclick="submitSession(<?php echo $this->sitereview->listing_id ?>)"><?php echo $this->translate('Make Payment'); ?></a>
                                    <form name="setSession_form" method="post" id="setSession_form" action="<?php echo $redirectUrl ?>">
                                            <input type="hidden" name="listing_id_session" id="listing_id_session" />
                                    </form>
				</div>
	<?php endif; ?>
<?php
/* Include the common user-end field switching javascript */
echo $this->partial('_jsSwitch.tpl', 'fields', array(
        //'topLevelId' => (int) @$this->topLevelId,
        //'topLevelValue' => (int) @$this->topLevelValue
))
?>
<?php echo $this->partial('application/modules/Sitereview/views/sitemobile/scripts/dashboard/header.tpl', array('sitereview'=>$this->sitereview));?>
<div class="dashboard-content">
  <?php echo $this->form->render(); ?>
</div>
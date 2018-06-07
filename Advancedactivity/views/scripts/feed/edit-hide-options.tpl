<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: edit-hide-option.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php
	$this->headLink()
  	->prependStylesheet($this->layout()->staticBaseUrl.'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');
?>
<?php if(count($this->hideItems)<1):?>
    <div class="tip">
        <span style="margin-right: 15px; margin-left: 15px">
          <?php echo $this->translate("You have not hidden activity feeds from any sources."); ?>
        </span>
        <button onclick='javascript:parent.Smoothbox.close()'> <?php echo $this->translate('Close') ?></button>
    </div>
<?php else: ?>
<div class="seaocore_members_popup seaocore_members_popup_notbs">
  <div class="top">
    <div class="heading"><?php echo $this->translate('Edit Activity Feed Settings'); ?>
    </div>  
  </div>
  <div class="aaf_edit_setting">
  	<div class="aaf_edit_setting_right">  
	  	
	    	<?php foreach ($this->hideItems as $resource_type=>$hideItem):?>    
	      	<div class="aaf_edit_setting_right_list_head">
	      		<?php echo $this->translate("AAF_HIDE_".strtoupper($resource_type)."_TYPE_TITLE"); ?>
	      	</div>
	      	<?php foreach ($hideItem as $item_id):?>
			      <div id="hide_item_<?php echo $resource_type ?>_<?php echo $item_id ?>" class="aaf_edit_setting_right_list">
			      	<?php $content=Engine_Api::_()->getItem($resource_type,$item_id);?>
			        <?php echo $content->getTitle();?>
			        <span onclick="selectForUnhideItem('<?php echo $resource_type ?>','<?php echo $item_id ?>');" class="aaf_icon_remove" title="<?php echo $this->translate("Remove"); ?>"></span>
			      </div>
		      <?php endforeach; ?>
	    	<?php endforeach;?>	      
	  </div>  
	</div>
</div>	
<div class="seaocore_members_popup_bottom">
  <form action="" method="post">
    <input type="hidden" name="unhide_items" id="unhide_items" value="" />
    <button type="submit"> <?php echo $this->translate('Save') ?></button>  
    <button onclick='javascript:parent.Smoothbox.close()'> <?php echo $this->translate('Cancel') ?></button>
  </form>
</div>
<div class="clr dblock"></div>

<script type="text/javascript">
  var hideItem=new Array();
  function selectForUnhideItem(type,id){ 
   var content= type+'_'+id;
   var el= document.getElementById('hide_item_'+content);
   if(el)
     el.style.display='none';
   hideItem.push(type+'-'+id);
  document.getElementById('unhide_items').value =hideItem;
  }
</script>
<?php endif; ?>
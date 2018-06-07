<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: share-item.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php
	$this->headLink()
  	->prependStylesheet($this->layout()->staticBaseUrl.'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');
?>
<div id="content_share_aaf" class="seaocore_members_popup seaocore_members_popup_notbs">
  <div class="top">
    <div class="heading">
    	<?php echo $this->translate('People Who Shared This'); ?>
    </div>  
  </div>  
  <div id="content_share_aaf_feed" class="seaocore_members_popup_content">  
    <?php
    echo $this->shareAdvancedActivityLoop($this->activity, array(
        'action_id' => null,
        'viewAllComments' => false,
        'viewAllLikes' => false,
    ));
    ?>
  </div>
</div>
<div class="seaocore_members_popup_bottom">
	<div class="fleft">
		<div class="tip">
		  <span>
				<?php echo $this->translate("You can only see shares that are visible to you in your activity feeds."); ?>
		  </span>
		</div>
	</div>
	<button  onclick='javascript:parent.Smoothbox.close()'><?php echo $this->translate('Close'); ?></button>
</div>

<script type="text/javascript">
    window.addEvent('domready', function () {
    $('content_share_aaf').setStyles({
        height : (window.parent.getSize().y-160)+ 'px' 
      })
       $('content_share_aaf_feed').setStyles({
        height : (window.parent.getSize().y-200)+ 'px' 
      }); 
    });
    
  document.addEvent('click', function (event) {
    var el= event.target;
    if(el.get('tag')=='img'){
      el=el.getParent();
    }    
    if(el.get('tag')=='a' && el.hasAttribute("rel") && el.hasAttribute("href")){
      event.stop();
      parent.window.location.href =el.href;
      parent.Smoothbox.close();
    }
  });
    
</script>

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
<h2>
    <?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>

<?php if (count($this->navigation)): ?>
<div class='seaocore_admin_tabs'>
    <?php
    // Render the menu
    //->setUlClass()
    echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
</div>
<?php endif; ?>
<?php 
$this->navigationAAF = $navigationAAF = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main_settings', array(), 'advancedactivity_admin_main_settings_banner');

?> 

<?php if (count($this->navigationAAF)): ?>
<div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigationAAF)->render() ?>
</div>
<?php endif; ?>



<p>
    <?php echo $this->translate('Below, you can manage the feed backgrounds which will stand out in a status update. You can add a new background via ‘Add New Background’ link, set color for the background and text displaying over it as per your choice. Set the sequence of backgrounds by drag-drop and option to edit, delete, highlight and to check the preview of that background.'); ?> 
</p>
<br />
<div class='clear seaocore_settings_form'>
    <div class="tip">
        <span>  To manage the banner related settings go to <b>‘Feed Decoration Settings’</b> tab from <a href="<?php echo $this->baseUrl()?>/admin/advancedactivity/settings/feed-settings"> here.</a>
        </span>
    </div>
</div>
<div>

    <a href="<?php echo $this->url(array('action' => 'create')) ?>" class="buttonlink seaocore_icon_add " title="<?php echo $this->translate('Add New Banner'); ?>"><?php echo $this->translate('Add New Banner'); ?></a> 
    <span><?php echo $this->translate('Total Banner ('.count($this->banners).')'); ?></span>
</div>
<br />

<?php $banners = $this->banners->toArray(); if(!empty($banners)) : ?> 
<div class="seaocore_admin_order_list">
  <form id='saveorder_form' method='post' action='<?php echo $this->url(array('action' => 'update-order')) ?>'>
    <input type='hidden'  name='order' id="order" value=''/>
    <div id='order-element'>
        <ul class="aaf-banner-preview">

        <?php foreach ($this->banners as $item) : ?> 
        <?php $file = Engine_Api::_()->getDbtable('files', 'storage')->getFile($item->file_id); ?>
        <?php $title = null; $imgpath = null; ?>
        <?php if(!empty($file)): $title = "Image Banner"; $imgpath = $file->getHref(); elseif($item->gradient): $gradient = $item->gradient; $title = "Gradient Banner"; else: $title = "Color Banner"; endif; ?>
        <?php if(!empty($item->enabled)): $class = "aaf-banner-enable-status"; else: $class = "aaf-banner-disable-status"; endif; ?>
         <li class="<?php echo $class ?>" title="<?php echo $title ?>" >
            <input type='hidden'  name='order[]' value='<?php echo $item->banner_id; ?>'>
            <div style="color:<?php echo $item->color ?>; <?php if(!empty($imgpath)): ?> background:url(<?php echo $imgpath ?>); <?php elseif($item->gradient): ?> background: <?php echo $item->gradient; ?>;<?php else: ?> background: <?php echo $item->background_color; ?>; <?php endif; ?>"  onclick="setPreview('<?php echo $imgpath ?>', '<?php echo $item->background_color ?>', '<?php echo $item->color ?>','<?php echo $item->gradient ?>')" class='aaf-banner'>
        
      
                <span style="font-weight: 600">  <?php echo $this->translate('Preview Will look like this.') ?> </span> 
                </div>
                <div class="aaf-banner-info">
					<div class="aaf-banner-start-date">
						<span>From:</span>
						<?php echo $item->startdate;?>
					</div>
					<div class="aaf-banner-end-date">
						<span>To:</span>
						<?php echo $item->enddate;?>
					</div>
					<div class="aaf-banner-status">
						<?php echo ( $item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' =>
						'advancedactivity', 'controller' => 'banner', 'action' => 'enabled', 'banner_id' =>
						$item->banner_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled1.gif',
						'', array('title' => $this->translate('Disable Banner'))), array())  :
						$this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'banner',
						'action' => 'enabled', 'banner_id' => $item->banner_id),
						$this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled0.gif', '', array('title' =>
						$this->translate('Enable Banner')))) ) ?>
					</div>
					<div class="aaf-banner-actionlinks">
						<a class='buttonlink aaf_banner_icon_preview' title='Preview' href='javascript:void(0)' onclick="setPreview('<?php echo $imgpath ?>', '<?php echo $item->background_color ?>', '<?php echo $item->color ?>','<?php echo $item->gradient ?>')">
						</a>
                                                <?php if(!empty($item->highlighted)): $ttile = "Mark As Unhighlighted"; $class = "aaf_banner_icon_unhighlight"; else: $ttile = "Mark As Highlighted"; $class = "aaf_banner_icon_highlight"; endif;  ?>
                                                <a class='buttonlink <?php echo $class ?>' title='<?php echo $ttile ?>' href='<?php echo $this->url(array('action' => 'highlight', 'banner_id' => $item->banner_id)) ?>'>
						</a>
						<a class='buttonlink aaf_banner_icon_edit' title='Edit' href='<?php echo $this->url(array('action' => 'edit', 'banner_id' => $item->banner_id)) ?>'>
						</a>
						<a class='buttonlink aaf_banner_icon_delete smoothbox' title='Delete' href='<?php echo $this->url(array('action' => 'delete', 'banner_id' => $item->banner_id)) ?>' class="smoothbox">
						</a>
					</div>
				</div>
			</li>
    <?php endforeach; ?>
	</ul>
    </div>
  </form>
    <button style="float:left; clear: both;" onClick="javascript:saveOrder(true);" type='submit'>
    <?php echo $this->translate("Save Order") ?>
  </button>
</div>
<script type="text/javascript">

  var saveFlag = false;
  var origOrder;
  var changeOptionsFlag = false;

  function saveOrder(value) {
    saveFlag = value;
    var finalOrder = [];
    var li = $('order-element').getElementsByTagName('li');
    for (i = 1; i <= li.length; i++)
      finalOrder.push(li[i]);
    $("order").value = finalOrder;

    $('saveorder_form').submit();
  }
  window.addEvent('domready', function() {
    //         We autogenerate a list on the fly
    var initList = [];
    var li = $('order-element').getElementsByTagName('li');
    for (i = 1; i <= li.length; i++)
      initList.push(li[i]);
    origOrder = initList;
    var temp_array = $('order-element').getElementsByTagName('ul');
    temp_array.innerHTML = initList;
    new Sortables(temp_array);
  });

  window.onbeforeunload = function(event) {
    var finalOrder = [];
    var li = $('order-element').getElementsByTagName('li');
    for (i = 1; i <= li.length; i++)
      finalOrder.push(li[i]);



    for (i = 0; i <= li.length; i++) {
      if (finalOrder[i] != origOrder[i])
      {
        changeOptionsFlag = true;
        break;
      }
    }

    if (changeOptionsFlag == true && !saveFlag) {
      var answer = confirm("<?php echo $this->string()->escapeJavascript($this->translate("A change in the order of the tabs has been detected. If you click Cancel, all unsaved changes will be lost. Click OK to save change and proceed.")); ?>");
      if (answer) {
        $('order').value = finalOrder;
        $('saveorder_form').submit();

      }
    }
  }
</script>

<?php else: ?>  
<br />
<div class='tip'>
    <span>
        <?php echo $this->translate('You have not added any banner yet.') ?>
    </span>
</div>
<?php endif; ?>
<script type="text/javascript">
    function setPreview(imagePath, bgcolor, color, gradient) {
        var preview = "<div style='padding: 110px 30px; text-align:center; font-size: 25px; color:" + color + "; font-weight:bold;"+ (gradient ? "background:"+gradient+";" : "background:"+bgcolor+";");
        var background = imagePath ? "background:url(" + imagePath + ");" : ";";
        preview += background + "'><span style='font-weight:600'>" + en4.core.language.translate('Preview will look like this.') + "</span></div>";
        Smoothbox.open("<div>" + preview + "</div>");
    }
    window.addEventListener("beforeunload", function(e){
  confirm(" Hello");
}, false);
</script>

<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: manage-Custom-block.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<script type="text/javascript">
  var previewFileForceOpen;
  var previewFile = function(event)
  {
	event = new Event(event);
	element = $(event.target).getParent('.admin_file').getElement('.admin_file_preview');
			
	// Ignore ones with no preview
	if( !element || element.getChildren().length < 1 ) {
	  return;
	}

	if( event.type == 'click' ) {
	  if( previewFileForceOpen ) {
		previewFileForceOpen.setStyle('display', 'none');
		previewFileForceOpen = false;
	  } else {
		previewFileForceOpen = element;
		previewFileForceOpen.setStyle('display', 'block');
	  }
	}
	if( previewFileForceOpen ) {
	  return;
	}

	var targetState = ( event.type == 'mouseover' ? true : false );
	element.setStyle('display', (targetState ? 'block' : 'none'));
  }

  window.addEvent('load', function() {
	$$('.customblock-image-preview').addEvents({
	  click : previewFile,
	  mouseout : previewFile,
	  mouseover : previewFile
	});
	$$('.admin_file_preview').addEvents({
	  click : previewFile
	});
  });

  function multiDelete()
  {
	var finalOrder = [];
	var li = $('order-element').getElementsByTagName('li');
	for (i = 1; i <= li.length; i++)
	  finalOrder.push(li[i]);
	for (i = 0; i <= li.length; i++){
	  if(finalOrder[i]!=origOrder[i])
	  {
		changeOptionsFlag = true;
		break;
	  }
	}
	if(changeOptionsFlag == true) {
	  var orderchange=confirm("<?php echo $this->string()->escapeJavascript($this->translate("A change in the order has been detected. If you click OK, all unsaved changes will be lost. Click Cancel to stay on this page and save your changes.")); ?>");
			
	  if(orderchange){
		var doc= confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected Custom Block? They will not be recoverable after being deleted.")) ?>'); 		if(doc == true)
		  setFlag(true);
		return doc;
	  }
	  else {
		setFlag(false);
		return orderchange;
	  }
	}
	else {
	  return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected Custom Block? They will not be recoverable after being deleted.")) ?>'); 
	  }
  }

  function selectAll()
  {
	var i;
	var multidelete_form = $('multidelete_form');
	var inputs = multidelete_form.elements;
	for (i = 1; i < inputs.length - 1; i++) {
	  if (!inputs[i].disabled) {
		inputs[i].checked = inputs[0].checked;
	  }
	}
  }
		
  var saveFlag=false;
  var origOrder;
  var changeOptionsFlag = false;

  function setFlag(value){
	saveFlag=value;
  }
  window.addEvent('domready', function(){
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

  window.onbeforeunload = function(event){
	var finalOrder = [];
	var li = $('order-element').getElementsByTagName('li');
	for (i = 1; i <= li.length; i++)
	  finalOrder.push(li[i]);
	for (i = 0; i <= li.length; i++){
	  if(finalOrder[i]!=origOrder[i])
	  {
		changeOptionsFlag = true;
		break;
	  }
	}
						
	if(changeOptionsFlag == true && !saveFlag){
	  var answer=confirm("<?php echo $this->string()->escapeJavascript($this->translate("A change in the order has been detected. If you click OK, all unsaved changes will be lost. Click Cancel to stay on this page and save your changes.")); ?>");
	  if(answer) {
		document.multidelete_form.submit();
	  }
	}
  }
</script>

<h2>
  <?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>

<?php if (count($this->navigation)): ?>
	<div class='seaocore_admin_tabs'>
  <?php
	// Render the menu
	echo $this->navigation()->menu()->setContainer($this->navigation)->render()
  ?>
  </div>
<?php endif; ?>

<?php if (count($this->sub_navigation)): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->sub_navigation)->render() ?>
  </div>
<?php endif; ?>

	<h3><?php echo $this->translate('Custom Blocks for Welcome Tab') ?></h3>
	<p><?php echo $this->translate('Below you can create and manage Custom Blocks for the Welcome Tab. You can use custom blocks to show welcome content to users which is different from the already available blocks. For example, you can introduce those features / aspects of your website that form your site\'s most important core features. You can order the blocks in desired sequence using drag-and-drop below. Note that for custom blocks to be visible, you must place the "Welcome: Custom Blocks" widget in Welcome Tab page from the Layout Editor.') ?>
	</p>
	<br class="clear" />
<?php
	// Show Success message.
	if (isset($this->success_message)) {
	  echo '<ul class="form-notices" style="margin:0px;"><li style="float:left;">' . $this->translate('Successfully create') . ' ' . ucfirst($this->success_message) . ' ' . $this->translate('Custom Block.') . '</li></ul>';
	}
?>

	<p class="clear" style="display:block;">

	<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'welcome-settings'), $this->translate("Back to Welcome Tab Settings"), array('class'=>'seaocore_icon_back buttonlink')) ?>

<?php
	// Show link for "Create Featured Content".
	echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'custom-block-create'), $this->translate("Create new Custom Block"), array('class' => 'buttonlink seaocore_icon_add'));
?>
  </p>
  <br />

<?php if (count($this->paginator)): ?>
	<form id='multidelete_form' name='multidelete_form' method="post" action="" >
	  <div class="seaocore_admin_order_list">
		<div class="list_head">
		  <div style="width:2%;"><input onclick="selectAll()" type='checkbox' class='checkbox'></div>
		  <div style="width:2%;">
<?php
	  if (empty($this->id_orderby)) {
		$orderby = 1;
	  } else {
		$orderby = 0;
	  }
	  echo $this->translate("ID");
?>
	  </div>
	  <div style="width:26%;">
		<?php echo $this->translate("Block Title"); ?>
	  </div>
	  <div style="width:20%;" class="admin_table_centered">
		<?php echo $this->translate("Limitation"); ?>
	  </div>
	  <div style="width:20%;" class="admin_table_centered">
		<?php echo $this->translate("Status"); ?>
	  </div>
	  <div style="width:20%;" class="admin_table_centered">
<?php echo $this->translate("Options"); ?>
	  </div>
	</div>

	<div id='order-element'>
	  <ul>
<?php foreach ($this->paginator as $item): $i = 0;
		  $i++;
		  $id = 'admin_file_' . $i;
		  $contentKey = $item->customblock_id; ?>
  		<li>
  		  <input type='hidden' name='customblock_id[]' value='<?php echo $item->customblock_id; ?>'>
		  <div style="width:2%;">
			<input type='checkbox' name='delete_<?php echo $item->customblock_id; ?>' value='<?php echo $item->customblock_id ?>' class='checkbox' value="<?php echo $item->customblock_id ?>" <?php if (empty($item->custom)) {
			echo 'DISABLED';
		  } ?> />
		  </div>
		  <div style="width:2%;">
		  <?php echo $item->customblock_id; ?>
		  </div>

<?php
		  if (!empty($item->title)) {
			$tmpBody = strip_tags($item->title);
			$title = Engine_String::strlen($tmpBody) > 20 ? Engine_String::substr($tmpBody, 0, 20) . '..' : $tmpBody;
		  } else {
			$title = '-';
		  }
?>
		  <div style="width:26%;">
			<?php echo $title; ?>
		  </div>
		  <div style="width:20%;" class="admin_table_centered">
			<?php
			if (empty($item->limitation)) {
			  echo 'None';
			} else if ($item->limitation == 1) {
			  echo $this->translate(array('Friend: %s', 'Friends: %s', $item->limitation_value), $this->locale()->toNumber($item->limitation_value));
			} else if ($item->limitation == 2) {
			  echo $this->translate(array('Signup Day: %s', 'Signup Days: %s', $item->limitation_value), $this->locale()->toNumber($item->limitation_value));
			}
			?>
  		  </div>
  		  <div style="width:20%;" class="admin_table_centered">
<?php if (empty($item->enabled)) { ?>
<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'enabled', 'id' => $item->customblock_id, 'enabled' => 1), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled0.gif', '', array('title' => $this->translate('Enable from Welcome Tab'))), array('class' => 'smoothbox')) ?>
			<?php } else {
			?>
			<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'enabled', 'id' => $item->customblock_id, 'enabled' => 0), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled1.gif', '', array('title' => $this->translate('Disable from Welcome Tab'))), array('class' => 'smoothbox')) ?>
			<?php } ?>
		  </div>

		  <div style="width:20%;" class="admin_table_centered">
			<?php
			if (!empty($item->custom)) {
			  echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'custom-block-create', 'customblock_id' => $item->customblock_id), $this->translate("edit"));
			} else {
			  echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'custom-block-creates', 'customblock_id' => $item->customblock_id), $this->translate("edit"), array('class' => 'smoothbox'));
			}

			if (!empty($item->custom)) {
			  echo ' | ';
			  echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'settings', 'action' => 'custom-block-delete', 'customblock_id' => $item->customblock_id), $this->translate("delete"), array('class' => 'smoothbox'));
			}
			?>
			  </div>
			</li>
<?php endforeach; ?>
				  </ul>
				</div>
				<br />&nbsp;
				<button type='submit' name="delete" onclick="return multiDelete()" value="delete_image"><?php echo $this->translate('Delete Selected'); ?></button>	&nbsp;&nbsp;
			<button name="order" id="order" type="submit" value="save_order" onClick="setFlag(true);"><?php echo $this->translate('Save Order'); ?></button>
		  </div>
		</form>
<?php echo $this->paginationControl($this->paginator); ?>
<?php else: ?>
			  <div class="tip">
			    <span>
<?php echo $this->translate('There are no custom block available.'); ?>
			    </span>
			  </div>
<?php endif; ?>
<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: manage.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
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
    $$('.slideshow-image-preview').addEvents({
      click : previewFile,
      mouseout : previewFile,
      mouseover : previewFile
    });
    $$('.admin_file_preview').addEvents({
      click : previewFile
    });
  });

	var currentOrder = '<?php echo $this->order ?>';
  var currentOrderDirection = '<?php echo $this->order_direction ?>';
  var changeOrder = function(order, default_direction){
    // Just change direction
    if( order == currentOrder ) {
      	$('order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
    } 
    else {
      	$('order').value = order;
      	$('order_direction').value = default_direction;
    }
    $('filter_form').submit();
  }

	function multiDelete()
	{
		return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete selected badegs?")) ?>');
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

</script>

<div class='admin_search'>
 <?php echo $this->formFilter->render($this) ?>
</div>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/back.png" class="icon" />
<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'editors', 'action' => 'manage'), $this->translate('Back to Manage Editors'), array('class'=> 'buttonlink', 'style'=> 'padding-left:0px;')) ?>
<br /><br />

<h3>
  <?php echo $this->translate('Manage Badges') ?>
</h3>

<p>
  <?php echo $this->translate("This page lists all the badges for Editors on your site. Here, you can monitor, edit and delete them.") ?>
</p><br />

<?php if( count($this->paginator->getTotalItemCount()) ): ?>	
	<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'badge', 'action' => 'create'), $this->translate('Add New Badge'), array('class'=> 'buttonlink smoothbox', 'style'=> 'background-image: url('.$this->layout()->staticBaseUrl.'application/modules/Sitereview/externals/images/badge.png);')) ?>
<br /><br />
<?php endif; ?>

<?php if( $this->paginator->getTotalItemCount() ): ?>	

	<div>
		<?php echo $this->translate(array('%s badge found.', '%s badges found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
	</div>
	<br />

<div class="admin_files_wrapper sitereview_manage_wrapper">
	<ul class="admin_files" style="max-height:inherit;">
		<li>
			<form id='multidelete_form' method="post" action="<?php echo $this->url(array('action'=>'multi-delete'));?>" onSubmit="return multiDelete()">
				<table class='admin_table admin_table_list_badge'>
			    <thead>
			      <tr>
							<th class='admin_table_short'><input onclick='selectAll();' type='checkbox' class='checkbox' /></th>
							<th style='width: 1%;' class='admin_table_centered'><a href="javascript:void(0);" onclick="javascript:changeOrder('badge_id', 'ASC');"><?php echo $this->translate('ID');?></a></th>
							<th  style='width: 10%;' align="left"><?php echo $this->translate('Badge'); ?></th>
							<th align="left"><?php echo $this->translate("Options") ?></th>
			      </tr>
			    </thead>
					<tbody>
						<?php foreach ($this->paginator as $item): $i = 0; $i++; $id = 'admin_file_' . $i;?>
							<tr>
								<td><input name='delete_<?php echo $item->badge_id;?>' type='checkbox' class='checkbox' value="<?php echo $item->badge_id ?>"/></td>
								<td class='admin_table_centered'><?php echo $item->badge_id ?></td>
			
								<td>
									<?php if(!empty($item->badge_main_id)): ?>
										<?php $main_path = Engine_Api::_()->storage()->get($item->badge_main_id, '')->getPhotoUrl();?>
										<?php if(!empty($main_path)): ?>
											<li class="admin_file admin_file_type_image" id="<?php echo $id ?>">
												<div class="slideshow-image-preview">
													<?php echo'<img src="'. $main_path .'" class="photo" width="50" />'; ?>
												</div>
												<div class="admin_file_preview admin_file_preview_image" style="display:none;">
													<?php echo '<img src="'. $main_path .'" class="photo sitereview_img_pre" />'; ?>
												</div>
											</li>
										<?php endif; ?>
									<?php endif; ?>
								</td>
			
								<td width="10%" class="admin_table_options">
									<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'badge', 'action' => 'edit', 'id' => $item->badge_id), $this->translate('edit'), array('class' => 'smoothbox')) ?>
										| 
									<?php echo $this->htmlLink(
									array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'badge', 'action' => 'delete', 'id' => $item->badge_id), $this->translate('delete'), array('class' => 'smoothbox')) ?>
								</td>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
			  </table>
				<br/>
				<div class='buttons'>
				<button type='submit'><?php echo $this->translate('Delete Selected');?></button>
				</div>
			</form>
		</li>
	</ul>	
</div>
<?php else:?>
	<div class="tip">
		<span>
			<?php echo $this->translate('You have not created any badges yet. Get started by ').$this->htmlLink(array(
							'route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'badge', 'action' => 'create'
						), $this->translate('creating'), array('class' => 'smoothbox')). $this->translate(" one."); ?>
		</span>
  </div>	
<?php endif; ?>
<?php echo $this->paginationControl($this->paginator); ?>
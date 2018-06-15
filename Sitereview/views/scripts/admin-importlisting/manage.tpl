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
	var importfile_id;
	function startImporting(file_id) {
	  var confirmation =  confirm('If you want import profile photos also with the listing import then use "Upload Photos" link for photos." \n\n If you do not want upload photos or already uploaded then click on "Ok" button otherwise "click" on Cancel button.');
	  if(confirmation == true) {
			Smoothbox.open($('startImporting').innerHTML);
			importfile_id = file_id;
			en4.core.request.send(new Request({
				url : en4.core.baseUrl+'admin/sitereview/importlisting/data-import?importfile_id='+importfile_id,
				method: 'get',
				data : {
					//'format' : 'json',
				},

				onSuccess : function(responseJSON) {
					parent.window.location.reload();
					parent.Smoothbox.close();
						
					}
			}))
		}
	}
  
  function multiImport() {
    
    var confirmation_check = confirm('<?php echo $this->string()->escapeJavascript($this->translate("Only files will be import which are in pending status from selected files.")) ?>');
    
    if(confirmation_check == false) { return; }
    //importfile_ids = $('multidelete_form').toQueryString();
    //alert(en4.core.baseUrl+'admin/sitereview/importlisting/data-import?multi_import=1&'+importfile_ids);
    Smoothbox.open($('startImporting').innerHTML);
		importfile_ids = $('multidelete_form').toQueryString();
		en4.core.request.send(new Request({
			url : en4.core.baseUrl+'admin/sitereview/importlisting/data-import?multi_import=1&'+importfile_ids,
			method: 'get',
			data : {
				//'format' : 'html'
			},
			onSuccess : function(responseJSON) {
				parent.window.location.reload();
				parent.Smoothbox.close();
					
				}
		}))
  }  

	function stopImoprt() {

		var request = new Request.JSON({
			'url' : en4.core.baseUrl+'admin/sitereview/importlisting/stop',
			'data' : {
								'format' : 'json'
							},
			onSuccess : function(responseJSON) {
				parent.window.location.reload();
				parent.Smoothbox.close();
			}
		});
		request.send();
	}

	function multiDelete()
	{
		if(confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure that you want to delete the selected file entries ? These will not be recoverable after being deleted. Note that deleting them will also delete the corresponding entries which were going to be used to import the Listings from those files.")) ?>')) {
      $('multidelete_form').submit();
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
  
	var currentOrder = '<?php echo $this->order ?>';
  var currentOrderDirection = '<?php echo $this->order_direction ?>';
  var changeOrder = function(order, default_direction){

    if( order == currentOrder ) {
      $('order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
    } else {
      $('order').value = order;
      $('order_direction').value = default_direction;
    }
    $('filter_form').submit();
  }

</script>

<div id="startImporting" style="display:none;">
	<center class="bold">
		<?php echo $this->translate("Import listings..."); ?>
	</center>
	<center class="mtop10">
		<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/loader.gif" alt="Importing sitereviews" />
	</center>
	<br />
	<center><button name="submit" id="submit" type="submit" onclick='stopImoprt();'><?php echo $this->translate("Stop");?></button></center>
</div>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<?php if (count($this->navigation)): ?>
  <div class='seaocore_admin_tabs'> <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?> </div>
<?php endif; ?>

<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>

<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'index'), $this->translate('Import a new file'), array('class' => 'buttonlink icon_sitereview_admin_import')) ?>

<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'log', 'action' => 'index'), $this->translate('Import History'), array('class' => 'buttonlink icon_sitereviews_log')) ?><br/><br/>

<?php if($this->paginator->getTotalItemCount()): ?>	

	<h3><?php echo $this->translate("Manage CSV Import Files"); ?></h3>
	<p class="form-description"><?php echo $this->translate('This page contains all the CSV files uploaded by you for importing Listings from them. You can start, stop, rollback and delete the import corresponding to each file now. Rollback will delete all the listings imported from that file. Delete will only delete those entries which were going to be used to import the corresponding Listings from that file and also delete the entry of that file from here. Upload the folder containing profile pictures for the listings uploaded via the csv file by using the ‘Upload Photos” link. Below are the meanings of status for the Files uploaded:'); ?></p> 

	<ul class="importlisting_form_sitereview">

		<li>
			<?php echo $this->translate("Pending: You have not started the listing imports from this file. Click on 'start' link and start importing.");?>
		</li>

		<li>
			<?php echo $this->translate("Running: You have started the listing imports from this file.");?>
		</li>

		<li>
			<?php echo $this->translate("Stopped: You have stopped the listing imports from this file. You can continue it anytime from the same point.");?>
		</li>

		<li>
			<?php echo $this->translate("Completed: Listing imports has been done successfully from this file.");?>
		</li>

	</ul>
	<br />

	<div class="admin_files_sitereviews">
		<?php $sitereviewInfo = $this->paginator->getPages(); ?>
		<?php echo $this->translate("Showing ");?><?php echo $sitereviewInfo->firstItemNumber ?>-<?php echo $sitereviewInfo->lastItemNumber ?><?php echo $this->translate(" of "); ?><?php echo $sitereviewInfo->totalItemCount ?><?php echo $this->translate(" files.")?>
	</div>

	<form id='multidelete_form' name='multidelete_form' method="post" action="<?php echo $this->url(array('action' => 'multi-delete'));?>" onSubmit="return false;">
		<table class='admin_table' width='100%'>
			<thead>
				<tr>
					<th width="1%" align="left"><input onclick="selectAll()" type='checkbox' class='checkbox'></th>
					<th width="1%" align="left"><a href="javascript:void(0);" onclick="javascript:changeOrder('importfile_id', 'ASC');"><?php echo $this->translate('Id'); ?></a></th>
					<th width="30%" align="left"><a href="javascript:void(0);" onclick="javascript:changeOrder('filename', 'ASC');"><?php echo $this->translate('File Name'); ?></a></th>
					<th width="15%" align="left"><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'ASC');"><?php echo $this->translate('Upload Date / Time'); ?></a></th>							
					<th width="10%" align="left"><a href="javascript:void(0);" onclick="javascript:changeOrder('status', 'ASC');"><?php echo $this->translate('Status'); ?></a></th>
					<th width="20%" align="left"><?php echo $this->translate('Options'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach( $this->paginator as $item ):?>
				<tr>
					<td width="1%">
            <?php if($item->status == 'Running'):  ?>
              <input name='d_<?php echo $item->importfile_id;?>' type='checkbox' disabled="disabled" class='checkbox' value="<?php echo $item->importfile_id ?>"/>           
            <?php else: ?>
              <input name='d_<?php echo $item->importfile_id;?>' type='checkbox' class='checkbox' value="<?php echo $item->importfile_id ?>"/>
            <?php endif; ?>
					</td>
					<td width="1%">
						<?php echo $item->importfile_id ?>
					</td>
			
					<td width="30%">
						<span title='<?php echo $item->filename; ?>'>
							<?php $widget_title = strip_tags($item->filename);
							$widget_title = Engine_String::strlen($widget_title) > 50 ? Engine_String::substr($widget_title, 0, 50) . '..' : $widget_title;?>
							<?php echo $widget_title ?>
						</span>
					</td>

					<td width="15%">
						<?php echo $item->creation_date ?>
					</td>
						
					<td width="10%">
						<span title='<?php echo $item->status; ?>'>
							<?php if($item->status == 'Pending'):?>
								<?php echo $this->translate("Pending"); ?>
							<?php elseif($item->status == 'Running'):?>
								<?php echo $this->translate("Running"); ?>
							<?php elseif($item->status == 'Completed'):?>
								<?php echo $this->translate("Completed"); ?>
							<?php elseif($item->status == 'Stopped'):?>
								<?php echo $this->translate("Stopped"); ?>
							<?php endif; ?>
						</span>
					</td>

					<td width="20%" style="text-align:left;">
						<?php if($item->status != 'Running'): ?>
							<?php if($item->status != 'Completed' && empty($this->runningSomeImport)): ?>
								<a href="javascript:void(0);" onclick='startImporting("<?php echo $item->importfile_id; ?>");'><?php echo $this->translate('Start') ?></a>
								|
							<?php endif; ?>
							<?php if($item->status != 'Pending'): ?>
								<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'rollback', 'importfile_id' => $item->importfile_id), $this->translate('Rollback'), array('class' => 'smoothbox')) ?>
								|
							<?php endif; ?>
							<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'delete', 'importfile_id' => $item->importfile_id), $this->translate('Delete'), array('class' => 'smoothbox')) ?>
						<?php else: ?>
							<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'stop', 'importfile_id' => $item->importfile_id, 'forceStop' => 1), $this->translate('Stop')) ?>
						<?php endif;?>|
						<a href="javascript:void(0);" onclick="javascript:uploadphoto('<?php echo $item->importfile_id;?>');"><?php echo $this->translate('Upload Photos'); ?></a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<br /><?php echo $this->paginationControl($this->paginator, null, null); ?>

		<br />
		&nbsp;<button type='submit' name="delete" onclick="return multiDelete()"><?php echo $this->translate('Delete Selected'); ?></button>	&nbsp;&nbsp;&nbsp;
    
      <button type='submit' name="multi_import" onclick="return multiImport()" value="1"><?php echo $this->translate('Import Selected'); ?></button>
	</form>
  
<?php else:?>
	<div class="tip">
		<span>
			<?php echo $this->translate('You have not imported any file yet.'); ?>
		</span>
	</div>	
<?php endif; ?>

<script type="text/javascript" >
  function uploadphoto(importfile_id) {
    var zip_enabled = '<?php echo extension_loaded('zip');?>';
    if(zip_enabled != '') {
      window.location.href = en4.core.baseUrl+'admin/sitereview/importlisting/upload-photo/importfile_id/'+importfile_id;
    }
    else {
      alert('You have not enabled the php_zip extension on your server. Please enable it to upload photos.');
    }
    
  }
</script>
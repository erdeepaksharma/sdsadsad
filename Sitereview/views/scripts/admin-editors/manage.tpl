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

    if( order == currentOrder ) {
      $('order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
    } else {
      $('order').value = order;
      $('order_direction').value = default_direction;
    }
    $('filter_form').submit();
  }

	function multiDelete()
	{
		return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to remove selected Editors from every listing types in which this user has been added as editor?")) ?>');
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

<script type="text/javascript">
  var changeListingType =function(listingtype_id){
    window.location.href= en4.core.baseUrl+'admin/sitereview/editors/manage/listingtype_id/'+listingtype_id;
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
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>

<div class='clear seaocore_settings_form'>
	<div class='settings'>
		<form id='multidelete_form' method="post" action="<?php echo $this->url(array('action'=>'multi-delete'));?>" onSubmit="return multiDelete()">
      <div>
        <h3><?php echo $this->translate("Manage Editors") ?> </h3>

        <?php $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes(); ?>
        <?php if(Count($listingTypes) > 1): ?>
        
          <p class="form-description">
            <?php echo $this->translate('Editor reviews are helpful in displaying accurate, trusted and unbiased reviews that will showcase listings\' (for example: listings of hotels, products, etc.) quality, features, and value. This will bring more user engagement to your site, as editor reviews provide reviews from expert people (editors) on the listings of their interest.<br /><br />Below, you can add new editor by using "Add New Editor" link. You can also edit and remove editors added by you by clicking on the links for each. From the "Editor For" field below, you can select listing types for which editors will be allowed to write editor reviews. You can also make an editor as Super Editor, who will be assigned all the reviews if other editors delete their user accounts from your site. Super editor will be allowed to write editor reviews for all the listing types.<br /><br /><b>Badge:</b> You can assign badge to the editors. You can add / manage editor badges by clicking on "Manage Editor Badges" link.<br /><br /><b>Note:</b> You can not remove Super Editor from editor of your site. To do so, you have to first make some other editor as Super Editor. You can disable editor reviews by using "Allow Reviews" field from the "Manage Listing Types" section of this plugin.') ?>
          </p>        
        
          <div>
            <label>
              <b><?php echo $this->translate("Listing Type:") ?></b>
            </label>
            <select onchange="changeListingType($(this).value)" class="sitereview_cat_select" name="listingtype_id">    
              <option value="0">All Types</option>            
              <?php $listinTypesArray[0] = "All Types"; ?>
              <?php foreach ($listingTypes as $listingType): ?>
                <?php $listinTypesArray[$listingType->listingtype_id] = $listingType->title_plural; ?>
                <option value="<?php echo $listingType->listingtype_id;?>" <?php if( $this->listingtype_id == $listingType->listingtype_id) echo "selected";?>><?php echo $this->translate($listingType->title_plural);?>
                </option>
              <?php endforeach; ?>
            </select>
          </div><br />
        <?php else: ?>  
          <p class="form-description">
            <?php echo $this->translate('Editor reviews are helpful in displaying accurate, trusted and unbiased reviews that will showcase listings\' (for example: listings of hotels, products, etc.) quality, features, and value. This will bring more user engagement to your site, as editor reviews provide reviews from expert people (editors) on the listings of their interest.<br />Below, you can add new editor by using "Add New Editor" link. You can also edit and remove editors added by you by clicking on the links for each. You can also make an editor as Super Editor, who will be assigned all the reviews if other editors delete their user accounts from your site.<br /><br /><b>Badge:</b> You can assign badge to the editors. You can add / manage editor badges by clicking on "Manage Editor Badges" link.<br /><br /><b>Note:</b> You can not remove Super Editor from editor of your site. To do so, you have to first make some other editor as Super Editor. You can disable editor reviews by using "Allow Reviews" field from the "Manage Listing Types" section of this plugin.') ?>
          </p>            
        <?php endif; ?>
        
				<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'editors', 'action' => 'create'), $this->translate('Add New Editor'), array('class' => 'buttonlink seaocore_icon_add')) ?> <t/><t/>
        
				<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'badge', 'action' => 'manage'), $this->translate('Manage Editor Badges'), array('class' => 'buttonlink', 'style'=>'background-image: url('.$this->layout()->staticBaseUrl.'application/modules/Sitereview/externals/images/badge.png);')) ?> <br /><br />        

        <?php if(Count($this->paginator) > 0):?>

					<table class='admin_table' width="100%">
						<thead>
							<tr>
<!--								<th style='width: 1%;' class='admin_table_short'><input onclick="selectAll()" type='checkbox' class='checkbox'></th>-->
								<th width="1%" align="left"><?php echo $this->translate("Editor Photo") ?></th>

								<?php $class = ( $this->order == 'engine4_users.username' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
								<th width="1%" align="left" class="<?php echo $class ?>"><?php echo $this->translate("User Name") ?></th>
                
                <?php $class = ( $this->order == 'engine4_sitereview_editors.designation' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->order_direction) : '' ) ?>
								<th width="1%" align="left" class="<?php echo $class ?>"><?php echo $this->translate("Designation") ?></th>  
                
                <th width="1%" align="left"><?php echo $this->translate("About Editor") ?></th>
                
                <th width="1%" align="center"><?php echo $this->translate("Reviews") ?></th>
                
                <th width="%" align="left"><?php echo $this->translate("Badge") ?></th>
                
                <?php if($this->listingTypeCount > 1): ?>
                  <th width="%" align="left"><?php echo $this->translate("Editors For") ?></th>
                <?php endif; ?>
                
                <th width="1%" align="left"><?php echo $this->translate("Super Editor") ?></th>

								<th width="1%" align="left"><?php echo $this->translate("Options") ?></th>
							</tr>
						</thead>					
						<tbody>
							<?php foreach ($this->paginator as $editor): $i = 0; $i++; $id = 'admin_file_' . $i; ?>
								<?php $username = $editor->username ? $editor->username : $editor->displayname;?>
								<tr>
									<td class='admin_table_user'><?php echo $this->htmlLink(array('route' => 'sitereview_review_editor_profile', 'username' => $username, 'user_id' => $editor->user_id), $this->itemPhoto($editor, 'thumb.icon'), array('target' => '_blank', 'title' => $editor->getTitle())) ?></td>					
                  
									<td class='admin_table_user'><?php echo $this->htmlLink(array('route' => 'sitereview_review_editor_profile', 'username' => $username, 'user_id' => $editor->user_id), $editor->getTitle(), array('target' => '_blank')) ?></td>	
                  
                  <?php if($editor->designation): ?>
                    <td class='admin_table_user'><?php echo $editor->designation; ?></td>
                  <?php else: ?>
                    <td class='admin_table_user'>---</td>
                  <?php endif; ?>
                    
                  <?php if($editor->details): ?>
                    <td class='admin_table_user'><span title="<?php echo $editor->details; ?>"><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($editor->details, 90); ?></span></td>
                  <?php else: ?>
                    <td class='admin_table_user'>---</td>
                  <?php endif; ?>  
                  <?php 
										$params = array();
										$params['owner_id'] = $editor->user_id;
										$params['type'] = 'editor';
                  ?>  
                  <td class='admin_table_centered'><?php echo Engine_Api::_()->getDbtable('reviews', 'sitereview')->totalReviews($params); ?></td>  
                    
                  <td>
                    <?php if(!empty($editor->badge_id)): ?>
                      <?php $badge = Engine_Api::_()->getItem('sitereview_badge', $editor->badge_id); ?>
                      <?php if(isset($badge->badge_main_id) && !empty($badge->badge_main_id)): ?>
                        <?php $main_path = Engine_Api::_()->storage()->get($badge->badge_main_id, '')->getPhotoUrl();?>
                        <?php if(!empty($main_path)): ?>
                          <div class="admin_file admin_file_type_image" id="<?php echo $id ?>">
                            <div class="slideshow-image-preview">
                              
                              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'badge', 'action' => 'assign-badge', 'editor_id'=> $editor->editor_id), '<img src="'. $main_path .'" class="photo" width="50" />', array('class' => 'smoothbox', 'title' => 'Click to change or remove badge')) ?>
                              
                            </div>
                          </div>
                        <?php endif; ?>
                      <?php endif; ?>    
                    <?php else: ?>
                      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'badge', 'action' => 'assign-badge', 'editor_id'=> $editor->editor_id), $this->translate('Assign'), array('class' => 'smoothbox', 'title' => 'Click to assign badge')) ?>
                    <?php endif; ?>
                  </td>          
                  
                  <?php if($this->listingTypeCount > 1): ?>
                    <td>
                      <?php $getDetails = $this->tableEditor->getEditorDetails($editor->user_id); ?>
                      <?php if(($getCount = Count($getDetails)) > 0):  ?>                   
	                      <?php foreach($getDetails as $getDetail): ?>
	                      	<div class="clr">
	                        	<?php echo $this->htmlLink(array('route' => 'sitereview_general_listtype_'.$getDetail->listingtype_id), $getDetail->title_plural, array('target' => '_blank')); ?><?php if(empty($editor->super_editor)): ?>&nbsp;<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'editors', 'action' => 'delete', 'editor_id'=> $editor->editor_id, 'listingtype_id' => $getDetail->listingtype_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/cross.png', '', array('title' => $this->translate('Remove from this listing type'))), array('class' => 'smoothbox')) ?><?php endif; ?>
	                        </div>
	                      <?php endforeach;?>

                        <?php if($getCount < $this->listingTypeCount): ?>
                          <div class="clr"><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'editors', 'action' => 'add', 'editor_id' => $editor->editor_id), $this->translate('Add More'), array('class' => 'smoothbox sr_add_more')); ?></div> 
                        <?php endif; ?>
                      <?php endif; ?>                    
                    </td>
                  <?php endif; ?>
                  
                  <?php if($editor->super_editor == 1):?>
                    <td align="center" class="admin_table_centered"> 
                      <?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/approved.gif'); ?></td>
                  <?php else: ?>
                    <td align="center" class="admin_table_centered"> <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'editors', 'action' => 'super-editor', 'editor_id' => $editor->editor_id, 'super_editor' => 0), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/disapproved.gif', '', array('title' => $this->translate('Make Super Editor'))), array('class' => 'smoothbox')) ?></td>
                  <?php endif; ?>
                  
									<td align="left">
                    
                    <?php echo $this->htmlLink(array('route' => 'sitereview_review_editor_profile', 'username' => $username, 'user_id' => $editor->user_id), $this->translate("Profile"), array('target' => '_blank')) ?> | 

                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'editors', 'action' => 'edit', 'editor_id'=> $editor->editor_id), $this->translate('Edit'), array('class' => 'smoothbox')) ?> | 
                    
                  <?php if($editor->super_editor == 1):?>  
                    <span><?php echo $this->translate('Remove'); ?></span>
                  <?php else: ?>                
                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'editors', 'action' => 'delete', 'editor_id'=> $editor->editor_id), $this->translate('Remove'), array('class' => 'smoothbox',)) ?>
                  <?php endif; ?>                    

									</td>
								</tr>
							<?php  endforeach; ?>							
						</tbody>
					</table>

					<br />

				<?php else:?>
					<div class="tip">
						<span><?php echo $this->translate("There are currently no editor has been added by site admin.") ?></span>
					</div>
				<?php endif;?>
			</div>
		</form>
	</div>
</div>
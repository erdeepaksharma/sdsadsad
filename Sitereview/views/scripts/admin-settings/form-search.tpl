<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: form-search.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<script type="text/javascript">
  var changeListingType =function(listingtype_id){
    window.location.href= en4.core.baseUrl+'admin/sitereview/settings/form-search/listingtype_id/'+listingtype_id;
  }
</script>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>
<?php if( count($this->navigation) ): ?>
	<div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
	</div>
<?php endif; ?>


<h3>
  <?php echo $this->translate("Search Form Settings") ?>
</h3>

<p>
  <?php echo $this->translate('This page lists all the fields which will be displayed to the users in the search form widget at "Listings Home", "Browse Listings" and "My Listings". Below, you can set the sequence of items in the order in which they should appear to users in the search form. To do so, drag-and-drop the items vertically and click on "Save Order" to save the sequence. You can also hide/display the individual fields in this search form.') ?>
</p>

<br />

<?php $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes(); ?>
<?php if(Count($listingTypes) > 1): ?>
  <div>
    <label>
      <b><?php echo $this->translate("Listing Type:") ?></b>
    </label>
    <select onchange="changeListingType($(this).value)" class="sitereview_cat_select" name="listingtype_id">            
      <?php foreach ($listingTypes as $listingType): ?>
        <?php $listinTypesArray[$listingType->listingtype_id] = $listingType->title_plural; ?>
        <option value="<?php echo $listingType->listingtype_id;?>" <?php if( $this->listingtype_id == $listingType->listingtype_id) echo "selected";?>><?php echo $this->translate($listingType->title_plural);?>
        </option>
      <?php endforeach; ?>
    </select>
  </div><br />  
<?php endif; ?>
 
<form id='saveorder_form' method='post' action='<?php echo $this->url(array('action' => 'form-search')) ?>' style="overflow:hidden;">
	<input type='hidden'  name='order' id="order" value=''/>
	<div class="seaocore_admin_order_list" style="width:50%;">
		<div class="list_head">     
			<div style="width:45%;">
				<?php echo $this->translate("Item Label") ?>
			</div>
			<div style="width:45%;" class="admin_table_centered">
				<?php echo $this->translate("Hide / Display") ?>
			</div>
		</div>
		<div id='order-element'>
			<ul>
				<?php foreach ( $this->searchForm  as $item) :?>
					<li>
						<input type='hidden'  name='order[]' value='<?php echo $item->searchformsetting_id; ?>'>
						<div style="width:45%;">
							<?php echo $this->translate($item->label) ?>
						</div>
						<div style="width:45%;" class="admin_table_centered">
							<?php if($item->display == 1):?>
								<?php   echo $this->htmlLink(array('route' => 'default', 'module' => 'sitereview', 'controller' => 'admin-settings', 'action' => 'diplay-form', 'id' => $item->searchformsetting_id,  'name' => $item->name, 'display' => 0, 'listingtype_id' => $this->listingtype_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/approved.gif', '', array('title' => $this->translate('Hide')))); ?>
							<?php else: ?>
								<?php   echo $this->htmlLink(array('route' => 'default', 'module' => 'sitereview', 'controller' => 'admin-settings', 'action' => 'diplay-form', 'id' => $item->searchformsetting_id,  'name' => $item->name,  'display' => 1, 'listingtype_id' => $this->listingtype_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/disapproved.gif', '', array('title' => $this->translate('Display')))); ?>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
	    </ul>
    </div>
  </div>
</form>
<br />

<button onClick="javascript:saveOrder(true);" type='submit' class="clear">
	<?php echo $this->translate("Save Order") ?>
</button>

<script type="text/javascript">

  var saveFlag=false;
  var origOrder;
	var changeOptionsFlag = false;

	function saveOrder(value){
		saveFlag=value;
		var finalOrder = [];
		var li = $('order-element').getElementsByTagName('li');
		for (i = 1; i <= li.length; i++)
			finalOrder.push(li[i]);
		$("order").value=finalOrder;
		$('saveorder_form').submit();
	}

  window.addEvent('domready', function(){
			var initSitereview = [];
			var li = $('order-element').getElementsByTagName('li');
			for (i = 1; i <= li.length; i++)
					initSitereview.push(li[i]);
			origOrder = initSitereview;
			var temp_array = $('order-element').getElementsByTagName('ul');
			temp_array.innerHTML = initSitereview;
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
	}
</script>
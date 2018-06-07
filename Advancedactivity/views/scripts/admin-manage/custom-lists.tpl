<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: custom-lists.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<?php include_once APPLICATION_PATH .
'/application/modules/Advancedactivity/views/scripts/admin-manage/navigation.tpl'; ?>
<h3>
    <?php echo $this->translate("Custom Lists"); ?>
</h3>
<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.customlist.filtering', 1)):?>
<div class="tip">
  <span>
    <?php echo "You have disabled \"Custom Lists Filtering\" from the \"General\" tab of this Manage Lists section. To enable it, do so "; ?><a href="<?php echo $this->url(array("action"=>"index")); ?>"><?php echo "from here";?></a>.
  </span>
</div>
<?php endif; ?>
<p>
   <?php echo $this->translate('Below, you can choose and order the content types over which users should be able to create custom lists to filter their home page activity feeds. Drag and drop items to arrange their sequence. You can assign a higher positioning to content types that are more important for your community. You can also add a new content type for custom lists below. You can add content type from any 3rd-party plugin also for custom lists for filtering. <br />For every custom list, users can compose them from multiple items of different content types from the ones selected by you. It is recommended that for custom lists, you choose content types in which content have their own updates / activity feed, like Groups, Events, Directory Items / Pages, Listings, Users, etc. <br /> With their custom lists, users can choose to easily see updates from their favorite content items and friends that they are interested in. For example, a user can create a custom list called "Football" in which he can add a Group related to Football, a Football Event, a Page of Football Club, Friends he plays Football with, etc, so that he can receive updates related to Football in that list.'); ?>
</p>
<br />
<div>
  <a href="<?php echo $this->url(array('action' =>'add-custom')) ?>" class="buttonlink seaocore_icon_add" title="<?php echo $this->translate('Add a Content Type');?>"><?php echo $this->translate('Add a Content Type');?></a>
</div>
<br />
<div class="seaocore_admin_order_list">
	<div class="list_head">
    <div style="width:20%">
	    <?php echo $this->translate("Content Module");?>
	  </div>
	  <div style="width:20%">
	    <?php echo $this->translate("Resource Type");?>
	  </div>
	   <div style="width:20%">
	    <?php echo $this->translate("Title");?>
	  </div>	  
    <div style="width:20%" class="admin_table_centered">
	    <?php echo $this->translate("Enabled");?>
	  </div>
	  <div style="width:10%">
	    <?php echo $this->translate("Options");?>
	  </div>
	</div>
  <form id='saveorder_form' method='post' action='<?php echo $this->url(array('action' =>'update-order')) ?>'>
  	<input type='hidden'  name='order' id="order" value=''/>
    <input type='hidden'  name='item_type' id="item_type" value='advancedactivity_customtype'/>
    <div id='order-element'>
    	<ul>
      	<?php foreach ($this->customslist as $item) : ?>
        	<li>
	          <input type='hidden'  name='order[]' value='<?php echo $item->customtype_id; ?>'>
	          <div style="width:20%;" class='admin_table_bold'>
	            <?php echo $item->module_title; ?>
              <?php  if(Engine_Api::_()->hasModuleBootstrap('sitereview')  && $item->module_name=='sitereviewlistingtype'):?>
              &nbsp; (<?php echo Engine_Api::_()->getItemByGuid(str_replace('sitereview_listing_listtype','sitereview_listingtype',$item->resource_type))->getTitle(true);?>)
              <?php endif;?>
	          </div>
             <div style="width:20%;" class='admin_table_bold'>
	            <?php echo $item->resource_type; ?>
	          </div>
             <div style="width:20%;" class='admin_table_bold'>
	            <?php echo $this->translate($item->resource_title); ?>
	          </div>          
	          <div style="width:20%;" class='admin_table_centered'>
	            <?php echo ( $item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' =>
'advancedactivity', 'controller' => 'manage', 'action' => 'enabled-custom-list', 'resource_type' =>
$item->resource_type), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled1.gif',
'', array('title' => $this->translate('Disable from Custom Lists'))), array())  :
$this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'manage',
'action' => 'enabled-custom-list', 'resource_type' => $item->resource_type),
$this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled0.gif', '', array('title' =>
$this->translate('Enable from Custom Lists')))) ) ?>
	          </div>
	          <div style="width:10%;">          
	            <a href='<?php echo $this->url(array('action' => 'edit-custom','module_name'=>$item->module_name, 'resource_type' => $item->resource_type)) ?>'>
	              <?php echo $this->translate("Edit") ?>
	            </a>
              <?php if(empty($item->default)):?>
              | <a href='<?php echo $this->url(array('action' => 'delete-custom','resource_type' => $item->resource_type)) ?>' class="smoothbox">
	              <?php echo $this->translate("Delete") ?>
	            </a>
              <?php endif; ?>
	      		</div>
	      	</li>
	    	<?php endforeach; ?>
   		</ul>
  	</div>
	</form>
  <br />
   <button onClick="javascript:saveOrder(true);" type='submit'>
    <?php echo $this->translate("Save Order") ?>
  </button>
</div>
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
				var answer=confirm("<?php echo $this->string()->escapeJavascript($this->translate("A change in the order of the tabs has been detected. If you click Cancel, all unsaved changes will be lost. Click OK to save change and proceed.")); ?>");
				if(answer) {
          $('order').value=finalOrder;
					$('saveorder_form').submit();

				}
			}
		}
</script>
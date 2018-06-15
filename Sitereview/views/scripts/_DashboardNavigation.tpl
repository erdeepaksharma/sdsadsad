<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _DashboardNavigation.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php
$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css') ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview_dashboard.css');
  $this->headScript()
    ->appendFile($this->layout()->staticBaseUrl. 'externals/moolasso/Lasso.js')
    ->appendFile($this->layout()->staticBaseUrl. 'externals/moolasso/Lasso.Crop.js');
      $this->headScript()
          ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/core.js')->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/seaomooscroll/SEAOMooHorizontalScrollBar.js')
          ;
?>

<?php
$isEnabledPackage = Engine_Api::_()->sitereview()->hasPackageEnable();
$sitereview = $this->sitereview;
$viewer = Engine_Api::_()->user()->getViewer();
$this->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
$listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);
$listing_singular_upper = strtoupper($listingType->title_singular);
$listing_singular_lower = strtolower($listingType->title_singular);
$listing_singular_upfirst = ucfirst($listingType->title_singular);

$allowStyle = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('sitereview_listing', $viewer->level_id, "style_listtype_$listingtype_id");

$allowOverview = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "overview_listtype_$listingtype_id");

$allowPackageOverview = 1;

$allowCreation = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "create_listtype_$listingtype_id");

$allowEdit = $this->sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id);

$allowVideoUpload = Engine_Api::_()->sitereview()->allowVideo($this->sitereview, $viewer);

if($isEnabledPackage) {
	if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "photo") && Engine_Api::_()->authorization()->isAllowed($this->sitereview, $viewer, "photo_listtype_$listingtype_id"))
	$allowPhotoUpload = 1;
	else 
	$allowPhotoUpload = 0;
	
	if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "map"))
	$canAllowMap = 1;
	else
	$canAllowMap = 0;
	
	if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "overview")) 
	$allowPackageOverview = 1;
	else
	$allowPackageOverview = 0;
}
else {
	$allowPhotoUpload = Engine_Api::_()->authorization()->isAllowed($this->sitereview, $viewer, "photo_listtype_$listingtype_id");
	$canAllowMap = 1;
}

$allowContactDetailsUpload = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "contact_listtype_$listingtype_id");

$allowMetaKeywords = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "metakeyword_listtype_$listingtype_id");

$this->title = ucfirst($listingType->title_plural);
$params['listing_type_title'] = $this->title;
$params['dashboard'] = $this->translate('Dashboard');
//SET META TITLE
Engine_Api::_()->sitereview()->setMetaTitles($params);
$crowdfundingEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitecrowdfunding'); 
?>
<?php if(!Zend_Controller_Front::getInstance()->getRequest()->getParam('isajax')):?>
<?php $this->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_main_listtype_$listingtype_id");
include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/navigation_views.tpl'; ?>
<?php endif;?>

<?php if($isEnabledPackage):?>
<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereviewpaidlisting/externals/styles/style_sitereview_package.css');?>
<?php endif;?>
<?php $theme_table = Engine_Api::_()->getDbtable('themes', 'core'); 
            $activated = $theme_table ->select()
                                        ->from($theme_table->info('name'), 'name')
                                        ->where('active =?', 1)
                                        ->query()
                                        ->fetchColumn();?>
<div class="layout_middle <?php if(Engine_Api::_()->hasModuleBootstrap('spectacular') && $activated == 'spectacular'):?> spectacular_dashboard <?php elseif(Engine_Api::_()->hasModuleBootstrap('captivate') && $activated == 'captivate'): ?> captivate_dashboard <?php endif;?>">
<div class='seaocore_db_tabs'>
  <ul>
    <?php if (!empty($allowEdit)): ?>
      <li>
				<?php $url = $this->url(array('action' => 'edit', 'listing_id' => $this->sitereview->listing_id), "sitereview_specific_listtype_$listingtype_id", true); ?>
        <a class="<?php echo ($this->TabActive == "edit") ? 'selected' : '' ?>" href='<?php echo $this->url(array('action' => 'edit', 'listing_id' => $this->sitereview->listing_id), "sitereview_specific_listtype_$listingtype_id", true) ?>' ><?php echo $this->translate('Edit Info'); ?></a>
      </li>
      
      <?php if ($allowOverview && !empty($listingType->overview) && $allowPackageOverview): ?>
        <li>
          <a class="<?php echo ($this->TabActive == "overview") ? 'selected' : '' ?>" href='<?php echo $this->url(array('action' => 'overview', 'listing_id' => $this->sitereview->listing_id), "sitereview_specific_listtype_$listingtype_id", true) ?>' ><?php echo $this->translate("DASHBOARD_".$listing_singular_upper."_OVERVIEW"); ?></a>
        </li>
      <?php endif; ?>      

     <?php if (!empty($allowPhotoUpload) && ($listingType->photo_type == 'listing')): ?>
      <li>
				<?php $url = $this->url(array('action' => 'change-photo', 'listing_id' => $this->sitereview->listing_id), "sitereview_dashboard_listtype_$listingtype_id", true) ?>
        <a class="<?php echo ($this->TabActive == "profilepicture") ? 'selected' : '' ?>" href='javascript:void(0);' onclick="showAjaxBasedContent('<?php echo $url;?>')" ><?php echo $this->translate('Profile Picture'); ?></a>
      </li>
      <?php endif; ?>   

      <?php if($allowContactDetailsUpload && !empty($listingType->contact_detail)):?>
        <li>

				<?php $url = $this->url(array('action' => 'contact', 'listing_id' => $this->sitereview->listing_id), "sitereview_dashboard_listtype_$listingtype_id", true) ?>

          <a class="<?php echo ($this->TabActive == "contactdetails") ? 'selected' : '' ?>" href='javascript:void(0);' onclick="showAjaxBasedContent('<?php echo $url;?>')" ><?php echo $this->translate('Contact Details'); ?></a>
        </li>
      <?php endif; ?>     
        
      <?php if (Engine_Api::_()->sitereview()->enableLocation($listingtype_id) && $canAllowMap): ?>
        <li>
          <a class="<?php echo ($this->TabActive == "location") ? 'selected' : '' ?>" href='<?php echo $this->url(array('action' => 'editlocation', 'listing_id' => $this->sitereview->listing_id), "sitereview_specific_listtype_$listingtype_id", true) ?>' ><?php echo $this->translate('Location'); ?></a>
        </li>
      <?php endif; ?>        
        
      <?php if ($allowPhotoUpload): ?>
        <li>
				  <?php $url = $this->url(array('listing_id' => $this->sitereview->listing_id), "sitereview_albumspecific_listtype_$listingtype_id", true) ?>
          <a class="<?php echo ($this->TabActive == "photo") ? 'selected' : '' ?>" href='javascript:void(0);' onclick="showAjaxBasedContent('<?php echo $url;?>')" ><?php echo $this->translate('Photos'); ?></a>
        </li>
      <?php endif; ?>

      <?php if ($allowVideoUpload): ?>
        <li>
          <?php $url = $this->url(array('listing_id' => $this->sitereview->listing_id), "sitereview_videospecific_listtype_$listingtype_id", true) ?>
          <a class="<?php echo ($this->TabActive == "video") ? 'selected' : '' ?>" href='javascript:void(0);' onclick="showAjaxBasedContent('<?php echo $url;?>')" ><?php echo $this->translate('Videos'); ?></a>
        </li>
      <?php endif; ?>  

      <?php if ($crowdfundingEnable && Engine_Api::_()->hasModuleBootstrap('sitecrowdfundingintegration') && Engine_Api::_()->getDbtable('modules', 'sitecrowdfunding')->getIntegratedModules(array('enabled' => 1, 'item_type' => "sitereview_listing_$listingtype_id", 'item_module' => 'sitereview'))): ?> 
        <?php 
        //IF ADMIN HAVE SELECTED ANY PROJECT FOR LISTING PROFILE THAN DO NOT SHOW THE PROJECTS TAB OF DASHBAORD
        $adminSelectedProject = Engine_Api::_()->sitecrowdfunding()->adminSelectedProject("sitereview_index_view_listtype_".$sitereview->listingtype_id);
        ?>
        <?php if(empty($adminSelectedProject)): ?>
          <li>
            <?php $url = $this->url(array('action' => 'choose-project','listing_id' => $this->sitereview->listing_id), "sitereview_dashboard_listtype_$listingtype_id", true) ?>
            <a class="<?php echo ($this->TabActive == "projects") ? 'selected' : '' ?>" href='javascript:void(0);' onclick="showAjaxBasedContent('<?php echo $url;?>')" ><?php echo $this->translate('Projects'); ?></a>
          </li>
        <?php endif; ?>
      <?php endif; ?> 
        
      <?php if ($this->sitereview->allowWhereToBuy() ): ?>
        <li>

					<?php $url = $this->url(array('id' => $this->sitereview->listing_id), "sitereview_priceinfo_listtype_$listingtype_id", true) ?>
          <a class="<?php echo ($this->TabActive == "priceinfo") ? 'selected' : '' ?>" href='javascript:void(0);' onclick="showAjaxBasedContent('<?php echo $url;?>')" ><?php echo $this->translate("DASHBOARD_".$listing_singular_upper."_WHERE_TO_BUY"); ?></a>
        </li>
      <?php endif; ?>        
        
      <?php if($allowMetaKeywords && !empty($listingType->metakeyword)):?>
        <li>
					<?php $url = $this->url(array('action' => 'meta-detail', 'listing_id' => $this->sitereview->listing_id), "sitereview_dashboard_listtype_$listingtype_id", true) ?>
          <a class="<?php echo ($this->TabActive == "metadetails") ? 'selected' : '' ?>" href='javascript:void(0);' onclick="showAjaxBasedContent('<?php echo $url;?>')" ><?php echo $this->translate('Meta Keywords'); ?></a>
        </li>
      <?php endif; ?>

      <?php if ($allowStyle): ?>
        <li>
					<?php $url = $this->url(array('action' => 'editstyle', 'listing_id' => $this->sitereview->listing_id), "sitereview_specific_listtype_$listingtype_id", true) ?>
          <a class="<?php echo ($this->TabActive == "style") ? 'selected' : '' ?>" href='javascript:void(0);' onclick="showAjaxBasedContent('<?php echo $url;?>')" ><?php echo $this->translate('Edit Style'); ?></a>
        </li>
      <?php endif; ?>   
      <?php if ($listingType->allow_apply && $listingType->show_application): ?>
        <li>
          <?php $url = $this->url(array('action' => 'show-application', 'listing_id' => $this->sitereview->listing_id), "sitereview_specific_listtype_$listingtype_id", true) ?>
         <a class="<?php echo ($this->TabActive == "application") ? 'selected' : '' ?>" href='javascript:void(0);' onclick="showAjaxBasedContent('<?php echo $url;?>')" ><?php echo $this->translate('Manage Applications'); ?>     </a>
        </li>
      <?php endif;?>
      <?php if ($isEnabledPackage): ?>
        <li>
         <?php $url = $this->url(array('action' => 'update-package', 'listing_id' => $this->sitereview->listing_id), "sitereview_package_listtype_$listingtype_id", true) ?>
         <a class="<?php echo ($this->TabActive == "package") ? 'selected' : '' ?>" href='javascript:void(0);' onclick="showAjaxBasedContent('<?php echo $url;?>')" ><?php echo $this->translate('Packages'); ?>     </a>
        </li>
      <?php endif; ?>
    <?php endif; ?>
  </ul>
  
  <div class="sr_dashboard_info clr">
    <div class="sr_dashboard_info_image prelative">
			<?php if($this->sitereview->newlabel):?>
				<i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
			<?php endif;?>
      <?php echo $this->htmlLink($this->sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($this->sitereview, 'thumb.profile')) ?>
    </div>
    <center class="clr">
      <span>
        <?php if ($this->sitereview->sponsored == 1): ?>
          <i title="<?php echo $this->translate('Sponsored');?>" class="sr_icon seaocore_icon_sponsored"></i>
        <?php endif; ?>
        <?php if ($this->sitereview->featured == 1): ?>
          <i title="<?php echo $this->translate('Featured');?>" class="sr_icon seaocore_icon_featured"></i>
        <?php endif; ?>
					<?php if (empty($this->sitereview->approved) && empty($this->sitereview->declined)): ?>
					<?php $approvedtitle = 'Not approved';
					if (empty($this->sitereview->approved_date)): $approvedtitle = "Approval Pending";
					endif; ?>
							<?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/disapproved.gif', '', array('class' => 'icon', 'title' => $this->translate($approvedtitle))) ?>
					<?php endif; ?>
      </span>
    </center>
    <?php if ($isEnabledPackage): ?>
      <div>
        <b><?php echo $this->translate('Package: ') ?></b>
        <a href='<?php echo $this->url(array("action" => "detail", 'id' => $this->sitereview->package_id), "sitereview_package_listtype_$this->listingtype_id", true) ?>' onclick="owner(this);return false;" title="<?php echo $this->translate(ucfirst($this->sitereview->getPackage()->title)) ?>"><?php echo $this->translate(ucfirst($this->sitereview->getPackage()->title)); ?></a>
      </div>
  <?php if (!$this->sitereview->getPackage()->isFree()): ?>
        <div>
          <b><?php echo $this->translate('Payment: ') ?></b>
          <?php
          if ($this->sitereview->status == "initial"):
            echo $this->translate("Not made");
          elseif ($this->sitereview->status == "active"):
            echo $this->translate("Yes");
          else:
            echo $this->translate(ucfirst($this->sitereview->status));
          endif;
          ?>
        </div>
  <?php endif ?>
	<div>
		<b><?php echo $this->translate('Status: ') . $this->sitereview->getListingStatus() ?></b>
  </div>
	<?php if (!empty($this->sitereview->approved_date) && !empty($this->sitereview->approved)): ?>
				<div style="color: chocolate">
		<?php echo $this->translate('Approved ') . $this->timestamp(strtotime($this->sitereview->approved_date)) ?>
				</div>
		<?php if ($isEnabledPackage): ?>
					<div style="color: green;">
			<?php
			$expiry = $this->sitereview->getExpiryDate();
			if ($expiry !== "Expired" && $expiry !== $this->translate('Never Expires'))
				echo $this->translate("Expiration Date: ");
			echo $expiry;
			?>
					</div>
		<?php endif; ?>
	<?php endif ?>
	<?php endif ?>
<?php if($isEnabledPackage):?>
	<?php if (Engine_Api::_()->sitereviewpaidlisting()->canShowPaymentLink($this->sitereview->listing_id)): ?>
				<div class="tip center mtop5">
					<span class="db_payment_link">
						<a href='javascript:void(0);' onclick="submitSession(<?php echo $this->sitereview->listing_id ?>)"><?php echo $this->translate('Make Payment'); ?></a>
						<form name="setSession_form" method="post" id="setSession_form" action="<?php echo $this->url(array(), "sitereview_session_payment_$this->listingtype_id", true) ?>">
							<input type="hidden" name="listing_id_session" id="listing_id_session" />
						</form>
					</span>
				</div>
	<?php endif; ?>
	<?php if (Engine_Api::_()->sitereviewpaidlisting()->canShowRenewLink($this->sitereview->listing_id)): ?>
				<div class="tip mtop5">
					<span style="margin:0px;"> <?php echo $this->translate("Please click "); ?>
						<a href='javascript:void(0);' onclick="submitSession(<?php echo $this->sitereview->listing_id ?>)"><?php echo $this->translate('here'); ?></a><?php echo $this->translate(" to renew $listing_singular_lower."); ?>
						<form name="setSession_form" method="post" id="setSession_form" action="<?php echo $this->url(array(), "sitereview_session_payment_$this->listingtype_id", true) ?>">
							<input type="hidden" name="listing_id_session" id="listing_id_session" />
						</form>
					</span>
				</div>
	<?php endif; ?>
<?php endif;?>
  </div> 
</div>

<?php if($isEnabledPackage):?>
	<?php if (Engine_Api::_()->sitereviewpaidlisting()->canShowPaymentLink($this->sitereview->listing_id)): ?>
			<div class="sitereview_edit_content">
				<div class="tip">
					<span>
					<?php echo $this->translate("The package for your $listing_singular_upfirst requires payment. You have not fulfilled the payment for this $listing_singular_upfirst."); ?>
						<a href='javascript:void(0);' onclick="submitSession(<?php echo $this->sitereview->listing_id ?>)"><?php echo $this->translate('Make payment now!'); ?></a>
						<form name="setSession_form" method="post" id="setSession_form" action="<?php echo $this->url(array(), "sitereview_session_payment_$this->listingtype_id", true) ?>">
							<input type="hidden" name="listing_id_session" id="listing_id_session" />
						</form>
					</span>
				</div>
			</div>
		<?php endif; ?>

		<?php if (Engine_Api::_()->sitereviewpaidlisting()->canShowRenewLink($this->sitereview->listing_id)): ?>
			<div class="sitereview_edit_content">
				<div class="tip">
					<span>
			<?php if ($this->sitereview->expiration_date <= date('Y-m-d H:i:s')): ?>
				<?php echo $this->translate("Your package for this $listing_singular_upper has expired and needs to be renewed.") ?>
			<?php else: ?>
				<?php echo $this->translate("Your package for this $listing_singular_upper is about to expire and needs to be renewed.") ?>
			<?php endif; ?>
			<?php echo $this->translate(" Click "); ?>
						<a href='javascript:void(0);' onclick="submitSession(<?php echo $this->sitereview->listing_id ?>)"><?php echo $this->translate('here'); ?></a><?php echo $this->translate(' to renew it.'); ?>
						<form name="setSession_form" method="post" id="setSession_form" action="<?php echo $this->url(array(), "sitereview_session_payment_$this->listingtype_id", true) ?>">
							<input type="hidden" name="listing_id_session" id="listing_id_session" />
						</form>
					</span>
				</div>
			</div>
		<?php endif; ?>
<?php endif;?>
<script type="text/javascript">

	function showAjaxBasedContent(url) {
		if (history.pushState) {
			history.pushState( {}, document.title, url );
		} else {
			window.location.hash = url;
		}
		$('global_content').getElement('.sr_dashboard_content').innerHTML = '<div class="seaocore_content_loader"></div>'; 
		en4.core.request.send(new Request.HTML({
			url : url,
			'method' : 'get',
			data : {
				format : 'html',
				'isajax' : 1
			},onSuccess :  function(responseTree, responseElements, responseHTML, responseJavaScript)  {
					$('global_content').innerHTML = responseHTML;
          Smoothbox.bind($('global_content'));
          en4.core.runonce.trigger();
          if(SmoothboxSEAO){
                                                    SmoothboxSEAO.bind($('global_content'));
                                                }
					if (window.InitiateAction) {
						InitiateAction ();
					}
				}
		}));
	}

var requestActive = false;
window.addEvent('load', function() {
  InitiateAction();
});

var InitiateAction = function () {
  formElement = $$('.global_form')[0];
  if (typeof formElement != 'undefined' ) {
    formElement.addEvent('submit', function(event) {
      if (typeof submitformajax != 'undefined' && submitformajax == 1) {
        submitformajax = 0;
        event.stop();
        Savevalues();
      }
    })
  }
}

var Savevalues = function() {
  if( requestActive ) return;

  requestActive = true;
  var pageurl = $('global_content').getElement('.global_form').action;
 
  currentValues = formElement.toQueryString();
  $('show_tab_content_child').innerHTML = '<div class="seaocore_content_loader"></div>';
  if (typeof page_url != 'undefined') {
    var param = (currentValues ? currentValues + '&' : '') + 'isajax=1&format=html&page_url=' + page_url;
  }
  else {
    var param = (currentValues ? currentValues + '&' : '') + 'isajax=1&format=html';
  }

  var request = new Request.HTML({
    url: pageurl,
    onSuccess :  function(responseTree, responseElements, responseHTML, responseJavaScript)  {
      $('global_content').innerHTML =responseHTML;
      InitiateAction (); 
      requestActive = false;
    }
  });
  request.send(param);
}

var Show_Tab_Selected = "<?php echo $this->sitereviews_view_menu; ?>";
function submitSession(id) {
	document.getElementById("listing_id_session").value=id;
	document.getElementById("setSession_form").submit();
}

function owner(thisobj) {
	var Obj_Url = thisobj.href;
	Smoothbox.open(Obj_Url);
}

</script>


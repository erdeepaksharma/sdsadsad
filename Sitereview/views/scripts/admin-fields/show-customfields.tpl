<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

 <?php echo $this->render('_jsAdmin.tpl') ?>
 
<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php  echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<br />

<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'importlisting'), $this->translate('Back to Import Listings from a CSV file'), array('class' => 'buttonlink seaocore_icon_back')) ?>
<br /><br />

<div class="importlisting_form">
	<div>
		<h3><?php echo $this->translate('Generate New CSV Template File');?></h3>
		<p>
		 <?php echo $this->translate("Below, you can generate your own .csv template file by selecting the Profile Fields to be included in it by using the “Generate new CSV template file” link. Here, you can also download the previous generated template file by using the “Download Previous CSV File” link below. Before starting to use this tool, please read the following points carefully.");?>
		 <br />
		 <?php echo $this->translate('<b>Note</b>: If you have already imported a csv file, and you want to generate a new csv template file from here, then you should first delete the existing imported file by ');?>
		 <a href='<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'manage'), "admin_default", true) ?>' target="_blank"><?php echo $this->translate("clicking here."); ?></a>
		</p>
		<ul class="importlisting_form_sitereview">
			<li>
				<?php echo $this->translate("The Profile Fields marked \"Required\" are selected by default and in case of \"Multi Select Box\" and \"Multi Checkbox\" question types, all choices are selected. We recommend you to do not uncheck required fields and uncheck all the choices for \"Multi Select Box\" and \"Multi Checkbox\" question types as atleast one choice should be selected.");?>
			</li>
			<li>
			  <?php echo $this->translate('For creating accurate listings from the CSV file, it is recommended that you fill all the required fields in the file to be imported.');?>
			</li>
			<li>
				<?php echo $this->translate('For the profile fields having below mentioned Question Types, you should write the appropriate code for that  field. To know the Code, please click on the help icon placed just after the Question Type below:');?><br />
        <div>
          - <?php echo $this->translate('Country');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'country')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Ethnicity');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'ethnicity')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Occupation');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'occupation')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Education');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'education_level')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Relationship Status');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'relationship_status')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Looking For');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'looking_for')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Weight');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'weight')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Religion');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'religion')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Political Views');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'political_views')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Income');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'income')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Interested In');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'partner_gender')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Zodiac');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'zodiac')) ?>" class="smoothbox bold">?</a>]
        </div>
        <div>
          - <?php echo $this->translate('Date');?>
          [<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'show-country','field' => 'date')) ?>" class="smoothbox bold">?</a>]
        </div>
			</li>	
		</ul>
		<br />
		<form method="post" class="global_form_box" id="customfield" action="<?php echo $this->url(array('action' => 'show-customfields')) ?><?php echo '?path=' . urlencode('example_listing_import.csv');?>" width="100%">
			<?php foreach($this->topLevelOptions as $key => $profile):?>
			<input type="hidden" name="fieldname[]" value=""/>
			<h3  style="background-color: #666;padding:5px; border: 1px solid #DDDDDD;color:#fff;"><?php echo $profile;?></h3>
			<?php $this->secondLevelMaps = Engine_Api::_()->getApi('core', 'sitereview')->getsecondLevelMaps($key);?>
			<?php if(count($this->secondLevelMaps) == 0):?>
				<div class="tip">
					<span>
						<?php echo $this->translate('You have not created any Question in this Profile Type yet.');?>
					</span>
				</div>
			<?php endif;?>
			<ul class="admin_fields sitereview_custom_fields">
				<?php foreach ($this->secondLevelMaps as $map): ?>
				  <?php if(!empty($map->option_id)):?>
						<?php $profileTypeLabel = Engine_Api::_()->getApi('core', 'sitereview')->getProfileTypeName($map->option_id);?>
				  <?php endif;?>
					<?php $meta = $map->getChild();?>
					<?php echo $this->adminFieldMetaQuestion($map) ?>
					<?php if($meta->type != 'heading' && $meta->type != 'multi_checkbox' && $meta->type != 'multiselect'):?>
						<?php if($meta->type == 'looking_for'):?>
							<?php $label_value = $profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'friendship'.'|'.$profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'dating'.'|'.$profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'relationship'.'|'.$profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'networking';?>
						<?php  elseif($meta->type == 'partner_gender'):?>
							<?php $label_value = $profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'Men'.'|'.$profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'Women';?>
						<?php  elseif($meta->type == 'ethnicity'):?>
							<?php $label_value = $profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'asian'.'|'.$profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'black'.'|'.$profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'hispanic'.'|'.$profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'pacific'.'|'.$profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'white'.'|'.$profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id.'_'.'other';?>
						<?php else:?>
							<?php $label_value = $profileTypeLabel.'_'.$meta->label.'_'.$meta->field_id;?>
						<?php endif;?>
						<li class="admin_field_checkbox">
							<input type="checkbox" name="addtional[]" <?php if(!empty($meta->required)):?> checked <?php endif;?> value="<?php echo $label_value;?>"/>
						</li>
					<?php endif;?>
				<?php endforeach; ?>
			</ul>
			<br />
			<?php endforeach;?>
			<?php if(empty($this->import_id)):?>
			<a href="javascript:void(0);" onclick="showErrorMessage(); return false;" class="buttonlink icon_sitereviews_download_csv"><?php echo $this->translate('Download New CSV Template File')?></a> &nbsp;&nbsp;&nbsp;
			<?php else:?>
				<div class="tip">
					<span>     
						<?php echo $this->translate("You have already imported a csv file. To generate a new csv template file from here, please delete the existing imported file by ");?><a href='<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'manage'), "admin_default", true) ?>' target="_blank"><?php echo $this->translate("clicking here."); ?></a>
					</span>
				</div>
			<?php endif;?>
			<a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'download')) ?><?php echo '?path=' . urlencode('previous_listing_import.csv');?>" class="buttonlink icon_sitereviews_download_csv"><?php echo $this->translate('Download Previous CSV Template File')?></a>  
		</form>
	</div>
</div>

<script type="text/javascript" >
	window.addEvent('load', function(){ 
	sortablesInstance.detach();
	})
	function showErrorMessage() {

		var import_id = '<?php echo $this->import_id;?>';
		if(import_id != '') {
			alert('You have already generated a csv file. Please delete it from import manage section first');
			return;
		}
		else {
			$('customfield').submit();
		}
	}
</script>
<style type="text/css">
ul.field_extraoptions_choices > li{padding:5px;}
ul.field_extraoptions_choices > li span.field_extraoptions_choices_options{padding:0px;}
</style>
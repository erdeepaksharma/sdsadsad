<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: compare.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>
<?php if (count($this->navigation)): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>
<?php //if($this->countCategories):?>
<div class='seaocore_settings_form'>
  <div class='settings'>
    <?php $this->form->setDescription($this->translate('Below, you can choose the fields over which users should be able to compare listings on your site. You can also choose different fields for comparison of listings belonging to different categories. For each category / sub-category / 3rd level category applied for comparison from "Categories" section, you can also add more fields for comparison from "Rating Parameter" sub-section (in "Reviews & Ratings" section) and from "Profile Fields" section. These fields will be displayed to users on listings comparison page. You can also enable / disable comparison of listings belonging to different categories by using "Enable Comparison" field below.<br /><br /><b>Note:</b> To add more comparison fields from "Profile Fields" section, you need to map the "Profile Type" created in this section with the desired Category from "Category-Listing Profile Mapping" section.'));
     $this->form->getDecorator('Description')->setOption('escape', false); ?>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script type="text/javascript" >
  var edit_url = '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'settings', 'action' => 'compare'), 'admin_default', true); ?>'

  function changeCategory(el){
    var preaddurl='';
    switch (el.get('id')){
      case 'subsubcategory_id':
        preaddurl = '/subsubcategory_id/'+$('3rd_category_id').value +preaddurl;
      case 'subcategory_id':
        preaddurl = '/subcategory_id/'+$('subcategory_id').value +preaddurl;
      case 'category_id':
        preaddurl = '/category_id/'+$('category_id').value +preaddurl;
        <?php if($this->listingTypesCount<=1):?>
          break;
        <?php endif; ?>          
      case 'listtype_id':
        preaddurl = '/listingtype_id/'+$('listtype_id').value +preaddurl;
        break;
    }
    window.location.href= edit_url+preaddurl;
  }
  	window.addEvent('domready', function(){	
      toggleEditorParm($('editor_rating'));
      toggleUserParm($('user_rating'));
    });
  var toggleEditorParm=function(el){
    if(!$('editor_rating_fields-wrapper'))
      return;
   
    if(el.checked){
      $('editor_rating_fields-wrapper').setStyle('display','block');
    }
    else{
      $('editor_rating_fields-wrapper').setStyle('display','none');
    }
  }
  
  var toggleUserParm=function(el){
     if(!$('user_rating_fields-wrapper'))
      return;
   if(el.checked){
      $('user_rating_fields-wrapper').setStyle('display','block');
    }
    else{
      $('user_rating_fields-wrapper').setStyle('display','none');
    }
  }
</script>

<style type="text/css">
	#views-wrapper, #tags-wrapper, #location-wrapper, #price-wrapper, #comments-wrapper, #likes-wrapper, #reviews-wrapper{border-top:none;padding-top:0px;}
</style>

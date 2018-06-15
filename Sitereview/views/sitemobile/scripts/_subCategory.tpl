<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2010-2011 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _formSubcategory.tpl 2011-05-05 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$request = Zend_Controller_Front::getInstance()->getRequest();
$module = $request->getModuleName();
$controller = $request->getControllerName();
$action = $request->getActionName();
$params = $request->getParams();

if($action == 'edit' && $controller == 'index' && $module  == 'sitereview') {
    if(Engine_Api::_()->getItem('sitereview_listing', $params['listing_id'])->subcategory_id)
      $params['subcategory_id'] = Engine_Api::_()->getItem('sitereview_listing', $params['listing_id'])->subcategory_id;
    if(Engine_Api::_()->getItem('sitereview_listing', $params['listing_id'])->subsubcategory_id)
      $params['subsubcategory_id'] = Engine_Api::_()->getItem('sitereview_listing', $params['listing_id'])->subsubcategory_id;
}

$listingtype_id = 0;
if (isset($params['listingtype_id']) && $params['listingtype_id'])
  $listingtype_id = $params['listingtype_id'];
?>

<?php
  $listing_id = 0;
  $defaultProfileFieldId = Engine_Api::_()->getDbTable('metas', 'sitereview')->defaultProfileId();
  $defaultProfileFieldId = "0_0_$defaultProfileFieldId";
  $profile_type = 0;
  if(isset($params['listing_id']) && $params['listing_id']) {
    $listing_id = $params['listing_id'];
    $listing = Engine_Api::_()->getItem('sitereview_listing', $params['listing_id']);
    $profile_type = Engine_Api::_()->getDbTable('categories', 'sitereview')->getProfileType(null, $listing->category_id, 'profile_type');
  }
  
?>

<script type="text/javascript">

  var defaultProfileFieldId = '<?php echo $defaultProfileFieldId; ?>';
  
  var actionType = '<?php echo $listing_id; ?>';
  var profile_type = '<?php echo $profile_type ?>';
  var previous_mapped_level = 0;

  sm4.switchView.searchArray={
    '<?php echo $module . '_' . $controller . "_" . $action . "_" . $listingtype_id ?>':{
      profile_type: 0,
      previous_mapped_level:0
    }
  };
   
  function showSRListingFields(cat_value, cat_level) { 
    var content= sm4.switchView.searchArray.<?php echo $module . '_' . $controller . "_" . $action . "_" . $listingtype_id ?>; 
    if(cat_level == 1 || (content.previous_mapped_level >= cat_level && content.previous_mapped_level != 1) || (content.profile_type == null || content.profile_type == '' || content.profile_type == 0)) {
      content.profile_type = getSRListingProfileType(cat_value); 
      if(content.profile_type == 0) { content.profile_type = ''; } else { content.previous_mapped_level = cat_level; }
      if($.mobile.activePage.find('#profile_type').get(0)) {
        $.mobile.activePage.find('#profile_type').value = content.profile_type;
        changeFields($.mobile.activePage.find('#profile_type')); 
      } else {
        if($('#'+defaultProfileFieldId)) { 
            $('#'+defaultProfileFieldId).val(content.profile_type);
            changeFields($('#'+defaultProfileFieldId));
        }
        
      }
    }
  }
  
 
  var getSRListingProfileType = function(category_id) {
    var mapping = <?php echo Zend_Json_Encoder::encode(Engine_Api::_()->getDbTable('categories', 'sitereview')->getMapping($listingtype_id, 'profile_type')); ?>;
    for(i = 0; i < mapping.length; i++) {
      if(mapping[i].category_id == category_id)
        return mapping[i].profile_type;
    }
    return 0;
  }
  
</script>


<?php
$tabel = Engine_Api::_()->getDbTable('categories', 'sitereview');
$subCategories = $tabel->getCategoriesByLevel($listingtype_id, 'subcategory', array('category_id', 'category_name', 'cat_dependency', 'subcat_dependency', 'category_slug'));

if (count($subCategories) == 0)
  return;


$catParams = array();
if ((isset($params['subcategory_id']) && $cat = $params['subcategory_id']) || (isset($params['subcategory']) && $cat = $params['subcategory'])) {
  $catParams[] = array('type' => 'subcategory', 'value' => $cat, 'isChildSet' => 1);
  if ((isset($params['subsubcategory_id']) && $cat = $params['subsubcategory_id']) || (isset($params['subsubcategory']) && $cat = $params['subsubcategory'])) {
    $catParams[] = array('type' => 'subsubcategory', 'value' => $cat);
  }
}


$subsubCategories = $tabel->getCategoriesByLevel($listingtype_id, 'subsubcategory', array('category_id', 'category_name', 'cat_dependency', 'subcat_dependency', 'category_slug'));
?>

<div id='subcategory_id-wrapper' class='form-wrapper dnone'>
  <div class='form-label'><label><?php echo $this->translate('Subcategory', "<sup>rd</sup>") ?></label></div>
  <div class='form-element'>
    <select name='subcategory_id' id='subcategory_id' onchange="showSRListingFields(this.value, 2);sm4.core.category.set(this.value,'subsubcategory');">
      <option value="0" ></option>
      <?php foreach ($subCategories as $category): ?>
        <option class="subcategory_option dnone" value="<?php echo $category->getIdentity() ?>" data-parent_category="<?php echo "sp_cat_" . $category->cat_dependency; ?>" ><?php echo $this->translate($category->getTitle(true)); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>
<?php if (count($subsubCategories) > 0): ?>
  <div id='subsubcategory_id-wrapper' class='form-wrapper dnone'>
    <div class='form-label'><label><?php echo $this->translate('3%s Level Category', "<sup>rd</sup>") ?> </label></div>
    <div class='form-element'>
      <select name='subsubcategory_id' id='subsubcategory_id' onchange="showSRListingFields(this.value, 3);sm4.core.category.onChange('subsubcategory',this.value);">
        <option value="0" ></option>
        <?php foreach ($subsubCategories as $category): ?>
          <option class="subsubcategory_option dnone" value="<?php echo $category->getIdentity() ?>" data-parent_category="<?php echo "sp_cat_" . $category->cat_dependency; ?>" ><?php echo $this->translate($category->getTitle(true)); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
<?php endif; ?>

<script type="text/javascript">
  sm4.core.runonce.add(function(){
    sm4.core.category.setDefault(<?php echo $this->jsonInline($catParams) ?>);
  });
</script>
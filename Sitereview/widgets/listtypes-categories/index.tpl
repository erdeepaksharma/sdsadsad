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

<?php
$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/styles.css');
?>

<div class="b_medium seaocore_navigation <?php echo $this->viewDisplayHR ?"seaocore_navigation_h" : "seaocore_navigation_v"; ?>" >
  <?php $level = 0 ?>
  <?php if($this->viewDisplayHR):?>
    <?php if ($this->widgetTitle): ?>
      <div class="heading"><span><?php echo $this->translate($this->widgetTitle) ?>:<span></div>
    <?php elseif ($this->listingTypesCount <= 1): ?>
      <?php foreach ($this->listingTypesArray as $item): ?>
        <?php $listtype = $item['list_type']; ?>
        <div class="heading"><a href="<?php echo $listtype->getHref() ?>"><span><?php echo $this->translate($listtype->getTitle()) ?></span></a></div>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php endif; ?>
           
  <ul class="seaocore_menu <?php echo $this->viewDisplayHR ?"seaocore_menu_h":"seaocore_menu_v b_dark" ?>" id="nav_cat_<?php echo $this->identity ?>">
    <?php foreach ($this->listingTypesArray as $item): ?>
      <?php $level = 0 ?>
      <?php $listtype = $item['list_type'];
      $categories = $item['categories']; ?>
      <?php if ($this->listingTypesCount > 1): ?>
        <li class="level<?php echo $level . " " . (!empty($categories) ? 'parent' : '')?>">
          <a class="level-top <?php if (isset($this->requestAllParams['listingtype_id']) && $this->requestAllParams['listingtype_id'] == $listtype->getIdentity()): echo "selected";
    endif; ?>" href="<?php echo $listtype->getHref() ?>">
            <span><?php echo $this->translate($listtype->getTitle()) ?></span>
          </a>
        <?php endif; ?>
        <?php if (!empty($categories)): ?>
          <?php if ($this->listingTypesCount > 1): ?>
            <ul class="level<?php echo $level ?>">
            <?php endif; ?>
            <?php foreach ($categories as $categorylist): ?>
              <?php $level = $this->listingTypesCount > 1 ? 1 : 0; ?>
              <?php $category = $categorylist['category'] ?>
              <?php $subcategories = $categorylist['subcategories'] ?>
              <li class="level<?php echo $level . " " . (!empty($subcategories) ? 'parent' : '') ?> ">
                <a class="<?php echo $this->listingTypesCount <= 1 ? "level-top" : '' ?> <?php if (isset($this->requestAllParams['category']) && $this->requestAllParams['category'] == $category->getIdentity()): echo "selected";
        endif; ?>" href="<?php echo $category->getHref() ?>">
                  <span><?php echo $this->translate($category->getTitle()) ?></span>
                </a>
                <?php if (!empty($subcategories)): ?>
                  <ul class="level<?php echo $level ?>">
                    <?php foreach ($subcategories as $subcategorieslist): ?>
                      <?php $level = $this->listingTypesCount > 1 ? 2 : 1; ?>
                      <?php $subcategory = $subcategorieslist['subcategory'] ?>
                      <?php $subsubcategories = $subcategorieslist['subsubcatgories'] ?>
                      <li class="level<?php echo $level . " " . (!empty($subsubcategories) ? 'parent' : '') ?> ">
                        <a class="<?php if (isset($this->requestAllParams['subcategory']) && $this->requestAllParams['subcategory'] == $subcategory->getIdentity()): echo "selected";
            endif; ?>" href="<?php echo $subcategory->getHref() ?>">
                          <span><?php echo $this->translate($subcategory->getTitle()) ?></span>
                        </a>
                        <?php if (!empty($subsubcategories)): ?>
                          <ul class="level<?php echo $level ?>">
                            <?php foreach ($subsubcategories as $subsubcategory): ?>
                              <?php $level = $this->listingTypesCount > 1 ? 3 : 2; ?>
                              <li class="level<?php echo $level ?> ">
                                <a class="<?php if (isset($this->requestAllParams['subsubcategory']) && $this->requestAllParams['subsubcategory'] == $subsubcategory->getIdentity()): echo "selected";
                endif; ?>" href="<?php echo $subsubcategory->getHref() ?>">
                                  <span><?php echo $this->translate($subsubcategory->getTitle()) ?></span>
                                </a>
                              </li>
                            <?php endforeach; ?>
                          </ul>
                        <?php endif; ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>

              </li>
            <?php endforeach; ?>
            <?php if ($this->listingTypesCount > 1): ?>
            </ul>
          <?php endif; ?>
        <?php endif; ?>
        <?php if ($this->listingTypesCount > 1): ?>
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
  <?php $server_name = $_SERVER['SERVER_NAME']; $check_demo = strstr($server_name, 'demo.socialengineaddons.com');?>
  <?php if($this->viewDisplayHR && $check_demo):?>      
	  <div class="demo_info_tip_wrapper">
	  	<i></i>
	  	<div class="demo_info_tip">
	  		<i></i>
	  		<b><?php echo $this->translate("NOTE:"); ?></b> <?php echo $this->translate('This is the "Listing Type / Category Navigation Bar" widget and you can choose to not show it on your website. Also the Layout for this Listing Type is for demonstration only. You can have a different Layout for similar Listing Types on your site from the Layout Editor section of your admin panel.'); ?>
	  	</div>
	  </div>
  <?php endif; ?>
</div>

<script type="text/javascript">
  en4.core.runonce.add(function(){
    if(!(typeof NavigationSitereview == 'function')){
      new Asset.javascript( en4.core.staticBaseUrl+'application/modules/Sitereview/externals/scripts/core.js',{
        onLoad :addDropdownMenu
      });
    } else {
      addDropdownMenu();
    }


    function addDropdownMenu(){
    NavigationSitereview("nav_cat_<?php echo $this->identity ?>", {"show_delay":"100","hide_delay":"100"});
    }
  })
</script>

<?php if($this->listingTypesCount >= 5):?>
	<style type="text/css">
  .layout_sitereview_listtypes_categories .seaocore_navigation_h .seaocore_menu_h > li:last-child ul, .layout_sitereview_listtypes_categories .seaocore_navigation_h .seaocore_menu_h > li:nth-last-child(2) ul, .layout_sitereview_listtypes_categories .seaocore_navigation_h .seaocore_menu_h > li:nth-last-child(3) ul, .layout_sitereview_listtypes_categories .seaocore_navigation_h .seaocore_menu_h > li:nth-last-child(4) ul {
      margin-left: -300px !important;
      right: 0;
  }
  </style>
<?php endif;?>
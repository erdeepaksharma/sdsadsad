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


<div class="categories-block">
<?php if ($this->listingTypeCount > 1 && 0): ?>

 <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>
  <?php if (!empty($this->listingtype_id)): ?>
    <?php
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $this->listingtype_id);
    $breadcrumb = array(
        array("href" => $this->url(array(), 'sitereview_review_categories', true), "title" => $this->translate('All Categories'), "icon" => "arrow-r"),
        array("title" => ucfirst($listingtypeArray->title_plural), "icon" => "arrow-d"),
    );

    echo $this->breadcrumb($breadcrumb);
    ?>
  <?php else: ?>
    <div>
      <strong><?php echo $this->translate('All Categories'); ?></strong>
    </div>
    <br />
  <?php endif; ?>    
  <?php else: ?>
    <div>
      <strong><?php echo $this->translate('Categories'); ?></strong>
    </div>
    <br />
  <?php endif;?>  


<?php endif; ?>
<ul  class="ui-listview collapsible-listview" >
  <?php $k = 0; ?>
  <?php for ($i = 0; $i <= $this->totalCategories; $i++) : ?>
    <?php
    $category = "";
    if (isset($this->categories[$k]) && !empty($this->categories[$k])) {
      $category = $this->categories[$k];
    }

    $k++;

    if (empty($category)) {
      break;
    }
    ?>
    <li class="ui-btn ui-btn-icon-right ui-li-has-arrow ui-li ui-btn-up-c <?php if (isset($category['count'])): ?>ui-li-has-count<?php endif; ?>">
      <?php $total_subcat = !empty($this->show2ndlevelCategory) ? count($category['sub_categories']) : 0; ?>
      <?php $item = Engine_Api::_()->getItem('sitereview_category', $category['category_id']); ?>
      
        <!--START-ICON OR PLUS-MINUS VIEW-->
        <?php if($this->category_icon_view && !empty($item->file_id)):?>
        <div class="collapsible_icon" >
          <a class="ui-link-inherit" href="<?php echo $item->getHref() ?>"  >
          <img alt="" height="16px" width="16px" class="ui-icon ui-icon-shadow" src="<?php echo $this->storage->get($item->file_id, '')->getPhotoUrl(); ?>" />
          </a>
        </div> <!--END-ICON OR PLUS-MINUS VIEW-->
        <?php elseif ($total_subcat) : ?>
        <div class="collapsible_icon" ><span class="ui-icon ui-icon-plus ui-icon-shadow">&nbsp;</span></div>
       
      <?php else: ?>
            <div class="collapsible_icon_none" ><span class="ui-icon ui-icon-circle ui-icon-shadow">&nbsp;</span></div>
      <?php endif; ?>
      <div class="ui-btn-inner ui-li" ><div class="ui-btn-text">          
          <a class="ui-link-inherit" href="<?php echo $item->getHref() ?>"  >
            <?php echo $this->translate($item->getTitle(true)); ?>
            <?php if (isset($category['count'])): ?><span class="ui-li-count ui-btn-up-c ui-btn-corner-all"><?php echo $category['count'] ?></span><?php endif; ?></a>
        </div><span class="ui-icon <?php if (Engine_Api::_()->sitemobile()->isApp()): ?>ui-icon-angle-right<?php else : ?>ui-icon-arrow-r<?php endif;?>">&nbsp;</span></div>
      <?php if ($total_subcat): ?>
        <ul class="collapsible">
          <?php foreach ($category['sub_categories'] as $subcategory) : ?>
            <li class="ui-btn ui-btn-icon-right ui-li-has-arrow ui-li ui-btn-up-c <?php if (isset($subcategory['count'])): ?>ui-li-has-count<?php endif; ?>">
              <?php $total_subcat_tree = !empty($this->show3rdlevelCategory) && isset($subcategory['tree_sub_cat']) ? count($subcategory['tree_sub_cat']) : 0;
              ?>
              <?php $item = Engine_Api::_()->getItem('sitereview_category', $subcategory['sub_cat_id']); ?>
                      <!--START-ICON OR PLUS-MINUS VIEW-->
              <?php if($this->category_icon_view && !empty($item->file_id)) :?>
              <div class="collapsible_icon" >
                <a class="ui-link-inherit" href="<?php echo $item->getHref() ?>"  >
                <img alt="" height="16px" width="16px" class="ui-icon ui-icon-shadow" src="<?php echo $this->storage->get($item->file_id, '')->getPhotoUrl(); ?>" />
                </a>
              </div> <!--END-ICON OR PLUS-MINUS VIEW-->
              <?php elseif ($total_subcat_tree) : ?>
              <div class="collapsible_icon" ><span class="ui-icon ui-icon-plus ui-icon-shadow">&nbsp;</span></div>

            <?php else: ?>
                  <div class="collapsible_icon_none" ><span class="ui-icon ui-icon-circle ui-icon-shadow">&nbsp;</span></div>
            <?php endif; ?>
              <div class="ui-btn-inner ui-li" ><div class="ui-btn-text">
                  
                  <a class="ui-link-inherit" href="<?php echo $item->getHref() ?>"  >
                    <?php echo $this->translate($item->getTitle(true)); ?>
                    <?php if (isset($subcategory['count'])): ?><span class="ui-li-count ui-btn-up-c ui-btn-corner-all"><?php echo $subcategory['count'] ?></span><?php endif; ?></a>
                </div><span class="ui-icon <?php if (Engine_Api::_()->sitemobile()->isApp()): ?>ui-icon-angle-right<?php else : ?>ui-icon-arrow-r<?php endif;?>">&nbsp;</span></div>
              <?php if ($total_subcat_tree): ?>
                <ul class="collapsible">
                  <?php foreach ($subcategory['tree_sub_cat'] as $subsubcategory) : ?>
                    <li class="ui-btn ui-btn-icon-right ui-li-has-arrow ui-li ui-btn-up-c <?php if (isset($subsubcategory['count'])): ?>ui-li-has-count <?php endif; ?>">
                      
                      <?php $item = Engine_Api::_()->getItem('sitereview_category', $subsubcategory['tree_sub_cat_id']); ?>
                      <?php if ($this->category_icon_view && !empty($item->file_id)): ?>
                        <div class="collapsible_icon" >
                          <a class="ui-link-inherit" href="<?php echo $item->getHref() ?>"  >
                            <img alt="" height="16px" width="16px" class="ui-icon ui-icon-shadow" src="<?php echo $this->storage->get($item->file_id, '')->getPhotoUrl(); ?>" />
                          </a>
                        </div> <!--END-ICON OR PLUS-MINUS VIEW-->              
                      <?php else: ?>
                        <div class="collapsible_icon_none" ><span class="ui-icon ui-icon-circle ui-icon-shadow">&nbsp;</span></div>
                      <?php endif; ?>
                      <div class="ui-btn-inner ui-li" ><div class="ui-btn-text">                         
                          <a class="ui-link-inherit" href="<?php echo $item->getHref() ?>"  >
                            <?php echo $this->translate($item->getTitle(true)); ?>
                            <?php if (isset($subsubcategory['count'])): ?><span class="ui-li-count ui-btn-up-c ui-btn-corner-all"><?php echo $subsubcategory['count'] ?></span><?php endif; ?></a>
                        </div><span class="ui-icon <?php if (Engine_Api::_()->sitemobile()->isApp()): ?>ui-icon-angle-right<?php else : ?>ui-icon-arrow-r<?php endif;?>">&nbsp;</span></div>

                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </li>
  <?php endfor; ?>
</ul>
<?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>
<?php if (empty($this->showCount) && 0): ?>
  <div class="t_l fright">
    <?php if ($this->listingtype_id > 0): ?>
      [ <?php echo $this->htmlLink(array('route' => 'sitereview_review_categories_' . $this->listingtype_id, 'showCount' => 1), $this->translate('See item counts')); ?> ]
    <?php else: ?>
      [ <?php echo $this->htmlLink(array('route' => 'sitereview_review_categories', 'showCount' => 1), $this->translate('See item counts')); ?> ]
    <?php endif; ?>
  </div> 
<?php endif; ?>
<?php endif; ?>
</div>

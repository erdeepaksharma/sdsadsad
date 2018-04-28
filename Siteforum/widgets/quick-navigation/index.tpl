 <?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/styles.css'); ?>


<script type="text/javascript">

	function changeFunc(title,url) {
    
		if(title != undefined){
			document.getElementById("siteform_quicknav_btn").innerHTML = title;			
			window.location.href = url;
		}
	}
  
  function showQuickLinks() {
  $(document.body).addEvent('click',function() {
    showForumQuickLinks();
  });
  $$('.siteform_quicknav_btn').addEvent('click',function(event) {
    event.stop();
    $(this).getParent('.siteforumcontent_dropdown').getElement('.siteforum_dropdown_menu').toggle(); 
    
  });
}

function showForumQuickLinks() {
      $$('.siteform_quicknav_btn').show();
      $$('.siteform_quicknav_btn').getParent('.siteforumcontent_dropdown').getElement('.siteforum_dropdown_menu').hide();
}
</script>   

<?php $params = array(); ?>


<?php if (!empty($this->show_navigation) && in_array('navigation', $this->show_navigation)) : ?> 
<div class="siteforumcontent_dropdown">
    <button class="siteform_quicknav_btn" id="siteform_quicknav_btn" type="button"><?php if (!empty($this->selected)) { ?> <?php echo $this->translate($this->selected)?><?php } else{ ?>
  <?php echo $this->translate("Quick Navigation"); } ?>
    <span class="siteforumcontent_dropdown_caret"></span>
  </button>
    <ul class="siteforum_dropdown_menu" id="siteforum_dropdown_menu" style="display:none">
     <?php foreach ($this->categories as $category) { ?>
        <?php   $params['category_id'] = $category->category_id;
                $siteforums[$category->category_id] = $this->forumTable->getForums($params);
                $subCategories[$category->category_id] = $this->categoryTable->getSubCategories($category->category_id);
                $catForumCount = count($siteforums[$category->category_id]);
                $catSubcategoryCount = count($subCategories[$category->category_id]);
                ?>
        <?php if($this->show_empty_category || ($catForumCount || $catSubcategoryCount)):?>
          <li class="siteforum_cate_title"><a href="javascript:void(0);" onclick="changeFunc('<?php echo $category->title; ?>', '<?php echo $this->url(array('category_id' => $category->category_id), 'siteforum_category'); ?>');"><?php echo $category->title; ?></a></li>
        <?php endif;?>
       <?php if ($this->hierarchy == 3):
                $params['category_id'] = $category->category_id;
                $siteforums[$category->category_id] = $this->forumTable->getForums($params);
                foreach ($siteforums[$category->category_id] as $siteforum) {
                    ?>
			       <li class="siteforum_cateforum_title"><a href="javascript:void(0);" onclick="changeFunc('<?php echo $siteforum->title; ?>','<?php echo $this->url(array('forum_id' => $siteforum->forum_id), 'siteforum_forum'); ?>');">&raquo; <?php echo $siteforum->title; ?></a></li>     
                                <?php  }
            endif; ?>
        
        
        <?php
            if ($this->hierarchy == 2 || $this->hierarchy == 3):
                $subCategories[$category->category_id] = $this->categoryTable->getSubCategories($category->category_id);
                foreach ($subCategories[$category->category_id] as $subCategory) {
                    ?>
             <?php $params['category_id'] = $subCategory->category_id;
                   $siteforums[$subCategory->category_id] = $this->forumTable->getForums($params);
                   $subcatForumCount = count($siteforums[$subCategory->category_id]);?>
             <?php if($this->show_empty_category || $subcatForumCount):?>
      	<li class="siteforum_subcate_title"><a href="javascript:void(0);" onclick="changeFunc('<?php echo $subCategory->title; ?>','<?php echo $this->url(array('category_id' => $category->category_id, 'subcategory_id' => $subCategory->category_id), 'siteforum_subcategory'); ?>');">&raquo; <?php echo $subCategory->title; ?></a></li>
             <?php endif;?>
        
                          <?php
                    if ($this->hierarchy == 3):
                        $params['category_id'] = $subCategory->category_id;
                        $siteforums[$subCategory->category_id] = $this->forumTable->getForums($params);
                        foreach ($siteforums[$subCategory->category_id] as $siteforum) {
                            ?>
      <li class="siteforum_subcateforum_title"><a href="javascript:void(0);" onclick="changeFunc('<?php echo $siteforum->title; ?>','<?php echo $this->url(array('forum_id' => $siteforum->forum_id), 'siteforum_forum'); ?>');">&raquo; <?php echo $siteforum->title; ?></a></li>
      
                    <?php }
                    endif; }
            endif;
             } ?>
    </ul>
</div>
<?php endif;?>
<?php if (!empty($this->show_navigation) && in_array('dashboard', $this->show_navigation)) : ?> 
    <div class="fright siteforum_dashboard_button">
        <a href="<?php echo $this->url(array('controller' => 'dashboard', 'action' => 'my-topics'), 'siteforum_specific'); ?>"><?php echo $this->translate('User Dashboard'); ?></a>
    </div>
<?php endif; ?>

<script type="text/javascript">
    en4.core.runonce.add(function () {
        showQuickLinks();
    });
    
    
        $$('.core_main_forum').getParent().addClass('active');
    </script>
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
<?php
$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
        . 'application/modules/Siteforum/externals/styles/style_siteforum.css');
?>

<script type="text/javascript">
    var show_setting = '<?php echo $this->show; ?>';
    function forum_show(id) {

        if ($('subcat_' + id).style.display == 'none') {
            $('subcat_' + id).style.display = 'block';
            $('forum_hide_' + id).setStyle('display', 'block');
            $('forum_expand_' + id).setStyle('display', 'none');
        }
        else {
            $('subcat_' + id).style.display = 'none';
            $('forum_hide_' + id).setStyle('display', 'none');
            $('forum_expand_' + id).setStyle('display', 'block');
        }
    }

    window.addEvent('domready', function () {
        if (show_setting == 3) {
            $$('.siteforum_hide').each(function (el) {
                el.style.display = 'none';
            });
            $$('.siteforum_category_box').each(function (el) {
                el.style.display = 'none';
            });
        }
        else {
            $$('.siteforum_expand').each(function (el) {
                el.style.display = 'none';
            });
        }
    });
</script>

<?php if (!empty($this->categories)): ?>

    <ul class="siteforum_categories">

        <?php foreach ($this->categories as $category) {
            ?>
            <?php
            // if(empty($this->empty_category[$category->category_id])){
            //   $show_category = 1;
            //}else{
//        if($this->show_empty_category)
//            $show_category = 1;
//        else
//            $show_category = 0;
            ?>
            <?php if (!$this->empty_category[$category->category_id] || $this->show_empty_category): ?>
                <li>
                    <div class="siteforum_category_header">
                        <?php
                        if (empty($this->isSubCategory)):
                            $url = $this->url(array('category_id' => $category->getIdentity()), 'siteforum_category');
                        else :
                            $url = $this->url(array('category_id' => $this->category_id, 'subcategory_id' => $category->getIdentity()), 'siteforum_subcategory');
                        endif;
                        ?>
                        <h3 class="fleft">
                            <?php if (!empty($this->show_category_icon)): ?>
                                <span class="cat_icon"><?php if ($category['photo_id']): ?><a href="<?php echo $url; ?>"><img alt=""  src='<?php echo $this->storage->get($category['photo_id'], '')->getPhotoUrl(); ?>' /></a><?php endif; ?></span>
                            <?php endif; ?>
                            <a href="<?php echo $url; ?>"><?php echo $this->translate($category->title); ?></a>
                        </h3>
                        <?php if (!empty($this->show_expand)): ?>
                            <div id="forum_expand_<?php echo $category->category_id ?>" class="siteforum_expand show_hide_link">
                                <a href='javascript:void(0);' onclick="javascript:forum_show('<?php echo $category->category_id ?>');" title="<?php echo $this->translate("Expand"); ?>"></a>
                            </div>
                            <div id="forum_hide_<?php echo $category->category_id ?>" class="siteforum_hide show_hide_link">
                                <a href='javascript:void(0);' onclick="javascript:forum_show('<?php echo $category->category_id ?>');" title="<?php echo $this->translate("Collapse"); ?>"></a>
                            </div>
                        <?php endif; ?>
                    </div>	

                    <div id="subcat_<?php echo $category['category_id'] ?>" class="siteforum_category_box">
                        <ul>
                            <?php if (!empty($this->siteforum[$category->category_id])): ?>
                                <?php
                                foreach ($this->siteforum[$category->category_id] as $siteforum) {
                                    $last_topic = $siteforum->getLastUpdatedTopic();
                                    $last_post = null;
                                    $last_user = null;
                                    if ($last_topic) {
                                        $last_post = $last_topic->getLastCreatedPost();
                                        $last_user = $this->user($last_post->user_id);
                                    }
                                    ?>
                                    <li>
                                        <?php if (!empty($this->show_forum_icon)): ?>
                                            <div class="siteforum_icon">
                                                <span class="cat_icon fleft">
                                                    <?php if (!empty($siteforum->photo_id)): ?>
                                                        <?php echo $this->htmlLink($siteforum->getHref(), $this->htmlImage($this->storage->get($siteforum->photo_id, '')->getPhotoUrl())); ?>
                                                    <?php else: ?>
                                                        <?php echo $this->htmlLink($siteforum->getHref(), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Siteforum/externals/images/siteforum.png')) ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="siteforum_info">
                                            <div class="siteforum_title">
                                                <h3>
                                                    <?php echo $this->htmlLink($siteforum->getHref(), $this->translate($siteforum->getTitle())) ?>
                                                </h3>
                                            </div>
                                            <div><?php echo $this->viewMore(strip_tags($siteforum->getDescription()), 130) ?></div>
                                            <?php if ($last_topic && $last_post): ?>
                                                <div class="siteforum_lastpost">
                                                    <?php //echo $this->htmlLink($last_post->getHref(), $this->itemPhoto($last_user, 'thumb.icon'))  ?>
                                                    <span class="siteforum_lastpost_info">
                                                        <?php echo $this->translate('Last post by %1$s, in %2$s', $last_user->__toString(), $this->htmlLink($last_post->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($last_topic->getTitle(), $this->truncationLastPost))) ?>
                                                        <?php echo $this->timestamp($last_post->creation_date, array('class' => 'siteforum_lastpost_date')) ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="siteforum_categories_stats fright">
                                          <div class="fleft mright5">
                                            <div class="siteforum_icon_topic" title="<?php echo $this->translate(array('Topic', 'Topics', $siteforum->topic_count), $this->locale()->toNumber($siteforum->topic_count)) ?>">
                                                <?php echo $siteforum->topic_count; ?>
                                            </div>
                                            <div class="txt_center"><?php echo $this->translate(array('Topic', 'Topics', $siteforum->topic_count), $this->locale()->toNumber($siteforum->topic_count)) ?></div>
                                          </div> 
                                          <div class="fleft">    
                                            <?php if($siteforum->post_count == 0 || $siteforum->post_count > 1):?>
                                            <div class="siteforum_icon_reply" title="<?php echo $this->translate('Posts') ?>">
                                            <?php echo $siteforum->post_count; ?>
                                            </div> 
                                            <div class="txt_center"><?php echo $this->translate('Posts') ?></div>
                                          
                                            <?php else:?>
                                          
                                            <div class="siteforum_icon_reply" title="<?php echo $this->translate('Post') ?>">
                                                <?php echo $siteforum->post_count; ?>
                                            </div> 
                                            <div class="txt_center"><?php echo $this->translate('Post') ?></div>
                                            <?php endif;?>
                                          </div>
                                        </div>    
                                    </li>
                                <?php } ?>
                            <?php endif; ?>

                            <?php if (!empty($this->subCategories[$category->category_id])): ?>            
                                <?php foreach ($this->subCategories[$category->category_id] as $subCategory) { ?>
                                    <?php if (!$this->empty_subcategory[$category->category_id][$subCategory->category_id] || $this->show_empty_category): ?>
                                        <li>
                                            <div class="siteforum_category_header mbot15">
                                                <h3 class="fleft">
                                                    <?php if (!empty($this->show_subcategory_icon)): ?>
                                                        <span class="cat_icon"><?php if ($subCategory['photo_id']): ?><a href="<?php echo $this->url(array('category_id' => $category->category_id, 'subcategory_id' => $subCategory->category_id), 'siteforum_subcategory'); ?>"><img alt=""  src='<?php echo $this->storage->get($subCategory['photo_id'], '')->getPhotoUrl(); ?>' /></a><?php endif; ?></span>
                                                    <?php endif; ?>
                                                    <a href="<?php echo $this->url(array('category_id' => $category->category_id, 'subcategory_id' => $subCategory->category_id), 'siteforum_subcategory') ?>">
                                                        <?php echo $this->translate($subCategory->title); ?>
                                                    </a>
                                                </h3>
                                            </div>

                                            <?php if (!empty($this->siteforum[$subCategory->category_id])): ?>   
                                                <ul>
                                                    <?php
                                                    foreach ($this->siteforum[$subCategory->category_id] as $siteforum) {
                                                        $last_topic = $siteforum->getLastUpdatedTopic();
                                                        $last_post = null;
                                                        $last_user = null;
                                                        if ($last_topic) {
                                                            $last_post = $last_topic->getLastCreatedPost();
                                                            $last_user = $this->user($last_post->user_id);
                                                        }
                                                        ?>
                                                        <li>
                                                            <?php if (!empty($this->show_forum_icon)): ?>
                                                                <div class="siteforum_icon">
                                                                    <span class="cat_icon fleft">
                                                                        <?php if (!empty($siteforum->photo_id)): ?>
                                                                            <?php echo $this->htmlLink($siteforum->getHref(), $this->htmlImage($this->storage->get($siteforum->photo_id, '')->getPhotoUrl())); ?>
                                                                        <?php else: ?>
                                                                            <?php echo $this->htmlLink($siteforum->getHref(), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Siteforum/externals/images/siteforum.png')) ?>
                                                                        <?php endif; ?>
                                                                    </span>
                                                                </div>
                                                            <?php endif; ?>

                                                            <div class="siteforum_info">
                                                                <div class="siteforum_title">
                                                                    <h3>
                                                                        <?php echo $this->htmlLink($siteforum->getHref(), $this->translate($siteforum->getTitle())) ?>
                                                                    </h3>
                                                                </div>
                                                                <div><?php echo $this->viewMore(strip_tags($siteforum->getDescription()), 130) ?></div>
                                                                <?php if ($last_topic && $last_post): ?>
                                                                    <div class="siteforum_lastpost">
                                                                        <span class="siteforum_lastpost_info">
                                                                            <?php
                                                                            echo $this->translate(
                                                                                    'Last post by %1$s, in %2$s', $last_user->__toString(), $this->htmlLink($last_post->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($last_topic->getTitle(), $this->truncationLastPost))
                                                                            )
                                                                            ?>
                                                                            <?php echo $this->timestamp($last_post->creation_date, array('class' => 'siteforum_lastpost_date')) ?>
                                                                        </span>
                                                                    </div>
                                                                <?php endif; ?> 
                                                            </div>

                                                            <div class="siteforum_categories_stats fright">
                                          <div class="fleft mright5">
                                            <div class="siteforum_icon_topic" title="<?php echo $this->translate(array('Topic', 'Topics', $siteforum->topic_count), $this->locale()->toNumber($siteforum->topic_count)) ?>">
                                                <?php echo $siteforum->topic_count; ?>
                                            </div>
                                            <div class="txt_center"><?php echo $this->translate(array('Topic', 'Topics', $siteforum->topic_count), $this->locale()->toNumber($siteforum->topic_count)) ?></div>
                                          </div> 
                                            
                                          <div class="fleft">    
                                            <?php if($siteforum->post_count == 0 || $siteforum->post_count > 1):?>
                                            <div class="siteforum_icon_reply" title="<?php echo $this->translate('Posts') ?>">
                                            <?php echo $siteforum->post_count; ?>
                                            </div> 
                                            <div class="txt_center"><?php echo $this->translate('Posts') ?></div>
                                          
                                            <?php else:?>
                                          
                                            <div class="siteforum_icon_reply" title="<?php echo $this->translate('Post') ?>">
                                                <?php echo $siteforum->post_count; ?>
                                            </div> 
                                            <div class="txt_center"><?php echo $this->translate('Post') ?></div>
                                            <?php endif;?>
                                          </div>
                                        </div>    
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            <?php endif; ?>
                                        </li>
                                    <?php endif; ?>
                                <?php } ?>
                            <?php endif; ?>

                        </ul></div>
                </li>
                <?php
            endif;
        }
        ?>
    </ul>
<?php endif; ?>
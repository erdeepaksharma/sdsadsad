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
<h2><?php echo "Advanced Forums Plugin" ?></h2>

<?php if (count($this->navigation)): ?>
    <div class='tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
    </div>
<?php endif; ?>

<script type="text/javascript">
    var moveCategoryUp = function (category_id) {
        var url = '<?php echo $this->url(array('action' => 'move - category')) ?>';
        var request = new Request.JSON({
            url: url,
            data: {
                format: 'json',
                category_id: category_id
            },
            onComplete: function () {
                window.location.replace(window.location.href);
            }
        });
        request.send();
    }
    var moveSubCategoryUp = function (category_id) {
        var url = '<?php echo $this->url(array('action' => 'move-sub-category')) ?>';
        var request = new Request.JSON({
            url: url,
            data: {
                format: 'json',
                category_id: category_id
            },
            onComplete: function () {
                window.location.replace(window.location.href);
            }
        });
        request.send();
    }
    var moveSiteforumUp = function (forum_id) {
        var url = '<?php echo $this->url(array('action' => 'move - siteforum')) ?>';
        var request = new Request.JSON({
            url: url,
            data: {
                format: 'json',
                forum_id: forum_id
            },
            onComplete: function () {
                window.location.replace(window.location.href);
            }
        });
        request.send();
    }
</script>

<div class="admin_siteforums_options">
    <a href="<?php echo $this->url(array('action' => 'add-category')); ?>" class="buttonlink smoothbox admin_siteforums_createcategory"><?php echo "Add Category" ?></a>
    <a href="<?php echo $this->url(array('action' => 'add-siteforum')); ?>" class="buttonlink smoothbox admin_siteforums_create"><?php echo "Add forum" ?></a>
</div>

<br />

<ul class="admin_siteforum_categories">
    <?php foreach ($this->categories as $category): ?>
        <li>
            <div class="admin_siteforum_categories_info">

                <div class="admin_siteforum_categories_options">
                    <div class="admin_siteforums fleft">
                        <span class="cat_icon fleft"><?php if ($category['photo_id']): ?> <img alt=""  src='<?php echo $this->storage->get($category['photo_id'], '')->getPhotoUrl(); ?>' /><?php endif; ?></span>
                        <span class="admin_siteforums_title fleft"><?php echo $category->getTitle(); ?></span>
                    </div>   
                    <?php if ($category->photo_id != 0) { ?>
                        <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'edit-photo', 'category_id' => $category->getIdentity())); ?>"><?php echo "edit icon" ?></a>
                        | <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'delete-photo', 'category_id' => $category->getIdentity())); ?>"><?php echo "delete icon" ?></a>
                        |
                        <?php
                    } else {
                        ?>

                        <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'photo-upload', 'category_id' => $category->getIdentity())); ?>"><?php echo "add icon" ?></a>
                        |
                        <?php
                    }
                    ?>

                    <a class="smoothbox" href="<?php echo $this->url(array('category_id' => $category->getIdentity(), 'action' => 'add-sub-category')); ?>"><?php echo "add subcategory" ?></a>
                    |
                    <span class="admin_siteforums_moveup">      
                        <?php echo $this->htmlLink('javascript:void(0);', 'move up', array('onclick' => 'moveCategoryUp(' . $category->category_id . ');')); ?> |
                    </span>
                    <a href="<?php echo $this->url(array('action' => 'edit-category', 'category_id' => $category->getIdentity())); ?>" class="smoothbox"><?php echo "edit" ?></a>
                    | <a class="smoothbox" href="<?php echo $this->url(array('action' => 'delete-category', 'category_id' => $category->getIdentity())); ?>"><?php echo "delete" ?></a>
                </div>
                <div class="admin_siteforum_categories_title"> 
                    <ul class="admin_siteforums"  >
                        <?php $forums = Engine_Api::_()->getDbTable('forums', 'siteforum')->getCategoryForum($category); ?>      
                        <?php $counter = 0; ?>
                        <?php foreach ($forums as $siteforum): ?>
                            <li>
                                <div class="admin_siteforums_options">
                                    <span class="admin_siteforums_moveup">
                                        <?php
                                        $counter++;
                                        if ($counter != 1) {
                                            echo $this->htmlLink('javascript:void(0);', 'move up', array('onclick' => 'moveSiteforumUp(' . $siteforum->getIdentity() . ');'));
                                            ?>
                                            |
                                        <?php } ?>

                                    </span>

                                    <?php if ($siteforum->photo_id != 0) { ?>
                                        <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'edit-forum-photo', 'forum_id' => $siteforum->getIdentity())); ?>"><?php echo "edit icon" ?></a>

                                        | <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'forum-photo-delete', 'forum_id' => $siteforum->getIdentity())); ?>"><?php echo "delete icon" ?></a>
                                        |
                                        <?php
                                    } else {
                                        ?>

                                        <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'forum-photo-upload', 'forum_id' => $siteforum->getIdentity())); ?>"><?php echo "add icon" ?></a>
                                        |
                                        <?php
                                    }
                                    ?>

                                    <a href="<?php echo $this->url(array('action' => 'edit-siteforum', 'forum_id' => $siteforum->getIdentity())); ?>" class="smoothbox"><?php echo "edit" ?></a>
                                    |<a class="smoothbox" href="<?php echo $this->url(array('action' => 'delete-siteforum', 'forum_id' => $siteforum->getIdentity())); ?>"><?php echo "delete" ?></a>
                                </div>
                                <span class="cat_icon fleft"><?php if ($siteforum['photo_id']): ?><img alt=""  src='<?php echo $this->storage->get($siteforum['photo_id'], '')->getPhotoUrl(); ?>' /><?php endif; ?></span>
                                <div class="admin_siteforums_info">
                                    <span class="admin_siteforums_title">
                                        <?php echo $siteforum->getTitle(); ?>
                                    </span>
                                    <span class="admin_siteforums_description">
                                        <?php echo $siteforum->getDescription(); ?>
                                    </span>
                                    <span class="admin_siteforums_moderators">
                                        <span class="admin_siteforums_moderators_top">
                                            <?php echo "Moderators" ?>
                                            <a href="<?php echo $this->url(array('action' => 'add-moderator', 'forum_id' => $siteforum->getIdentity())); ?>" class="smoothbox"><?php echo "add" ?></a>
                                        </span>
                                        <span>
                                            <?php
                                            $i = 0;
                                            foreach ($siteforum->getModeratorList()->getAllChildren() as $moderator) {
                                                if ($i > 0) {
                                                    echo ', ';
                                                }
                                                $i++;
                                                echo $moderator->__toString() . ' (<a class="smoothbox" href="' . $this->url(array('action' => 'remove-moderator', 'forum_id' => $siteforum->getIdentity(), 'user_id' => $moderator->getIdentity())) . '">' . "remove" . '</a>)';
                                            }
                                            ?>
                                        </span>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>     

                </div>
            </div>

            <?php $subcategories = Engine_Api::_()->getDbtable('categories', 'siteforum')->getSubCategory($category->getIdentity()); ?>   

            <?php $counter = 0; ?>

            <?php foreach ($subcategories as $subcategory): ?>
                <div class="admin_siteforum_subcategories_info">
                    <div class="events_photo">

                    </div>
                    <div class="admin_siteforum_categories_options">
                        <div class="admin_siteforums fleft">
                            <span class="cat_icon fleft"><?php if ($subcategory['photo_id']): ?><img alt=""  src='<?php echo $this->storage->get($subcategory['photo_id'], '')->getPhotoUrl(); ?>' /><?php endif; ?></span>
                            <span class="admin_siteforums_title fleft"><?php echo $subcategory->getTitle(); ?></span>
                        </div> 

                        <?php
                        $counter++;
                        if ($counter != 1) {
                            echo $this->htmlLink('javascript:void(0);', 'move up', array('onclick' => 'moveSubCategoryUp(' . $subcategory->category_id . ');'));
                            ?>
                            |
                            <?php
                        }
                        ?>

                        <?php if ($subcategory->photo_id != 0) { ?>
                            <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'edit-photo', 'category_id' => $subcategory->getIdentity())); ?>"><?php echo "edit icon" ?></a>
                            | <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'delete-photo', 'category_id' => $subcategory->getIdentity())); ?>"><?php echo "delete icon" ?></a>

                            <?php
                        } else {
                            ?>

                            <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'photo-upload', 'category_id' => $subcategory->getIdentity())); ?>"><?php echo "add icon" ?></a>
                            <?php
                        }
                        ?>
                        | <a href="<?php echo $this->url(array('action' => 'edit-category', 'category_id' => $subcategory->getIdentity())); ?>" class="smoothbox"><?php echo "edit" ?></a>
                        | <a class="smoothbox" href="<?php echo $this->url(array('action' => 'delete-category', 'category_id' => $subcategory->getIdentity())); ?>"><?php echo "delete" ?></a>
                    </div>


                    <ul class="admin_siteforums">
                        <?php foreach ($subcategory->getChildren('forum_forum', array('order' => 'order')) as $siteforum): ?>
                            <li>
                                <div class="admin_siteforums_options">
                                    <span class="admin_siteforums_moveup">

                                        <?php
                                        echo $this->htmlLink('javascript:void(0);', 'move up', array('onclick' => 'moveSiteforumUp(' . $siteforum->getIdentity() . ');'));
                                        ?> |
                                    </span>
                                    <?php if ($siteforum->photo_id != 0) { ?>
                                        <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'edit-forum-photo', 'forum_id' => $siteforum->getIdentity())); ?>"><?php echo "edit icon" ?></a>

                                        | <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'forum-photo-delete', 'forum_id' => $siteforum->getIdentity())); ?>"><?php echo "delete icon" ?></a>

                                        <?php
                                    } else {
                                        ?>

                                        <a class="smoothbox" href="<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'forum-photo-upload', 'forum_id' => $siteforum->getIdentity())); ?>"><?php echo "add icon" ?></a>
                                        <?php
                                    }
                                    ?>
                                    | <a href="<?php echo $this->url(array('action' => 'edit-siteforum', 'forum_id' => $siteforum->getIdentity())); ?>" class="smoothbox"><?php echo "edit" ?></a>
                                    | <a class="smoothbox" href="<?php echo $this->url(array('action' => 'delete-siteforum', 'forum_id' => $siteforum->getIdentity())); ?>"><?php echo "delete" ?></a>
                                </div>
                                <span class="cat_icon fleft"><?php if ($siteforum['photo_id']): ?><img alt=""  src='<?php echo $this->storage->get($siteforum['photo_id'], '')->getPhotoUrl(); ?>' /><?php endif; ?></span>
                                <div class="admin_siteforums_info">
                                    <span class="admin_siteforums_title">
                                        <?php echo $siteforum->getTitle(); ?>
                                    </span>
                                    
                                    <span class="admin_siteforums_description">
                                        <?php echo $siteforum->getDescription(); ?>
                                    </span>
                                    <span class="admin_siteforums_moderators">
                                        <span class="admin_siteforums_moderators_top">
                                            <?php echo "Moderators" ?>
                                            <a href="<?php echo $this->url(array('action' => 'add-moderator', 'forum_id' => $siteforum->getIdentity())); ?>" class="smoothbox"><?php echo "add" ?></a>
                                        </span>
                                        <span>
                                            <?php
                                            $i = 0;
                                            foreach ($siteforum->getModeratorList()->getAllChildren() as $moderator) {
                                                if ($i > 0) {
                                                    echo ', ';
                                                }
                                                $i++;
                                                echo $moderator->__toString() . ' (<a class="smoothbox" href="' . $this->url(array('action' => 'remove-moderator', 'forum_id' => $siteforum->getIdentity(), 'user_id' => $moderator->getIdentity())) . '">' . "remove" . '</a>)';
                                            }
                                            ?>
                                        </span>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>  
                </div>
            <?php endforeach; ?>
        </li>
    <?php endforeach; ?>
</ul>


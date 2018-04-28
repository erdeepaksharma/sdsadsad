<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: my-posts.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php include_once APPLICATION_PATH . '/application/modules/Siteforum/views/scripts/_DashboardNavigation.tpl'; ?> 
<div class="siteforum_dashboard_content">

    <?php if ($this->paginator->getPages()->pageCount > 1): ?>
        <div class="siteforum_header">
            <div class="siteforum_header_pages">
                <?php echo $this->paginationControl($this->paginator); ?>
            </div>
        </div>
    <?php endif; ?>


    <?php if (count($this->paginator) > 0): ?>
        <ul class="siteforum_dashboard_post">
            <?php
            foreach ($this->paginator as $post):
                $user = $post->getOwner();
                $topic = $post->getParent();
                $siteforum = $topic->getParent();
                ?>
                <li>
                    <div class='description'>
                        <?php echo $this->viewMore(strip_tags($post->getDescription()), 120) ?>
                    </div>
                    <div class='siteforum_post_info'>

                        <span class="parent">
                            <?php echo $this->translate('In') ?>
                            <?php echo $this->htmlLink($topic->getHref(), $this->translate($topic->getTitle())) ?>

                            <?php //echo $this->htmlLink($siteforum->getHref(), $this->translate($siteforum->getTitle())) ?>
                        </span>
                        <div class="seaocore_txt_light">
                            <span class='siteforum_time_icon'>
                                <?php echo $this->timestamp($post->creation_date) ?>
                            </span>
                            <span class="siteforum_like_counts" title="<?php echo $this->translate(array('%1$s %2$s Like', '%1$s %2$s Likes', $post->like_count), $this->locale()->toNumber($post->like_count), '') ?> " >
                                <?php echo $post->like_count ?>
                            </span>
                        </div>
                    </div>

                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="tip">
            <span>
                <?php echo $this->translate('You haven\'t replied to any topic yet.') ?>
            </span>
        </div>
    <?php endif; ?>
    <div class="siteforum_header_pages">
        <?php echo $this->paginationControl($this->paginator); ?>
    </div>
</div>
</div>

<script type="text/javascript">
    $$('.core_main_siteforum').getParent().addClass('active');
</script>

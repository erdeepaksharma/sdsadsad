<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$this->headTranslate(array('Disconnect from Instagram', 'We were unable to process your request. Wait a few moments and try again.', 'Updating...', 'Are you sure that you want to delete this comment? This action cannot be undone.', 'Delete', 'cancel', 'Close', 'You need to be logged into Instagram to see your Instagram Connections Feed.', 'Click here'));
?>
<?php if (empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate)) : ?>
    <?php if (empty($this->isajax) && empty($this->tabaction)) { ?>   
        <div id="showadvfeed-instagramfeed">
        <?php } ?> 
        <?php if ($this->response) : ?>

            <script type='text/javascript'>
                action_logout_taken_instagram = 0;
            </script>
        <?php else : ?>
            <div class="white">
                <?php
                if (!empty($this->instagramLoginURL)) :
                    if (!empty($this->is_instagram_ajax)):
                        echo '<div class="clr ui-icon-instagram-sign"><a class="t_l" data-icon="instagram-sign"  data-role="button" href="javascript:void(0);" onclick= "sm4.socialactivity.socialFeedLogin(\'' . $this->instagramLoginURL . '\', \' widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed \', \'instagramfeed\')" >' . $this->translate('Sign in to Instagram') . '</a></div>';
                    endif;
                    ?>
                    <script type='text/javascript'>
                        action_logout_taken_instagram = 1;
                    </script>

                    <?php
                    return;
                else :
                    ?>
                    <div class="aaf_feed_tip"> 
                        <?php echo $this->translate("There are no posts to show.") ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
        endif;
        $viewer = Engine_Api::_()->user()->getViewer();

        if ($this->response > 0 && empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate)) :
            ?>
            <ul id="activity-feed-instagramfeed" class="feeds">
            <?php endif; ?>  
        <?php endif; ?>
        <?php
        $view_moreconnection_instagram = 0;
        if (empty($this->isajax) || !empty($this->next_previous)) :
            $view_moreconnection_instagram = 1;
        endif;
        ?>
        <?php
        $execute_script = 1;
        $current_instagram_timestemp = 0;
        ?>
        <?php
        if ($this->instagram_FeedCount > 0 || 1) :
            $Api_instagram = new Seaocore_Api_Instagram_Api();

            foreach ($this->response as $data) :
                $showFeedSize = $this->showFeedSize;
                $link = $data->link;
                $id = $data->id;
                $caption = $data->caption->text;
                $author = $data->caption->from->username;
                $thumbnail = $data->images->$showFeedSize->url;
                $rand = rand(5, 15);
                ?>
                <li id="activity-item-<?php echo $rand ?>-instagram" class="photo">
                    <div id="main-feed-<?php echo $rand ?>-instagram" class="phot_head">
                        <center>
                                <?php if (!empty($this->showInHeader)): ?>
                                <a style="margin-top:0;" href="http://instagram.com/<?php echo $data->user->username; ?>" target="_blank" class="ui-btn">
                                    <?php echo '@' . $data->user->username; ?>
                                </a>
                                <?php else: ?>
                                <time style="font-weight: 700;">
            <?php
            echo date('d M Y', $data->created_time);
            ?>
        <?php endif; ?>
                            </time>
                        </center>

                        <div> <a href="<?php echo $link ?>" target="_blank"><center><img src="<?php echo $thumbnail ?>" alt=""/></center></a></div>
                        <div class="feed_item_option">
                            <div data-inset="false" data-role="navbar" class="ui-navbar" role="navigation">
                                <ul class="ui-grid-a">
                                    <li class="ui-block-a">
                                    <center class="ui-btn ui-btn-active">
                                        <i class="ui-icon ui-icon-thumbs-up-alt feed-like-icon"></i> 
                                        <?php echo $data->likes->count . ' '; ?>
                                    </center>
                                    </li>
                                    <li class="ui-block-b">
                                    <center class="ui-btn ui-btn-active">
                                        <i class="ui-icon ui-icon-comment"></i>
        <?php echo $data->comments->count; ?>
                                    </center>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
    <?php endforeach; ?>
            <?php if ($this->response > 0 && empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate)) : ?>
            </ul>

                <?php if ($this->is_first_time): ?>
                <br />
                <div class = "seaocore_view_more mtop10" id="seaocore_view_more">
                    <?php
                    echo $this->htmlLink('javascript:void(0);', $this->translate(''), array(
                        'id' => '',
                        'class' => 'buttonlink icon_viewmore'
                    ))
                    ?>
                </div>
                <div class="feeds_loading" id="loading_image" style="display: none;">
                    <i class="icon_loading"></i>
                </div>

                <div class="aaf_feed_tip" id="feed_no_more_instagram" style="display: none;"> 
                <?php echo $this->translate("There are no more posts to show.") ?>
                </div>
                <div id="hideResponse_div"></div>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <?php
        if (!empty($this->instagramLoginURL) && empty($this->isajax)) {
            $execute_script = 0;
            ?>
            <div class="aaf_feed_tip"><?php echo $this->translate('Instagram is currently experiencing technical issues, please try again later.'); ?></div>
    <?php } else { ?>
    <?php } ?>       
<?php endif; ?> 

<?php if (empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate) && empty($this->tabaction)) : ?>

    </div> 
<?php endif; ?>
<?php if (empty($this->isajax) || 1): ?>
    <script type="text/javascript">

        var canFeedMoreData = true;
        sm4.core.runonce.add(function() {
            hideViewMoreLink();
        });

        function hideViewMoreLink() {
            window.onscroll = doOnScrollLoadFeeds;
        }


        function viewMorePhoto()
        {
            $('#seaocore_view_more').css("display", "none");
            $('#loading_image').css("display", "block");
            sm4.core.request.send({
                type: "POST",
                'url': sm4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed',
                data: {
                    format: 'html',
                    is_instagram_ajax: 0,
                    is_ajax: 1,
                    new_max_id: '<?php echo $id; ?>',
                    advancedactivity_show_in_header: '<?php echo $this->showInHeader; ?>',
                    instagram_feed_count: '<?php echo $this->feedCount ?>',
                    instagram_image_width: '<?php echo $this->feedwidth ?>',
                    homefeed: '<?php echo $this->isHomeFeeds; ?>'
                },
                success: function(responseTree, responseElements, responseHTML, responseJavaScript) {
                    $('#hideResponse_div').html(responseHTML.responseText);
                    var photocontainer = $('#hideResponse_div').html();
                    $('#activity-feed-instagramfeed').append(photocontainer);

                    setTimeout(function() {
                        $('#hideResponse_div').html("");
                        canFeedMoreData = true;
                    }, 5000);
                }

            });
            return false;
        }

        function doOnScrollLoadFeeds()
        {
            if (typeof ($('#seaocore_view_more').get(0)) != 'undefined') {
                var elementPostionY = $('#seaocore_view_more').get(0).offsetTop;
            } else {
                var elementPostionY = $('#seaocore_view_more').get(0).y;
            }
            if(sm4.core.isApp()){
                viewMorePhoto();    
            }
            else{
            if (elementPostionY <= $(window).scrollTop() + ($(window).height() - 40)) {
                if (canFeedMoreData) {
                    viewMorePhoto();
                }
            }
          }

        }
    </script>
<?php endif; ?>
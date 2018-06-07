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
<?php if (empty($this->homefeeds)): ?>
    <script type="text/javascript"> var isHomeFeedsWidget = false;</script>
    <?php
    $this->headScript()
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/core.js')
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/advancedactivity-instagram.js')
            ->appendFile($this->layout()->staticBaseUrl . 'externals/mdetect/mdetect' . ( APPLICATION_ENV != 'development' ? '.min' : '' ) . '.js');
    ?>
<?php else: ?>
    <script type="text/javascript"> var isHomeFeedsWidget = true;</script>
<?php endif; ?>

<?php if (!empty($this->is_instagram_ajax)): ?>
    <style type="text/css">
        .adv_instagram_photo{
            display: inline-block;
            margin: 5px 17px 5px 4px;
            padding: 6px 20px 6px 11px;
            position: relative;
            width: <?php echo $this->feedwidth ?>px;
        }
        .adv_instagram_photo:nth-child(3n) {
            margin-right: 0;
        }  
        .adv_instagram_photo::before {
            bottom: auto;
            content: "";
            left: 30px;
            min-height: 185px;
            position: absolute;
            right: 0;
            top: 25px;
            width: 71.8%;
            transition: all 0.3s ease-in 0s;
            z-index:-1;
        }

        .adv_instagram_photo:hover::before {
            background:#eaeaea;
            bottom:0;
            left:0px;
            min-height:inherit;
            right: 0;
            top: 0px;
            width: 100%;
            box-shadow:0 0 0 1px rgba(0, 0, 0, 0.04), 0 1px 5px rgba(0, 0, 0, 0.2);
        }
        .phot_head{text-align:center;margin:4px 0;opacity:0;transition: all 0.2s ease-in 0s;}
        .phot_foot{text-align:center;margin:4px 0;opacity:0;transition: all 0.2s ease-in 0s;}
        .adv_instagram_photo:hover .phot_head, .adv_instagram_photo:hover .phot_foot{opacity:1;}
        .adv_instagram_photo img {
            background: none repeat scroll 0 0 #eaeaea;
            padding: 4px;
            width:100%;
            box-shadow:0 0 0 1px rgba(0, 0, 0, 0.04), 0 1px 5px rgba(0, 0, 0, 0.2);
        }
        .adv_instagram_photo:hover img{box-shadow:none;}
        .adv_instagram_photo .phot_foot img {
            box-shadow: none;
            float: none;
            left: 2px;
            margin: 0;
            position: relative;
            top: 3px;
        }
        .adv_instagram_photo .phot_foot > span {
            margin-right: 5px;
        }
        .adv_instagram_photo .phot_foot > span:last-child{margin-right:0;}
    </style>
<?php endif; ?>
<?php
$this->headTranslate(array('Disconnect from Instagram', 'We were unable to process your request. Wait a few moments and try again.', 'Updating...', 'Are you sure that you want to delete this comment? This action cannot be undone.', 'Delete', 'cancel', 'Close', 'You need to be logged into Instagram to see your Instagram Connections Feed.', 'Click here'));
?>
<script type="text/javascript">
    update_freq_instagram = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.update.frequency', 120000); ?>;
</script>

<?php if (empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate)) : ?>
    <?php if ($this->response) : ?>

        <script type='text/javascript'>
            action_logout_taken_instagram = 0;
        </script>

    <?php else : ?>

        <div class="white">
            <?php
            if (!empty($this->instagramLoginURL)) :
                if (!empty($this->is_instagram_ajax)):
                    echo '<div class="aaf_feed_tip">' . $this->translate('You need to be logged into Instagram to see your Instagram Connections Feed.') . ' <a href="javascript:void(0);" onclick= "AAF_ShowFeedDialogue_Instagram()" >' . $this->translate('Click here') . '</a>.</div>';
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

    <?php endif; ?>
<?php endif; ?>

<?php
if (!empty($this->checkUpdate)): // if this is for the live update
    if ($this->instagram_FeedCount > 0)
        echo "<script type='text/javascript'>
                
          if($('update_advfeed_instagramblink') && activity_type != 6)
           $('update_advfeed_instagramblink').style.display = 'block';
         </script>
        <div class='aaf_feed_tip_more'>
          <a href='javascript:void(0);' onclick='javascript:activityUpdateHandler_instagram.getFeedUpdate();feedUpdate_instagram.empty();'>
            <i class='aaf_feed_more_arrow'></i><b>{$this->translate(array('%d new update is available - click this to show it.', '%d new updates are available - click this to show them.', $this->instagram_FeedCount), $this->instagram_FeedCount)}
          </b></a>
        </div>";
    return; // Do no render the rest of the script in this mode
endif;
?>

<?php
$execute_script = 1;
?>
<?php
if ($this->response) :
    $Api_instagram = new Seaocore_Api_Instagram_Api();
    $prev_timestamp = 0;
    ?>
<?php else: ?>
    <?php
    if (!empty($this->instagramLoginURL) && !empty($this->is_instagram_ajax)) :
        $execute_script = 0;
        ?>
        <div class="aaf_feed_tip">
            <?php echo $this->translate('Instagram is currently experiencing technical issues, please try again later.'); ?>
        </div>

    <?php else : ?>

        <script type="text/javascript">
            if ($('feed_viewmore_instagram')) {
                if (autoScrollFeedAAFEnable)
                    window.onscroll = "";
                feed_viewmore_instagram.style.display = 'none';
                feed_loading_instagram.style.display = 'none';
                feed_no_more_instagram.style.display = '';
            }
        </script>

    <?php endif; ?>       

<?php endif; ?>  

<?php if ($this->is_first_time): ?>
    <ul id="adv_instagram_photos">
    <?php endif; ?>
    <?php
    foreach ($this->response as $data) :
        $showFeedSize = $this->showFeedSize;
        $link = $data->link;
        $id = $data->id;
        $caption = $data->caption->text;
        $author = $data->caption->from->username;
        $thumbnail = $data->images->$showFeedSize->url;
        ?>
        <li class="adv_instagram_photo">
            <div class="phot_head">
                <span>
                    <?php if (!empty($this->showInHeader)): ?>
                        <a href="http://instagram.com/<?php echo $data->user->username; ?>" target="_blank">
                            <?php echo '@' . $data->user->username; ?>
                        </a>
                    <?php else: ?>
                        <time style="font-weight: 700;">
                            <?php
                            // echo $this->locale()->useDateLocaleFormat();
                            echo date('d M Y', $data->created_time);
                            ?>
                        <?php endif; ?>
                    </time>
                </span>
            </div>
            <a href="<?php echo $link ?>" target="_blank"><span></span><img src="<?php echo $thumbnail ?>" /></a>

            <div class="phot_foot"><a href="<?php echo $link; ?>" target="_blank" style="text-decoration: none !important;"><span><img style="height: 16px; width: 16px; padding: inherit;"  src="<?php echo $this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/i_like.png' ?>"> <?php echo $data->likes->count . ' '; ?></span><span><img style="height: 16px; width: 16px;padding: inherit;" src="<?php echo $this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/i_comment.png' ?>"> <?php echo $data->comments->count; ?></span></a></div>
        </li>
    <?php endforeach; ?>

</ul>
<?php if ($this->is_first_time && empty($this->disableViewMore)): ?>
    <br />
    <div class = "seaocore_view_more mtop10" id="seaocore_view_more">
        <?php
        echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
            'id' => '',
            'class' => 'buttonlink icon_viewmore'
        ))
        ?>
    </div>
    <div class="seaocore_view_more" id="loading_image" style="display: none;">
        <img src='<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Seaocore/externals/images/core/loading.gif' style='margin-right: 5px;' />
        <?php echo $this->translate("Loading ...") ?>
    </div>

    <div class="aaf_feed_tip" id="feed_no_more_instagram" style="display: none;"> 
        <?php echo $this->translate("There are no more posts to show.") ?>
    </div>


    <div id="hideResponse_div"></div>
<?php endif; ?>

<script type="text/javascript">

    var canFeedMoreData = true;
    en4.core.runonce.add(function () {
<?php if (empty($this->disableViewMore)): ?>
            hideViewMoreLink();
<?php endif; ?>
    });

    function hideViewMoreLink() {
        window.onscroll = doOnScrollLoadFeeds;
    }


    function viewMorePhoto()
    {
        $('seaocore_view_more').style.display = 'none';

<?php if (!empty($this->paginationNextUrlLikes) || !empty($this->paginationNextUrlUploades)): ?>

            en4.core.request.send(new Request.HTML({
                'url': en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed',
                data: {
                    format: 'html',
                    is_instagram_ajax: 0,
                    new_max_id: '<?php echo $id; ?>',
                    pagination_next_url_likes: '<?php echo $this->paginationNextUrlLikes; ?>',
                    pagination_next_url_uploades: '<?php echo $this->paginationNextUrlUploades; ?>',
                    advancedactivity_show_in_header: '<?php echo $this->showInHeader; ?>',
                    instagram_feed_count: '<?php echo $this->feedCount ?>',
                    instagram_image_width: '<?php echo $this->feedwidth ?>',
                    homefeed: '<?php echo $this->isHomeFeeds; ?>',
                    is_ajax_request: true
                },
                onRequest: function () {
                    $('loading_image').style.display = 'block';
                },
                onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
                    $('hideResponse_div').innerHTML = responseHTML;
                    var photocontainer = $('hideResponse_div').getElement('.layout_advancedactivity_advancedactivityinstagram_userfeed').innerHTML;
                    if ($('hideResponse_div').getElement('.layout_advancedactivity_advancedactivityinstagram_userfeed').getElement('.white')) {
                        canFeedMoreData = false;
                        $('seaocore_view_more').style.display = 'none';
                        $('feed_no_more_instagram').style.display = 'block';
                    } else {
                        $('seaocore_view_more').style.display = 'block';
                        $('adv_instagram_photos').innerHTML = $('adv_instagram_photos').innerHTML + photocontainer;
                    }
                    $('hideResponse_div').innerHTML = "";
                    $('loading_image').style.display = 'none';

                }
            }));
<?php else: ?>
            canFeedMoreData = false;
            $('seaocore_view_more').style.display = 'none';
            $('loading_image').style.display = 'none';
            $('feed_no_more_instagram').style.display = 'block';
<?php endif; ?>
        return false;
    }

    function doOnScrollLoadFeeds()
    {
        if (typeof ($('seaocore_view_more').offsetParent) != 'undefined') {
            var elementPostionY = $('seaocore_view_more').offsetTop;
        } else {
            var elementPostionY = $('seaocore_view_more').y;
        }
        if (elementPostionY <= window.getScrollTop() + (window.getSize().y - 30)) {
            if (canFeedMoreData)
                viewMorePhoto();
        }
    }

    function applyclassfocus(el) {
        if (!el.hasClass('pgmiImageLinkFocussed'))
            el.addClass('pgmiImageLinkFocussed');
        else {
            el.removeClass('pgmiImageLinkFocussed');
        }
    }

    if (window.opener != null) {
<?php if (!empty($_GET['redirect_instagram'])) : ?>
            window.opener.location.reload(false);
            close();

<?php endif; ?>
    }
</script>

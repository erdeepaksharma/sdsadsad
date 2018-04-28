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
$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Siteforum/externals/scripts/core.js');
?>

<?php
$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
        . 'application/modules/Siteforum/externals/styles/style_siteforum.css');
?>

<?php $this->tinyMCESEAO()->addJS(); ?>
<script type="text/javascript">
    en4.core.runonce.add(function () {
        var pre_rate = <?php echo $this->topic->rating; ?>;
        var rated = '<?php echo $this->rated; ?>';
        var topic_id = <?php echo $this->topic->topic_id; ?>;
        var total_votes = <?php echo $this->rating_count; ?>;
        var viewer = <?php echo $this->viewer_id; ?>;
        var new_text = '';

        var rating_over = window.rating_over = function (rating) {
            if (rated == 1) {
                $('rating_text').innerHTML = "<?php echo $this->translate('you already rated'); ?>";
                //set_rating();
            } else if (viewer == 0) {
                $('rating_text').innerHTML = "<?php echo $this->translate('please login to rate'); ?>";
            } else {
                $('rating_text').innerHTML = "<?php echo $this->translate('click to rate'); ?>";
                for (var x = 1; x <= 5; x++) {
                    if (x <= rating) {
                        $('rate_' + x).set('class', 'seao_rating_star_generic rating_star_y');
                    } else {
                        $('rate_' + x).set('class', 'seao_rating_star_generic seao_rating_star_disabled');
                    }
                }
            }
        }

        var rating_out = window.rating_out = function () {
            if (new_text != '') {
                $('rating_text').innerHTML = new_text;
            }
            else {
                $('rating_text').innerHTML = " <?php echo $this->translate(array('%s rating', '%s ratings', $this->rating_count), $this->locale()->toNumber($this->rating_count)) ?>";
            }
            if (pre_rate != 0) {
                set_rating();
            }
            else {
                for (var x = 1; x <= 5; x++) {
                    $('rate_' + x).set('class', 'seao_rating_star_generic seao_rating_star_disabled');
                }
            }
        }

        var set_rating = window.set_rating = function () {

            var rating = pre_rate;
            if (new_text != '') {
                $('rating_text').innerHTML = new_text;
            }
            else {
                $('rating_text').innerHTML = "<?php echo $this->translate(array('%s rating', '%s ratings', $this->rating_count), $this->locale()->toNumber($this->rating_count)) ?>";
            }
            for (var x = 1; x <= parseInt(rating); x++) {
                $('rate_' + x).set('class', 'seao_rating_star_generic rating_star_y');
            }

            for (var x = parseInt(rating) + 1; x <= 5; x++) {
                $('rate_' + x).set('class', 'seao_rating_star_generic seao_rating_star_disabled');
            }

            var remainder = Math.round(rating) - rating;
            if (remainder <= 0.5 && remainder != 0) {
                var last = parseInt(rating) + 1;
                $('rate_' + last).set('class', 'seao_rating_star_generic rating_star_half_y');
            }
        }

        var rate = window.rate = function (rating) {
            $('rating_text').innerHTML = "<?php echo $this->translate('Thanks for rating!'); ?>";
            for (var x = 1; x <= 5; x++) {
                $('rate_' + x).set('onclick', '');
            }
            (new Request.JSON({
                'format': 'json',
                'url': '<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'index', 'action' => 'rate'), 'default', true) ?>',
                'data': {
                    'format': 'json',
                    'rating': rating,
                    'topic_id': topic_id
                },
                'onRequest': function () {
                    rated = 1;
                    total_votes = total_votes + 1;
                    pre_rate = (<?php echo Engine_Api::_()->getDbTable('ratings', 'siteforum')->totalRating($this->topic->topic_id); ?> + rating) / total_votes;
                    set_rating();
                },
                'onSuccess': function (responseJSON, responseText)
                {
                    $('rating_text').innerHTML = responseJSON[0].total + " ratings";
                    new_text = responseJSON[0].total + " ratings";

                }
            })).send();
        }

        var tagAction = window.tagAction = function (tag) {
            $('tag').value = tag;
            $('filter_form').submit();
        }

        set_rating();
    });

    var rate = window.thank = function (user_id, post_id) {
        (new Request.JSON({
            'format': 'json',
            'url': '<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'topic', 'action' => 'thank'), 'default', true) ?>',
            'data': {
                'format': 'json',
                'user_id': user_id,
                'post_id': post_id
            },
            'onRequest': function () {
                timer = (function () {
                    $('thank_link_' + post_id).innerHTML = '<a href="javascript:void(0);"><img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif" /></a>';

                }).delay(5);
            },
            'onSuccess': function (responseJSON, responseText)
            {
                user = responseJSON[0].user;
                thanked = responseJSON[0].thanked;
                thanks = responseJSON[0].thanks;
                $('thank_link_' + post_id).innerHTML = '';
                $$('.user_thanks_' + user_id).each(function (el) {
                    el.innerHTML = "<a class='siteforum_thanks_icon' href='javascript:void(0);'  onclick='showMemberList(" + post_id + ") ;' title='Thank(s)'>" + thanks + "</a>";
                });

                $$('.user_thanked_' + user).each(function (el) {
                    el.innerHTML = thanked;
                });
            }
        })).send();
    }

    var showMemberList = function (post_id) {
        SmoothboxSEAO.open('<center><div class="siteforum_profile_loading_image"></div></center>');
        en4.core.request.send(new Request.HTML({
            'url': '<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'post', 'action' => 'view'), 'default', true); ?>',
            'data': {
                'format': 'html',
                'is_ajax_load': 1,
                'topic_post_id': post_id,
            },
            onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
                if ($$('.seao_smoothbox_lightbox_overlay').isVisible() == 'true') {
                    SmoothboxSEAO.close();
                    SmoothboxSEAO.open('<div>' + responseHTML + '</div>');
                }
            }
        }), {
            'force': true
        });
    }

    var showRepList = function (topic_id) {
        SmoothboxSEAO.open('<center><div class="siteforum_profile_loading_image"></div></center>');
        en4.core.request.send(new Request.HTML({
            'url': '<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'topic', 'action' => 'reputation-view'), 'default', true); ?>',
            'data': {
                'format': 'html',
                'is_ajax_load': 1,
                'topic_post_id': topic_id,
            },
            onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
                if ($$('.seao_smoothbox_lightbox_overlay').isVisible() == 'true') {
                    SmoothboxSEAO.close();
                    SmoothboxSEAO.open('<div>' + responseHTML + '</div>');
                }
            }
        }), {
            'force': true
        });
    }
    var subscribeTopic = function (watch, topic_id) {
        (new Request.JSON({
            'format': 'json',
            'url': '<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'topic', 'action' => 'watch'), 'default', true) ?>',
            'data': {
                'format': 'json',
                'watch': watch,
                'topic_id': topic_id,
            },
            'onRequest': function () {
                timer = (function () {

                    $('subscribe_link_' + topic_id).innerHTML = '<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif" />';
                }).delay(5);

            },
            'onSuccess': function (responseJSON, responseText)
            {
                if (responseJSON[0].isWatching == 1) {
                    $('subscribe_link_' + topic_id).innerHTML = '<a href="javascript:void(0);" class="siteforum_icon_subscribe" onclick="subscribeTopic(1,<?php echo $this->topic->topic_id ?>)"><?php echo $this->translate('Subscribe Topic');?></a>';

                } else {
                    $('subscribe_link_' + topic_id).innerHTML = '<a href="javascript:void(0);" class="siteforum_icon_subscribe" onclick="subscribeTopic(0,<?php echo $this->topic->topic_id ?>)"><?php echo $this->translate('Unsubscribe Topic');?></a>';
                }
            }
        })).send();
    }

</script>

<div class="siteforum_topic_title_wrapper">
    <div class="siteforum_topic_title fleft">
        <h3><?php echo $this->topic->getTitle() ?></h3>
    </div>
    <div class="siteforum_topic_title_options">
        <?php
        $params = array();
        echo $this->htmlLink($this->siteforum->getHref(), $this->translate('Back To Topics'), array('class' => 'siteforum_icon_back'))
        ?>
        <?php if ($this->canPost): ?>
            <?php echo $this->htmlLink($this->topic->getHref(array('action' => 'post-create', 'page' =>$this->total_page)), $this->translate('Post Reply'), array('class' => 'siteforum_icon_postreply')) ?>
        <?php endif; ?>
        <span id="subscribe_link_<?php echo $this->topic->topic_id ?>">
            <?php if ($this->viewer->getIdentity()): ?>
                <?php if (!$this->isWatching): ?>
                    <a href="javascript:void(0);" class="siteforum_icon_subscribe" onclick="subscribeTopic(1,<?php echo $this->topic->topic_id ?>);"><?php echo $this->translate('Subscribe Topic');?></a>
                    <?php //echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '1')), $this->translate('Watch Topic'), array('class' => 'siteforum_icon_watch'))?>                   
                <?php else: ?>
                    <a href="javascript:void(0);" class="siteforum_icon_subscribe" onclick="subscribeTopic(0,<?php echo $this->topic->topic_id ?>);"><?php echo $this->translate('Unsubscribe Topic');?></a>
                    <?php //echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '0')), $this->translate('Stop Watching Topic'), array('class' => 'siteforum_icon_unwatch'))?>
                <?php endif; ?>
        
            <?php endif; ?>
        </span>
        <?php if ($this->viewer->getIdentity()): ?>
            <!--  Start: Suggest to Friend link show work -->
              <?php if( !empty($this->forumSuggLink) ): ?>				
                    <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'suggestion', 'controller' => 'index', 'action' => 'popups', 'sugg_id' => $this->topic->topic_id, 'sugg_type' => 'forum_topic'), $this->translate('Suggest to Friends'), array(
                        'class'=>'buttonlink  icon_page_friend_suggestion smoothbox')) ?>
              <?php endif; ?>					
            <!--  End: Suggest to Friend link show work -->
            <?php endif; ?>
    </div>

</div>

<div class="siteforum_topic_options">
    <div>
        <?php if ($this->canEdit): ?>
            <?php if (!$this->topic->sticky): ?>
                <?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '1', 'reset' => false), $this->translate('Make Sticky'), array('class' => 'siteforum_icon_sticky')) ?>
            <?php else: ?>
                <?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '0', 'reset' => false), $this->translate('Remove Sticky'), array('class' => 'siteforum_icon_remove')) ?>
            <?php endif; ?>
            <?php if (!$this->topic->closed): ?>
                <?php echo $this->htmlLink(array('action' => 'close', 'close' => '1', 'reset' => false), $this->translate('Close'), array('class' => 'siteforum_icon_closelock')) ?>
            <?php else: ?>
                <?php echo $this->htmlLink(array('action' => 'close', 'close' => '0', 'reset' => false), $this->translate('Open'), array('class' => 'siteforum_icon_openlock')) ?>
            <?php endif; ?>
            <?php echo $this->htmlLink(array('action' => 'rename', 'reset' => false), $this->translate('Rename'), array('class' => 'smoothbox siteforum_icon_edit')) ?>
            <?php echo $this->htmlLink(array('action' => 'move', 'reset' => false), $this->translate('Move'), array('class' => 'smoothbox siteforum_icon_move')) ?>
        <?php endif; ?>
        <?php if ($this->viewer()->getIdentity()): ?>
            <span id="siteforum_topic_like_unlike_<?php echo $this->subject()->getIdentity() ?>">
                <?php if ($this->subject()->likes()->isLike($this->viewer())): ?>
                    <a href="javascript:void(0);" class="siteforum_icon_unlike" onclick="en4.siteforum.topics.unlike('<?php echo $this->subject()->getIdentity() ?>')"><?php echo $this->translate('Unlike') ?></a>
                <?php else: ?>
                    <a href="javascript:void(0);" class="siteforum_icon_like" onclick="en4.siteforum.topics.like('<?php echo $this->subject()->getIdentity() ?>')"><?php echo $this->translate('Like') ?></a>
                <?php endif; ?>
            </span>   
        <?php endif; ?>

        <div id="siteforum_share">
            <?php
            $this->subject = $this->topic;
            include APPLICATION_PATH . '/application/modules/Siteforum/views/scripts/_shareTopicButtons.tpl';
            ?>
        </div>  

        <?php if ($this->canDelete): ?>
            <?php echo $this->htmlLink(array('action' => 'delete', 'reset' => false), $this->translate('Delete'), array('class' => 'smoothbox siteforum_icon_delete')) ?>
        <?php endif; ?>  
        <?php if ($this->viewer->getIdentity()): ?>
            <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.rating', 1)) { ?>             
                <span id="siteforum_rating" class="rating fright" onmouseout="rating_out();">
                    <span id="rating_text" class="rating_text mright5"><?php echo $this->translate('click to rate'); ?></span>
                    <span id="rate_1" class="seao_rating_star_generic rating_star_y" <?php if (!$this->rated && $this->viewer_id): ?>onclick="rate(1);"<?php endif; ?> onmouseover="rating_over(1);"></span>
                    <span id="rate_2" class="seao_rating_star_generic rating_star_y" <?php if (!$this->rated && $this->viewer_id): ?>onclick="rate(2);"<?php endif; ?> onmouseover="rating_over(2);"></span>
                    <span id="rate_3" class="seao_rating_star_generic rating_star_y" <?php if (!$this->rated && $this->viewer_id): ?>onclick="rate(3);"<?php endif; ?> onmouseover="rating_over(3);"></span>
                    <span id="rate_4" class="seao_rating_star_generic rating_star_y" <?php if (!$this->rated && $this->viewer_id): ?>onclick="rate(4);"<?php endif; ?> onmouseover="rating_over(4);"></span>
                    <span id="rate_5" class="seao_rating_star_generic rating_star_y" <?php if (!$this->rated && $this->viewer_id): ?>onclick="rate(5);"<?php endif; ?> onmouseover="rating_over(5);"></span>
                </span>

            <?php } ?>
        <?php endif; ?>
    </div>

    <?php if (Count($this->topicTags)): ?>
        <div class="mtop10 siteforum_topic_view_tags">
            <?php echo $this->translate(array('Tag : ', 'Tags : ', Count($this->topicTags)), $this->locale()->toNumber(Count($this->topicTags)), '')?>
            <?php if (empty($this->topicTags)) : ?><?php echo $this->translate('None'); ?> <?php endif; ?>
            <?php foreach ($this->topicTags as $tag): ?>
                <a href='<?php echo $this->url(array('action' => 'search'), "siteforum_general"); ?>?tag=<?php echo $tag->getTag()->text ?>&tag_id=<?php echo $tag->getTag()->getIdentity() ?>'><?php echo $tag->getTag()->text ?></a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="mtop10 siteforum_topic_view_tags">
            <?php echo $this->translate('Tags : '); ?>  
            <?php echo $this->translate('None'); ?> 
        </div>
    <?php endif; ?>

    <!--Like Counts-->
    <div id='topic_likes' class="fright mtop10"><a class="smoothbox" href='<?php echo $this->url(array('module' => 'seaocore', 'controller' => 'like', 'action' => 'likelist', 'resource_type' => $this->subject()->getType(), 'resource_id' => $this->subject()->getIdentity(), 'call_status' => 'public'), 'default', true); ?>'><span class="siteforum_icon_like" title="<?php echo $this->translate("Topic Likes"); ?>"><?php echo $this->subject()->like_count; ?></span></a></div>       
</div>

<?php if ($this->topic->closed): ?>
    <div class="siteforum_discussions_thread_options_closed siteforum_icon_closelock seaocore_txt_light">
        <?php echo $this->translate('This topic has been closed.'); ?>
    </div>
<?php endif; ?>

<script type="text/javascript">
    en4.core.runonce.add(function () {
        $$('.siteforum_topic_posts_info_body').enableLinks();
        var post_id = <?php echo sprintf('%d', $this->post_id) ?>;
        if (post_id > 0) {
            window.scrollTo(0, $('siteforum_post_' + post_id).getPosition().y);
        }
    });
</script>

<ul class="siteforum_topics">
    <?php foreach ($this->paginator as $i => $post): ?>
        <?php $user = $this->user($post->user_id); $user_name = $user->getTitle()?>
        <?php $signature = $post->getSignature(); ?>
        <?php $isModeratorPost = $this->siteforum->isModerator($post->getOwner()) ?>
        <li id="siteforum_post_<?php echo $post->post_id ?>" class="siteforum_nth_<?php echo $i % 2 ?><?php if ($isModeratorPost): ?> siteforum_moderator_post<?php endif ?>">
            <div class="siteforum_topic_posts_author fleft">
                <div>
                    <div><?php echo $this->htmlLink($user->getHref(), $user->getTitle(), array('class' => 'bold')) ?></div>
                    <div class="siteforum_topics_icon">
                        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')); ?>
                        
                        <?php if ($this->onlineIcon && Engine_Api::_()->siteforum()->isOnline($user->getIdentity())) { ?>
                            <span class="seao_online_icon fright seaocore_txt_light" >
                                <i title=<?php echo $this->translate("Online")?>></i>
                            </span>
                        <?php } ?>    
                    </div>  
                </div>
                <div class="siteforum_topic_posts_author_info clr">
                    <?php if ($post->user_id != 0 && $post->getOwner() && $isModeratorPost): ?>
                        <div class="siteforum_topic_posts_author_info_title"><?php echo $this->translate('Moderator') ?></div>
                    <?php endif; ?>
                    <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.thanks', 1)): ?>
                        <?php $no_of_thanks = Engine_Api::_()->getDbTable('thanks', 'siteforum')->countThanks($user->getIdentity()); ?>          
                        <span class="user_thanks_<?php echo $user->getIdentity() ?>"> 
                            <?php if (!empty($no_of_thanks)) { ?>
                                <a href="javascript:void(0);"  onclick="showMemberList(<?php echo $post->getIdentity(); ?>);" class="siteforum_thanks_icon"  title="<?php echo $this->translate('Thank(s)'); ?>"><?php echo $no_of_thanks; ?></a>
                                <?php
                            }
                            ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($signature): ?>
                        <span class="siteforum_post_counts" title="<?php echo $this->translate(array('%1$s %2$s Post', '%1$s %2$s Posts', $signature->post_count), $this->locale()->toNumber($signature->post_count), '')." + ".$this->translate(array('%1$s %2$s Comment', '%1$s %2$s Comments', $post->commentCount()), $this->locale()->toNumber($post->commentCount()), '')." by $user_name on forum"; ?>"><?php echo ($signature->post_count+$post->commentCount()); ?></span>
                    <?php endif; ?>

                    <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.reputation', 1)): ?>
                        <?php
                        $reputation = Engine_Api::_()->getDbTable('reputations', 'siteforum')->reputationCount($user->getIdentity());
                        if (!empty($reputation[0]) || !empty($reputation[1])) {
                            ?>
                            <span>
                                <a href="javascript:void(0);" onclick="showRepList(<?php echo $post->getIdentity(); ?>);" class="siteforum_reputation_icon" title="<?php echo $this->translate('Reputation'); ?>"><?php
                                    echo $reputation[0];
                                    if ($reputation[1] != 0)
                                        echo '&sbquo;&nbsp;-' . $reputation[1];
                                    else
                                        echo '&sbquo;&nbsp;' . $reputation[1];
                                }
                                ?>
                            </a>
                        </span> 
                    <?php endif; ?>
                </div>
            </div>

            <div class="siteforum_topic_posts_info">
                <div class="siteforum_topic_posts_info_body">

                    <?php
                    $params['user_id'] = $post->user_id;
                    $body = $post->body;
                    $doNl2br = false;
                    if (strip_tags($body) == $body) {
                        $body = nl2br($body);
                    }
                    if (!$this->decode_html && $this->decode_bbcode) {
                        $body = $this->BBCode($body, array('link_no_preparse' => true));
                    }
                    if (!empty($signature->body))
                        echo $body . '<div class="siteforum_topic_signature">' . $signature->body . '</div>';
                    else
                        echo $body;
                    ?>
                    <?php if ($post->edit_id && !empty($post->modified_date)): ?>
                        <i class="siteforum_edit_meassage">
                            <?php echo $this->translate('This post was edited by %1$s at %2$s', $this->user($post->edit_id)->__toString(), $this->locale()->toDateTime(strtotime($post->modified_date))); ?>
                        </i>
                    <?php endif; ?>
                </div>



                <?php if ($post->file_id): ?>
                    <div class="siteforum_topic_posts_info_photo">
                        <?php echo $this->itemPhoto($post, null, '', array('class' => 'siteforum_post_photo')); ?>
                    </div>
                <?php endif; ?>

            </div>

            <div class="siteforum_topic_posts_info_bottom seaocore_txt_light">
                <a class="siteforum_icon_comment fleft mright5" href="<?php echo $this->topic->getHref()."#siteforum_post_".$post->getIdentity() ?>">
                    &nbsp;
                  </a>
                <span class="siteforum_time_icon fleft">
                    <?php echo $this->locale()->toDateTime(strtotime($post->creation_date)) ?>
                </span>
                <div id="siteforum_topic_posts_info_top_options_<?php echo $post->getIdentity(); ?>" class="siteforum_topic_posts_info_bottom_options fleft">
                    <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.thanks', 1) && !Engine_Api::_()->getDbTable('thanks', 'siteforum')->checkThanked($post->getIdentity(), $post->user_id, $this->viewer_id) && !($post->user_id == $this->viewer_id)) { ?>
                        <?php if ($this->viewer->getIdentity()): ?>
                            <div id="thank_link_<?php echo $post->getIdentity(); ?>" class="fleft"> 
                                <a href="javascript:void(0);" class="siteforum_thanks_icon"   onclick="thank(<?php echo $post->user_id; ?>,<?php echo $post->getIdentity(); ?>);" ><?php echo $this->translate('Thanks');?></a>
                            </div>
                        <?php endif; ?>
                    <?php } ?>
                    <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.reputation', 1) && !Engine_Api::_()->getDbTable('reputations', 'siteforum')->checkReputation($post->user_id, $this->viewer_id, $post->getIdentity()) && !($post->user_id == $this->viewer_id)) { ?>
                        <?php if ($this->viewer->getIdentity()): ?>
                            <a href="<?php echo $this->url(array('post_id' => $post->getIdentity(), 'user_id' => $post->user_id, 'controller' => 'post', 'action' => 'reputation','page' => $this->page_param), 'siteforum_reputation'); ?>" class="smoothbox siteforum_reputation_icon"><?php echo $this->translate('Add Reputation'); ?></a>            
                        <?php endif; ?>
                    <?php } ?> 
                    <?php if ($this->canPost): ?>
                        <?php echo $this->htmlLink(array('route' => 'siteforum_topic', 'action' => 'post-create', 'topic_id' => $this->subject()->getIdentity(), 'quote_id' => $post->getIdentity(),), $this->translate('Quote'), array('class' => 'siteforum_icon_quote',)) ?>
                    <?php endif; ?>

                    <?php if ($this->canPost): ?>     
                        <a class="siteforum_icon_comment" href="javascript:void(0);" onclick="if ($('comment-form-' + <?php echo $post->getIdentity(); ?>).style.display == 'none')
                                            $('comment-form-' + <?php echo $post->getIdentity(); ?>).style.display = 'inline-block';
                                        else
                                            $('comment-form-' + <?php echo $post->getIdentity(); ?>).style.display = 'none';
                                        $('comment-form-' + <?php echo $post->getIdentity(); ?>).body.focus();" ><?php echo $this->translate('Comment'); ?></a>  
                       <?php endif; ?>
                       <?php if ($this->viewer()->getIdentity()): ?>
                        <span id="siteforum_post_like_unlike_<?php echo $post->getIdentity() ?>">
                            <?php if ($post->likes()->isLike($this->viewer())): ?>
                                <a href="javascript:void(0);" class="siteforum_icon_unlike" onclick="en4.siteforum.comments.unlike('<?php echo $post->getIdentity() ?>')"><?php echo $this->translate('Unlike') ?></a>
                            <?php else: ?>
                                <a href="javascript:void(0);" class="siteforum_icon_like" onclick="en4.siteforum.comments.like('<?php echo $post->getIdentity() ?>')"><?php echo $this->translate('Like') ?></a>
                            <?php endif; ?>
                        </span>   
                    <?php endif; ?>

                    <?php if ($this->canEdit): ?>
                        <a href="<?php echo $this->url(array('post_id' => $post->getIdentity(), 'action' => 'edit','page' => $this->page_param), 'siteforum_post'); ?>" class="siteforum_icon_edit"><?php echo $this->translate('Edit'); ?></a>
                        <a href="<?php echo $this->url(array('post_id' => $post->getIdentity(), 'action' => 'delete'), 'siteforum_post'); ?>" class="smoothbox siteforum_icon_delete"><?php echo $this->translate('Delete'); ?></a>
                    <?php elseif ($post->user_id != 0 && $post->isOwner($this->viewer) && !$this->topic->closed): ?>
                        <?php if ($this->canEdit_Post): ?>
                            <a href="<?php echo $this->url(array('post_id' => $post->getIdentity(), 'action' => 'edit'), 'siteforum_post'); ?>" class="siteforum_icon_edit"><?php echo $this->translate('Edit'); ?></a>
                        <?php endif; ?>
                        <?php if ($this->canDelete_Post): ?>
                            <a href="<?php echo $this->url(array('post_id' => $post->getIdentity(), 'action' => 'delete'), 'siteforum_post'); ?>" class="smoothbox siteforum_icon_delete"><?php echo $this->translate('Delete'); ?></a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($this->viewer()->getIdentity() && $post->user_id != $this->viewer()->getIdentity()): ?>
                        <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'core', 'controller' => 'report', 'action' => 'create', 'subject' => $post->getGuid(), 'format' => 'smoothbox',), $this->translate('Report'), array('class' => 'siteforum_icon_report smoothbox',)) ?>
                    <?php endif; ?>
                </div>
                <!--Like Counts-->
                <div class="fright" id='post_likes_<?php echo $post->getIdentity(); ?>'><a class="smoothbox" href='<?php echo $this->url(array('module' => 'seaocore', 'controller' => 'like', 'action' => 'likelist', 'resource_type' => $post->getType(), 'resource_id' => $post->getIdentity(), 'call_status' => 'public'), 'default', true); ?>'><span class="siteforum_icon_like" title="<?php echo $this->translate("Post likes"); ?>"><?php echo $post->like_count; ?></span></a></div>


                <?php Engine_Api::_()->core()->clearSubject(); ?>
                <?php echo $this->action("list", "comment", "siteforum", array("type" => $post->getType(), "id" => $post->getIdentity())); ?>
                <?php Engine_Api::_()->core()->clearSubject(); ?>
                <?php Engine_Api::_()->core()->setSubject($this->topic); ?>  

            </div>
        </li>
    <?php endforeach; ?>
</ul>

<div class="siteforum_topic_pages">
    <?php echo $this->paginationControl($this->paginator, null, null, array('params' => array('post_id' => null,),)); ?>
</div>
<?php if ($this->canPost && $this->form): ?>
    <?php echo $this->form->render(); ?>
<?php endif; ?>

<script type="text/javascript">
    en4.core.runonce.add(function () {
        showForumShareLinks();
    });
</script>

<script type="text/javascript">
    $$('.core_main_siteforum').getParent().addClass('active');
    if ($('siteforum_quick_reply')) {
<?php
echo $this->tinyMCESEAO()->render(array('element_id' => '"siteforum_quick_reply"',
    'language' => $this->language,
    'upload_url' => $this->upload_url,
    'directionality' => $this->directionality));
?>
    }

    $$(document.getElementsByTagName('blockquote')).each(function (element, key) {
        element.removeClass('siteforum_icon_quote').addClass('siteforum_icon_quote');
    });

</script>


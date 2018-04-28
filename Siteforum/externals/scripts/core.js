
/* $Id: core.js 2015-12-23 00:00:00Z SocialEngineAddOns Copyright 2015-2016 BigStep Technologies Pvt.Ltd. $ */


(function () { // START NAMESPACE
    var $ = 'id' in document ? document.id : window.$;

    /**
     * Core methods
     */
    en4.siteforum = {
    };

    en4.siteforum.topics = {
        like: function (id) {
            (new Request.JSON({
                'format': 'json',
                'url': en4.core.baseUrl + 'siteforum/comment/like',
                'data': {
                    'format': 'json',
                    type: 'forum_topic',
                    id: id
                },
                'onRequest': function () {
                    timer = (function () {
                        $('siteforum_topic_like_unlike_' + id).innerHTML = '<a href="javascript:void(0);"><img src="application/modules/Seaocore/externals/images/core/loading.gif" /></a>';
                    }).delay(5);
                },
                'onSuccess': function (responseJSON, responseText)
                {
                    $('topic_likes').innerHTML = '<a class="smoothbox" href="' + en4.core.baseUrl + 'seaocore/like/likelist/call_status/public/resource_type/forum_topic/resource_id/' + id + '"><span class="siteforum_icon_like" title="Topic likes">' + responseJSON.like_count + '</span></a>';
                    Smoothbox.bind($('topic_likes'));
                    $('siteforum_topic_like_unlike_' + id).innerHTML = '<a href="javascript:void(0);" class="siteforum_icon_unlike" onclick="en4.siteforum.topics.unlike(' + id + ')">' + en4.core.language.translate('Unlike') + '</a>';
                }
            })).send();
        },
        unlike: function (id) {
            (new Request.JSON({
                'format': 'json',
                'url': en4.core.baseUrl + 'siteforum/comment/unlike',
                'data': {
                    'format': 'json',
                    type: 'forum_topic',
                    id: id
                },
                'onRequest': function () {
                    timer = (function () {
                        $('siteforum_topic_like_unlike_' + id).innerHTML = '<a href="javascript:void(0);"><img src="application/modules/Seaocore/externals/images/core/loading.gif" /></a>';
                    }).delay(5);
                },
                'onSuccess': function (responseJSON, responseText)
                {


                    $('topic_likes').innerHTML = '<a class="smoothbox" href="' + en4.core.baseUrl + 'seaocore/like/likelist/call_status/public/resource_type/forum_topic/resource_id/' + id + '"><span class="siteforum_icon_like" title="Topic likes">' + responseJSON.like_count + '</span></a>';
                    Smoothbox.bind($('topic_likes'));

                    $('siteforum_topic_like_unlike_' + id).innerHTML = '<a href="javascript:void(0);" class="siteforum_icon_like" onclick="en4.siteforum.topics.like(' + id + ')">' + en4.core.language.translate('Like') + '</a>';
                }
            })).send();
        }
    }
    /**
     * Comments
     */
    en4.siteforum.comments = {
        loadComments: function (type, id, page) {
            en4.core.request.send(new Request.HTML({
                url: en4.core.baseUrl + 'siteforum/comment/list',
                data: {
                    format: 'html',
                    type: type,
                    id: id,
                    page: page
                }
            }), {
                'element': $('comments-' + id)
            });
        },
        attachCreateComment: function (formElement) {
            var bind = this;
            formElement.addEvent('submit', function (event) {
                event.stop();
                var form_values = formElement.toQueryString();

                if (formElement.body.value == '') {
                    return;
                }

                form_values += '&format=json';
                form_values += '&id=' + formElement.identity.value;
                en4.core.request.send(new Request.JSON({
                    url: en4.core.baseUrl + 'siteforum/comment/create',
                    data: form_values,
                    onComplete: function (responseJSON) {
                        $('comment-form-' + formElement.identity.value).style.display = "block";

                    }
                }), {
                    'element': $('comments-' + formElement.identity.value)
                });
            });
        },
        comment: function (type, id, body) {
            en4.core.request.send(new Request.JSON({
                url: en4.core.baseUrl + 'siteforum/comment/create',
                data: {
                    format: 'json',
                    type: type,
                    id: id,
                    body: body
                }
            }), {
                'element': $('comments-' + id)
            });
        },
        attachEditComment: function (formElement) {

            var form_values = formElement.toQueryString();

            form_values += '&format=json';
            en4.core.request.send(new Request.JSON({
                url: en4.core.baseUrl + 'siteforum/comment/edit',
                data: form_values
            }), {
                'element': $('comments-' + formElement.id.value)
            });

        },
        like: function (id) {
            (new Request.JSON({
                'format': 'json',
                'url': en4.core.baseUrl + 'siteforum/comment/like',
                'data': {
                    'format': 'json',
                    type: 'forum_post',
                    id: id
                },
                'onRequest': function () {
                    timer = (function () {
                        $('siteforum_post_like_unlike_' + id).innerHTML = '<a href="javascript:void(0);"><img src="application/modules/Seaocore/externals/images/core/loading.gif" /></a>';
                    }).delay(5);
                },
                'onSuccess': function (responseJSON, responseText)
                {
                    $('post_likes_' + id).innerHTML = '<a class="smoothbox" href="' + en4.core.baseUrl + 'seaocore/like/likelist/call_status/public/resource_type/forum_post/resource_id/' + id + '"><span class="siteforum_icon_like">' + responseJSON.like_count + '</span></a>';
                    Smoothbox.bind($('post_likes_' + id));
                    $('siteforum_post_like_unlike_' + id).innerHTML = '<a href="javascript:void(0);" class="siteforum_icon_unlike" onclick="en4.siteforum.comments.unlike(' + id + ')">' + en4.core.language.translate('Unlike') + '</a>';
                }
            })).send();
        },
        unlike: function (id) {
            (new Request.JSON({
                'format': 'json',
                'url': en4.core.baseUrl + 'siteforum/comment/unlike',
                'data': {
                    'format': 'json',
                    type: 'forum_post',
                    id: id
                },
                'onRequest': function () {
                    timer = (function () {
                        $('siteforum_post_like_unlike_' + id).innerHTML = '<a href="javascript:void(0);"><img src="application/modules/Seaocore/externals/images/core/loading.gif" /></a>';
                    }).delay(5);
                },
                'onSuccess': function (responseJSON, responseText)
                {
                    $('post_likes_' + id).innerHTML = '<a class="smoothbox" href="' + en4.core.baseUrl + 'seaocore/like/likelist/call_status/public/resource_type/forum_post/resource_id/' + id + '"><span class="siteforum_icon_like">' + responseJSON.like_count + '</span></a>';
                    Smoothbox.bind($('post_likes_' + id))
                    $('siteforum_post_like_unlike_' + id).innerHTML = '<a href="javascript:void(0);" class="siteforum_icon_like" onclick="en4.siteforum.comments.like(' + id + ')">' + en4.core.language.translate('Like') + '</a>';
                }
            })).send();
        },
        showLikes: function (type, id) {
            en4.core.request.send(new Request.HTML({
                url: en4.core.baseUrl + 'siteforum/comment/list',
                data: {
                    format: 'html',
                    type: type,
                    id: id,
                    viewAllLikes: true
                }
            }), {
                'element': $('comments-' + id)
            });
        },
        deleteComment: function (type, id, comment_id) {
            if (!confirm(en4.core.language.translate('Are you sure you want to delete this?'))) {
                return;
            }
            if ($('comment-' + comment_id)) {
                $('comment-' + comment_id).destroy();
            }
            (new Request.JSON({
                url: en4.core.baseUrl + 'siteforum/comment/delete',
                data: {
                    format: 'json',
                    type: type,
                    id: id,
                    comment_id: comment_id
                },
                onComplete: function () {
                    if ($('comment-' + comment_id)) {
                        $('comment-' + comment_id).destroy();
                    }
                    try {
                        var commentCount = $$('.comments_options span')[0];
                        var m = commentCount.get('html').match(/\d+/);
                        var newCount = (parseInt(m[0]) != 'NaN' && parseInt(m[0]) > 1 ? parseInt(m[0]) - 1 : 0);
                        commentCount.set('html', commentCount.get('html').replace(m[0], newCount));
                    } catch (e) {
                    }
                }
            })).send();
        }
    };

})(); // END NAMESPACE
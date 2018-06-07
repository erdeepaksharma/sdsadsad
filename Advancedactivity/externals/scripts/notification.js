/* $Id: notification.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep Technologies
 Pvt. Ltd. $
 */

var showNotifications;
window.addEvent('domready', function () {

    showNotifications = function () {
        en4.activity.updateNotifications();
        new Request.HTML({
            'url': en4.core.baseUrl + 'advancedactivity/notifications/pulldown',
            'data': {
                'format': 'html',
                'page': 1
            },
            'onComplete': function (responseTree, responseElements, responseHTML, responseJavaScript) {
                if (responseHTML) {
                    // hide loading icon
                    if ($('notifications_loading'))
                        $('notifications_loading').setStyle('display', 'none');

                    $('notifications_menu').innerHTML = responseHTML;
                    $('notifications_menu').addEvent('click', function (event) {
                        event.stop(); //Prevents the browser from following the link.          
                        var current_link = event.target;
                        var notification_li = $(current_link).getParent('li');
                        // if this is true, then the user clicked on the li element itself
                        if (notification_li.id == 'core_menu_mini_menu_update') {
                            notification_li = current_link;
                        }

                        var forward_link;
                        if (current_link.get('href')) {
                            forward_link = current_link.get('href');
                        } else {
                            forward_link = $(current_link).getElements('a:last-child').get('href');
                            if (forward_link == '' || $(current_link).get('tag') == 'img') {
                                var a_el = $(current_link).getParent('a');
                                if (a_el)
                                    forward_link = $(current_link).getParent('a').get('href');
                            }
                            if (forward_link == '' || $(current_link).get('tag') == 'span') {
                                forward_link = $(notification_li).getElements('a:last-child').get('href');
                            }
                        }
                        var notifications_unread = false;
                        if (notification_li.get('class')) {
                            notification_li.get('class').split(' ').each(function (className) {
                                className = className.trim();
                                if (className == 'notifications_unread')
                                    notifications_unread = true;
                            });
                        }
                        if (notifications_unread) {
                            notification_li.removeClass('notifications_unread');
                            en4.core.request.send(new Request.JSON({
                                url: en4.core.baseUrl + 'activity/notifications/markread',
                                data: {
                                    format: 'json',
                                    'actionid': notification_li.get('value')
                                },
                                onSuccess: function () {
                                    window.location = forward_link;
                                }
                            }));
                        } else {
                            window.location = forward_link;
                        }
                    });
                } else {
                    $('notifications_loading').innerHTML = en4.core.language.translate("You have no new updates.");
                }
            }
        }).send();
    };

});


window.addEvent('domready', function () {
    if ($('notifications_menu')) {
        $('notifications_menu').addEvent('click', function (event) {
            event.stop(); //Prevents the browser from following the link.
            notificationClick(event);
        });
    }
});

/**
 * When viewer click on any notification
 * @param {object} event
 */
function notificationClick(event)
{
    var current_link = event.target;
    var notification_li = $(current_link).getParent('li');

    var forward_link;
    if (current_link.get('href')) {
        forward_link = current_link.get('href');
    } else {
        forward_link = $(current_link).getElements('a:last-child').get('href');
        if (forward_link == '' || $(current_link).get('tag') == 'img') {
            var a_el = $(current_link).getParent('a');
            if (a_el)
                forward_link = $(current_link).getParent('a').get('href');
        }
        if (forward_link == '' || $(current_link).get('tag') == 'span') {
            forward_link = $(notification_li).getElements('a:last-child').get('href');
        }
    }
    if (forward_link) {
        en4.core.request.send(new Request.JSON({
            url: en4.core.baseUrl + 'activity/notifications/markread',
            data: {
                format: 'json',
                'actionid': notification_li.get('value')
            },
            onSuccess: function () {
                window.location = forward_link;
            }
        }));
    }
}
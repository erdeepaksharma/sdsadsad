<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: add-moderator.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<?php echo $this->form->render($this) ?>

<div class="siteforum_admin_manage_users">
    <ul id="user_list"></ul>
</div>
<script type="text/javascript">

    window.addEvent('domready', function () {
        $('siteforum_form_admin_moderator_create').addEvent('submit', function (event) {
            event.stop();
            updateUsers();
        });
    });

    function addModerator(user_id) {
        $('user_id').set('value', user_id);
        $('siteforum_form_admin_moderator_create').submit();
    }

    function updateUsers() {
        var request = new Request/*.HTML*/({
            url: '<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'manage', 'action' => 'user-search'), 'admin_default', true); ?>',
            method: 'GET',
            data: {
                format: 'html',
                page: '1',
                forum_id: <?php echo $this->siteforum->getIdentity(); ?>,
                username: $('username').value
            },
            'onSuccess': function (/*responseTree, responseElements,*/ responseHTML/*, responseJavaScript*/) {
                if (responseHTML.length > 0) {
                    $('user_list').setStyle('display', 'block');
                } else {
                    $('user_list').setStyle('display', 'none');
                }
                $('user_list').set('html', responseHTML);
                parent.Smoothbox.instance.doAutoResize();
                return false;
            }
        });
        request.send();
    }
</script>
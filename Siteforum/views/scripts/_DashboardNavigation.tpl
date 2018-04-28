<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _DashboardNavigation.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$siteforum_dashboard_content = Engine_Api::_()->getApi('menus', 'core')->getNavigation('siteforum_dashboard_content');
?>

<?php
$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Siteforum/externals/styles/style_siteforum_dashboard.css');
?>

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Siteforum/externals/styles/style_siteforum.css'); ?>

<div class="layout_middle <?php if (Engine_Api::_()->hasModuleBootstrap('spectacular')): ?> spectacular_dashboard <?php endif; ?>">
    <div class='seaocore_db_tabs'>

        <?php if (count($siteforum_dashboard_content)): ?>
            <ul>
                <li class="seaocore_db_head"><h3><?php echo $this->translate("Dashboard"); ?></h3></li>
                        <?php
                        foreach ($siteforum_dashboard_content as $item):
                            $attribs = array_diff_key(array_filter($item->toArray()), array_flip(array(
                                'reset_params', 'route', 'module', 'controller', 'action', 'type',
                                'visible', 'label', 'href')));
                            if (!isset($attribs['active'])) {
                                $attribs['active'] = false;
                            }
                            ?>
                <?php $viewer = Engine_Api::_()->user()->getViewer();?>
                <?php if(Engine_Api::_()->siteforum()->isModerator($viewer) || $item->getLabel()!='Sticky Topics'):?>
                    <li<?php echo($attribs['active'] ? ' class="selected"' : ''); ?>>
                        <?php echo $this->htmlLink($item->getHref(), $this->translate($item->getLabel()), $attribs); ?>
                    </li>
                <?php endif;?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php echo $this->htmlLink($this->viewer->getHref(), $this->itemPhoto($this->viewer)) ?>
        <?php //echo $this->htmlLink($this->viewer->getHref(), $this->viewer->getTitle()) ?>

    </div>  

    <script type="text/javascript">
        var globalContentElement = en4.seaocore.getDomElements('content');
        en4.core.runonce.add(function () {
            var element = $(event.target);
            if (element.tagName.toLowerCase() == 'a') {
                element = element.getParent('li');
            }
        });

        if ($$('.ajax_dashboard_enabled')) {
            en4.core.runonce.add(function () {
                $$('.ajax_dashboard_enabled').addEvent('click', function (event) {
                    var element = $(event.target);
                    event.stop();
                    var ulel = this.getParent('ul');
                    $(globalContentElement).getElement('.siteforum_dashboard_content').innerHTML = '<div class="seaocore_content_loader"></div>';
                    ulel.getElements('li').removeClass('selected');

                    if (element.tagName.toLowerCase() == 'a') {
                        element = element.getParent('li');
                    }

                    element.addClass('selected');
                    showAjaxBasedContent(this.href);
                });
            });
        }

        function showAjaxBasedContent(url) {

            if (history.pushState) {
                history.pushState({}, document.title, url);
            } else {
                window.location.hash = url;
            }

            en4.core.request.send(new Request.HTML({
                url: url,
                method: 'get',
                data: {
                    format: 'html',
                    'isajax': 1
                }, onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
                    $(globalContentElement).innerHTML = responseHTML;
                    Smoothbox.bind($(globalContentElement));
                    en4.core.runonce.trigger();
                    if (window.InitiateAction) {
                        InitiateAction();
                    }
                }
            }));
        }

        var requestActive = false;
        window.addEvent('load', function () {
            InitiateAction();
        });

        var InitiateAction = function () {
            formElement = $$('.global_form')[0];
            if (typeof formElement != 'undefined') {
                formElement.addEvent('submit', function (event) {
                    if (typeof submitformajax != 'undefined' && submitformajax == 1) {
                        submitformajax = 0;
                        event.stop();
                        Savevalues();
                    }
                })
            }
        }

        var Savevalues = function () {
            if (requestActive)
                return;

            requestActive = true;
            var pageurl = $(globalContentElement).getElement('.global_form').action;

            currentValues = formElement.toQueryString();
            $('show_tab_content_child').innerHTML = '<div class="seaocore_content_loader"></div>';
            if (typeof page_url != 'undefined') {
                var param = (currentValues ? currentValues + '&' : '') + 'isajax=1&format=html&page_url=' + page_url;
            }
            else {
                var param = (currentValues ? currentValues + '&' : '') + 'isajax=1&format=html';
            }

            var request = new Request.HTML({
                url: pageurl,
                onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
                    $(globalContentElement).innerHTML = responseHTML;
                    InitiateAction();
                    requestActive = false;
                }
            });
            request.send(param);
        }

        function owner(thisobj) {
            var Obj_Url = thisobj.href;
            Smoothbox.open(Obj_Url);
        }
    </script>

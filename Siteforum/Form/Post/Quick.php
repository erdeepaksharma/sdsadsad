<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Quick.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Post_Quick extends Engine_Form {

    public function init() {
        $this
                ->setTitle('Quick Reply')
                ->setAttrib('name', 'siteforum_post_quick')
        ;

        $viewer = Engine_Api::_()->user()->getViewer();
        $settings = Engine_Api::_()->getApi('settings', 'core');

        $allowHtml = (bool) $settings->getSetting('siteforum.html', 1);
        $allowBbcode = (bool) $settings->getSetting('siteforum.bbcode', 0);

        $filter = new Engine_Filter_Html();
        $allowed_tags = explode(',', Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'forum', 'commentHtml'));

        if ($settings->getSetting('siteforum.html', 1) == '0') {
            $filter->setForbiddenTags();
            $filter->setAllowedTags($allowed_tags);
        }

        // Element: body
        if ($allowHtml || $allowBbcode) {

            $upload_url = "";

            if (Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create')) {
                $upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'upload-photo'), 'siteforum_general', true);
            }

            $options = array(
                'bbcode' => $settings->getSetting('siteforum.bbcode', 0),
                'html' => $settings->getSetting('siteforum.html', 1)
            );

            $editorOptions = array_merge($options, Engine_Api::_()->seaocore()->tinymceEditorOptions($upload_url));
            $this->addElement('Textarea', 'body', array(
                'id' => 'siteforum_quick_reply',
                'disableLoadDefaultDecorators' => true,
                'required' => true,
                'attribs' => array('rows' => 24, 'cols' => 80, 'style' => 'height:300px;'),
                'editorOptions' => $editorOptions,
                'allowEmpty' => false,
                'decorators' => array('ViewHelper'),
                'filters' => array(
                    $filter,
                    new Engine_Filter_Censor(),
                )
            ));
        } else {
            $this->addElement('textarea', 'body', array(
                'label' => 'Quick Reply',
                'required' => true,
                'allowEmpty' => false,
                'filters' => array(
                    'StripTags',
                    $filter,
                    new Engine_Filter_Censor(),
                ),
            ));
        }

        // Element: photo
        // Need this hack for some reason
        $this->addElement('File', 'photo', array(
            'attribs' => array('style' => 'display:none;')
        ));

        // Element: watch
        $this->addElement('Checkbox', 'watch', array(
            'label' => 'Send me notifications when other members reply to this topic.',
            'value' => '0',
        ));

        // Element: submit
        $this->addElement('Button', 'submit', array(
            'label' => 'Post Reply',
            'type' => 'submit',
        ));
    }

}

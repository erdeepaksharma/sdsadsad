<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Edit.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Post_Edit extends Engine_Form {

    public $_error = array();
    protected $_post;

    public function setPost($post) {
        $this->_post = $post;
    }

    public function init() {
        $this
                ->setTitle("Edit Post")
                ->setMethod("POST")
                ->setAttrib('name', 'siteforum_post_edit')
                ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

        $viewer = Engine_Api::_()->user()->getViewer();
        $settings = Engine_Api::_()->getApi('settings', 'core');

        $allowHtml = (bool) $settings->getSetting('siteforum.html', 1);
        $allowBbcode = (bool) $settings->getSetting('siteforum.bbcode', 0);

        if (!$allowHtml) {
            $filter = new Engine_Filter_HtmlSpecialChars();
        } else {
            $filter = new Engine_Filter_Html();
            $filter->setForbiddenTags();
            $allowed_tags = array_map('trim', explode(',', Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'forum', 'commentHtml')));
            $filter->setAllowedTags($allowed_tags);
        }

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
            $editorOptions['height'] = '400px';
            $this->addElement('TinyMce', 'body', array(
                'disableLoadDefaultDecorators' => true,
                'required' => true,
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
                'required' => true,
                'allowEmpty' => false,
                'attribs' => array(
                    'rows' => 24,
                    'cols' => 80,
                    'style' => 'width:553px; max-width:553px; height:158px;'
                ),
                'filters' => array(
                    'StripTags',
                    $filter,
                    new Engine_Filter_Censor(),
                ),
            ));
        }

        if (!empty($this->_post->file_id)) {
            $photo_delete_element = new Engine_Form_Element_Checkbox('photo_delete', array('label' => 'This post has a photo attached. Do you want to delete it?'));
            $photo_delete_element->setAttrib('onchange', 'updateUploader()');
            $this->addElement($photo_delete_element);
            $this->addDisplayGroup(array('photo_delete'), 'photo_delete_group');
        }

        // Buttons
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array('ViewHelper')
        ));

        $this->addElement('Cancel', 'cancel', array(
            'label' => 'cancel',
            'link' => true,
            'prependText' => ' or ',
            'decorators' => array(
                'ViewHelper'
            )
        ));
        $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
        $button_group = $this->getDisplayGroup('buttons');
        $button_group->addDecorator('DivDivDivWrapper');
    }

}

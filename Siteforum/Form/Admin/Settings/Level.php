<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Level.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract {

  public function init() {
    parent::init();

    // My stuff
    $this
      ->setTitle('Member Level Settings')
      ->setDescription("These settings are applied on a per member level basis. Start by selecting the member level you want to modify, then adjust the settings for that level below. Note that settings on this page override the abilities granted to moderators. If you select \'No\' to an option, even moderators will not be able to perform that action. Conversely, if you select the \'Yes, even private ones\'/\'Yes, including other members' options, they will be able to perform that action even if they are not a moderator.");

    // Element: view
    $this->addElement('Radio', 'view', array(
      'label' => 'Allow Viewing of Forums?',
      'description' => 'Do you want to let users view forums? If set to no, some other settings on this page may not apply.',
      'value' => 1,
      'multiOptions' => array(
        2 => 'Yes, allow viewing and subscription of forums, even private ones.',
        1 => 'Yes, allow viewing and subscription of forums.',
        0 => 'No, do not allow forums to be viewed or subscribed to.',
      ),
      'value' => ( $this->isModerator() ? 2 : 1 ),
    ));
    if (!$this->isModerator()) {
      unset($this->view->options[2]);
    }

    if (!$this->isPublic()) {

      // Element: topic_create
      $this->addElement('Radio', 'topic_create', array(
        'label' => 'Allow Creation of Topics?',
        'description' => 'Do you want to allow users to create topics in forums?',
        'multiOptions' => array(
          2 => 'Yes, allow creation of topics in forums, even private ones.',
          1 => 'Yes, allow creation of topics.',
          0 => 'No, do not allow topics to be created.'
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if (!$this->isModerator()) {
        unset($this->topic_create->options[2]);
      }
      
      $request = Zend_Controller_Front::getInstance()->getRequest();
      if (null !== ($id = $request->getParam('id'))) {
        $level = Engine_Api::_()->getItem('authorization_level', $id);
      } else {
        $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
      }

      if (!$level instanceof Authorization_Model_Level) {
        throw new Engine_Exception('missing level');
      }

      $id = $level->level_id;
      
      if ($id !== 4) {
        // Element: topic_edit
        $this->addElement('Radio', 'topic_edit', array(
          'label' => 'Allow Editing of Topics?',
          'description' => 'Do you want to allow users to edit topics in forums?',
          'multiOptions' => array(
            2 => 'Yes, allow editing of topics in forums, including other members\' topics.',
            1 => 'Yes, allow editing of topics.',
            0 => 'No, do not allow topics to be edited.'
          ),
          'value' => ( $this->isModerator() ? 2 : 1 ),
        ));
        if (!$this->isModerator()) {
          unset($this->topic_edit->options[2]);
        }

        // Element: topic_edit
        $this->addElement('Radio', 'topic_delete', array(
          'label' => 'Allow Deletion of Topics?',
          'description' => 'Do you want to allow users to delete topics in forums?',
          'multiOptions' => array(
            2 => 'Yes, allow deletion of topics in forums, including other members\' topics.',
            1 => 'Yes, allow deletion of topics.',
            0 => 'No, do not allow topics to be deleted.'
          ),
          'value' => ( $this->isModerator() ? 2 : 1 ),
        ));
        if (!$this->isModerator()) {
          unset($this->topic_delete->options[2]);
        }
      }
      // Element: post_create
      $this->addElement('Radio', 'post_create', array(
        'label' => 'Allow Posting?',
        'description' => 'Do you want to allow users to post to the forums? If set to no, some other settings on this page may not apply. This is useful if you want users to be able to view forums and manage their subscriptions, but only want certain levels to be able to create forums.',
        'multiOptions' => array(
          2 => 'Yes, allow posting to forums, even private ones.',
          1 => 'Yes, allow posting to forums.',
          0 => 'No, do not allow forum posts.'
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if (!$this->isModerator()) {
        unset($this->post_create->options[2]);
      }

      // Element: post_edit
      $this->addElement('Radio', 'post_edit', array(
        'label' => 'Allow Editing of Posts?',
        'description' => 'Do you want to allow users to edit posts in forums?',
        'multiOptions' => array(
          2 => 'Yes, allow editing of posts, including other members\' posts.',
          1 => 'Yes, allow editing of posts.',
          0 => 'No, do not allow forum posts to be edited.'
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if (!$this->isModerator()) {
        unset($this->post_edit->options[2]);
      }

      // Element: post_edit
      $this->addElement('Radio', 'post_delete', array(
        'label' => 'Allow Deletion of Posts?',
        'description' => 'Do you want to allow users to delete posts in forums?',
        'multiOptions' => array(
          2 => 'Yes, allow deletion of posts, including other members\' posts.',
          1 => 'Yes, allow deletion of posts.',
          0 => 'No, do not allow forum posts to be deleted.'
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if (!$this->isModerator()) {
        unset($this->post_delete->options[2]);
      }

      // Element: post_edit

      if (!$this->isModerator()) {
        unset($this->post_delete->options[2]);
      }

      // Element: commentHtml
      $this->addElement('Text', 'commentHtml', array(
        'label' => 'Allow HTML in posts?',
        'description' => 'If you have disabled HTML in posts from Global Settings of this plugin but want to allow specific tags, you can enter them below (separated by commas). If you have enabled HTML in posts, this setting will have no effect. Example: b, img, a, embed, font ',
      ));
    }
  }

}

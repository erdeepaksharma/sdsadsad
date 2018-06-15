<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Video.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Video_Video extends Engine_Form {

  public function init() {

    $this
            ->setTitle('Add New Video')
            ->setAttrib('id', 'form-upload')
            ->setAttrib('name', 'video_create')
            ->setAttrib('enctype', 'multipart/form-data')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $user = Engine_Api::_()->user()->getViewer();

    $this->addElement('Text', 'title', array(
        'label' => 'Video Title',
        'maxlength' => '100',
        'allowEmpty' => false,
        'required' => true,
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
            new Engine_Filter_StringLength(array('max' => '100')),
        )
    ));

    $this->addElement('Text', 'tags', array(
        'label' => 'Tags (Keywords)',
        'autocomplete' => 'off',
        'description' => 'Separate tags with commas.',
                   'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
    ));
    $this->tags->getDecorator("Description")->setOption("placement", "append");

    $this->addElement('Textarea', 'description', array(
        'label' => 'Video Description',
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
            new Engine_Filter_EnableLinks(),
        ),
    ));

    $categories = Engine_Api::_()->video()->getCategories();

    if (count($categories) != 0) {
      $categories_prepared[0] = "";
      foreach ($categories as $category) {
        $categories_prepared[$category->category_id] = $category->category_name;
      }

      $this->addElement('Select', 'category_id', array(
          'label' => 'Category',
          'multiOptions' => $categories_prepared
      ));
    }

    $this->addElement('Checkbox', 'search', array(
        'label' => "Show this video in search results",
        'value' => 1,
    ));

    $availableLabels = array(
        'everyone' => 'Everyone',
        'registered' => 'All Registered Members',
        'owner_network' => 'Friends and Networks',
        'owner_member_member' => 'Friends of Friends',
        'owner_member' => 'Friends Only',
        'owner' => 'Just Me'
    );

    $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'auth_view');
    $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));

    if (!empty($viewOptions) && count($viewOptions) >= 1) {
      $this->addElement('Select', 'auth_view', array(
          'label' => 'Privacy',
          'description' => 'Who may see this video?',
          'multiOptions' => $viewOptions,
          'value' => key($viewOptions),
      ));
      $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
    }

    $commentOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'auth_comment');
    $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));

    if (!empty($commentOptions) && count($commentOptions) >= 1) {
      $this->addElement('Select', 'auth_comment', array(
          'label' => 'Comment Privacy',
          'description' => 'Who may post comments on this video?',
          'multiOptions' => $commentOptions,
          'value' => key($commentOptions),
      ));
      $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
    }

    $this->addElement('Select', 'type', array(
        'label' => 'Video Source',
        'multiOptions' => array('0' => ' '),
        'onchange' => "updateTextFields()",
    ));

    $video_options = Array();
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey')) {
      $video_options[1] = "YouTube";
    }
    $video_options[2] = "Vimeo";

    $allowed_upload = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'upload');
    $ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;

    if ($ffmpeg_path && Engine_Api::_()->seaocore()->isSitemobileApp()) {
      $infoApp = Engine_Api::_()->sitemobile()->isApp(true);
      if (isset($infoApp['version']) && $infoApp['appOS'] == 'android' && version_compare($infoApp['version'], '4.4', '>=') && version_compare($infoApp['version'], '4.5', '<')) {
        $ffmpeg_path = false;
      }
    }
    if (!empty($ffmpeg_path) && $allowed_upload) {
      $video_options[3] = "My Computer";
    }
    $this->type->addMultiOptions($video_options);

    $this->addElement('Text', 'url', array(
        'label' => 'Video Link (URL)',
        'description' => 'Paste the web address of the video here.',
        'maxlength' => '50',
        
    ));
    $this->url->getDecorator("Description")->setOption("placement", "append");

    $this->addElement('Hidden', 'code', array(
        'order' => 10001
    ));
    $this->addElement('Hidden', 'id', array(
        'order' => 10002
    ));
    $this->addElement('Hidden', 'ignore', array(
        'order' => 10003
    ));

    $fancyUpload = new Engine_Form_Element_FancyUpload('file');
    $fancyUpload->clearDecorators()
            ->addDecorator('FormFancyUpload')
            ->addDecorator('viewScript', array(
                'viewScript' => '_FancyUploadVideo.tpl',
                'placement' => '',
            ));
    Engine_Form::addDefaultDecorators($fancyUpload);
    $this->addElement($fancyUpload);

    $this->addElement('Button', 'upload', array(
        'label' => 'Save Video',
        'type' => 'submit',
    ));
  }

  public function clearAlbum() {
    $this->getElement('album')->setValue(0);
  }

  public function saveValues() {

    $set_cover = False;
    $values = $this->getValues();
    $params = Array();
    if ((empty($values['owner_type'])) || (empty($values['owner_id']))) {
      $params['owner_id'] = Engine_Api::_()->user()->getViewer()->user_id;
      $params['owner_type'] = 'user';
    } else {
      $params['owner_id'] = $values['owner_id'];
      $params['owner_type'] = $values['owner_type'];
      throw new Zend_Exception("Non-user album owners not yet implemented");
    }

    if (($values['album'] == 0)) {
      $params['name'] = $values['name'];
      if (empty($params['name'])) {
        $params['name'] = "Untitled Album";
      }
      $params['description'] = $values['description'];
      $params['search'] = $values['search'];
      $album = Engine_Api::_()->getDbtable('albums', 'album')->createRow();
      $set_cover = True;
      $album->setFromArray($params);
      $album->save();
    } else {
      if (is_null($album)) {
        $album = Engine_Api::_()->getItem('album', $values['album']);
      }
    }

    $api = Engine_Api::_()->getDbtable('actions', 'activity');
    $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $album, 'album_photo_new', null, array('count' => count($values['file'])));

    $count = 0;
    foreach ($values['file'] as $photo_id) {
      $photo = Engine_Api::_()->getItem("album_photo", $photo_id);
      if (!($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity())
        continue;

      if ($set_cover) {
        $album->photo_id = $photo_id;
        $album->save();
        $set_cover = false;
      }
      $photo->collection_id = $album->album_id;
      $photo->save();

      if ($action instanceof Activity_Model_Action && $count < 8) {
        $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
      }
      $count++;
    }
    return $album;
  }

}
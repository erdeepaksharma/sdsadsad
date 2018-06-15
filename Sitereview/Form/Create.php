<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Create.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Create extends Engine_Form {

  public $_error = array();
  protected $_defaultProfileId;

  public function getDefaultProfileId() {
    return $this->_defaultProfileId;
  }

  public function setDefaultProfileId($default_profile_id) {
    $this->_defaultProfileId = $default_profile_id;
    return $this;
  }

  public function init() {
      
    $this->loadDefaultDecorators();
    //GET LISTING TYPE ID
    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    
    if(Engine_Api::_()->sitereview()->hasPackageEnable()) {
        if($this->_item) {
            $pakage_id = $this->_item->package_id;
        }else {
            $pakage_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', null);
        }
    }

    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $listingTypeInfo = Zend_Registry::isRegistered('sitereviewListingTypeInfo') ? Zend_Registry::get('sitereviewListingTypeInfo') : null;
    $listing_singular_uc = ucfirst($listingtypeArray->title_singular);
    $listing_singular_lc = strtolower($listingtypeArray->title_singular);
    $listing_plural_lc = strtolower($listingtypeArray->title_plural);
    $listing_plural_uc = ucfirst($listingtypeArray->title_plural);

    $listing_singular_upper = strtoupper($listingtypeArray->title_singular);
    $note = '';
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $reviewApi = Engine_Api::_()->sitereview();
    $expirySettings = $reviewApi->expirySettings($listingtype_id);
    if ($expirySettings == 2) {
      $translate = Zend_Registry::get('Zend_Translate');
      $duration = $listingtypeArray->admin_expiry_duration;
      $typeStr = $translate->translate(array($duration[1], $duration[1] . 's', $duration[0]));
      $note = sprintf($translate->translate('Note: your ' . $listing_singular_lc . ' will be expired in %1$s %2$s after an approval or it may be changed by admin.'), $duration[0], $typeStr);
    }

    if (!Engine_Api::_()->sitereview()->hasPackageEnable()) {
      $this->setTitle(sprintf(Zend_Registry::get('Zend_Translate')->_("Post New $listing_singular_uc")));
    }
    $this->setDescription(sprintf(Zend_Registry::get('Zend_Translate')->_("Compose your new $listing_singular_lc below, then click 'Submit' to publish the $listing_singular_lc.")))
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setAttrib('name', 'sitereviews_create')
            ->setAttrib('id', 'sitereviews_create');
$this->getDecorator('Description')->setOption('escape', false);
    $this->addElement('Text', 'title', array(
        'label' => "$listing_singular_uc Title",
        'allowEmpty' => false,
        'required' => true,
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
        //new Engine_Filter_StringLength(array('max' => '63')),
            )));

    $user = Engine_Api::_()->user()->getViewer();
    $user_level = Engine_Api::_()->user()->getViewer()->level_id;

    if (isset($listingtypeArray->show_tag) && $listingtypeArray->show_tag) {
      $this->addElement('Text', 'tags', array(
          'label' => $listing_singular_upper . '_TAG_(Keywords)',
          'autocomplete' => 'off',
          'description' => Zend_Registry::get('Zend_Translate')->_('SEPARATE_' . $listing_singular_upper . '_TAGS_WITH_COMMAS'),
          'filters' => array(
              new Engine_Filter_Censor(),
          ),
      ));    
      $this->tags->getDecorator("Description")->setOption("placement", "append");
    }

    $defaultProfileId = "0_0_" . $this->getDefaultProfileId();

    if (!$this->_item || (isset($this->_item->category_id) && empty($this->_item->category_id)) || ($this->_item && $listingtypeArray->category_edit)) {
      $translate = Zend_Registry::get('Zend_Translate');
      $categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 0, $listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name'));
      if (count($categories) != 0) {
        $categories_prepared[0] = "";
        foreach ($categories as $category) {
          $categories_prepared[$category->category_id] = $translate->translate($category->category_name);
        }

        if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
          $onChangeEvent = "showFields($(this).value, 1); subcategories(this.value, '', '');";
          $categoryFiles = 'application/modules/Sitereview/views/scripts/_formSubcategory.tpl';
        } else {
          $onChangeEvent = "showSRListingFields(this.value, 1);sm4.core.category.set(this.value, 'subcategory');";
          $categoryFiles = 'application/modules/Sitereview/views/sitemobile/scripts/_subCategory.tpl';
        }
        
        
        $this->addElement('Select', 'category_id', array(
            'label' => 'Category',
            'allowEmpty' => false,
            'required' => true,
            'multiOptions' => $categories_prepared,
            'onchange' => $onChangeEvent
        ));
        
        $this->addElement('Select', 'subcategory_id', array(
            'RegisterInArrayValidator' => false,
            'allowEmpty' => true,
            'required' => false,
        ));

        $this->addElement('Select', 'subsubcategory_id', array(
            'RegisterInArrayValidator' => false,
            'allowEmpty' => true,
            'required' => false,
        ));

        $this->addDisplayGroup(array(
            'subcategory_id',
            'subsubcategory_id',
                ), 'Select', array(
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => $categoryFiles,
                        'class' => 'form element')))
        ));
        
//        $this->addElement('Select', 'subcategory_id', array(
//          'RegisterInArrayValidator' => false,
//         // 'order' =>  $this->_searchFormSettings['category_id']['order'] + 1,
//          'decorators' => array(array('ViewScript', array(
//                      'viewScript' => $categoryFiles,
//                      'class' => 'form element')))
//      ));
      }
    }

    $allowOverview = Engine_Api::_()->authorization()->getPermission($user->level_id, 'sitereview_listing', "overview_listtype_$listingtypeArray->listingtype_id");
    $allowEdit = Engine_Api::_()->authorization()->getPermission($user->level_id, 'sitereview_listing', "edit_listtype_$listingtypeArray->listingtype_id");

    $overview_checkinpackage = true;
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($pakage_id, "overview"))
                $overview_checkinpackage = false;
        }

        if ($listingtypeArray->overview && $listingtypeArray->overview_creation && $allowOverview && $allowEdit && !$this->_item) {
      $description = 'Short Description';
    } else {
      $description = 'Description';
    }

    if ($listingtypeArray->body_allow) {
      if ($listingtypeArray->show_editor) {
        if (!empty($listingtypeArray->body_required)) {
          $this->addElement('textarea', 'body', array(
              'label' => $description,
              'required' => true,
              'allowEmpty' => false,
                        'filters' => array(
                    // 'StripTags',
                      new Engine_Filter_Censor(),
                    ),
          ));
        } else {
          $this->addElement('textarea', 'body', array(
              'label' => 'Description',
                         'filters' => array(
                  //   'StripTags',
                      new Engine_Filter_Censor(),
                    ),
          ));
        }
      } else {
        if (!empty($listingtypeArray->body_required)) {
          $this->addElement('textarea', 'body', array(
              'label' => $description,
              'required' => true,
              'allowEmpty' => false,
              'attribs' => array('rows' => 24, 'cols' => 180, 'style' => 'width:300px; max-width:400px;height:120px;'),
              'filters' => array(
             //     'StripTags',
                  //new Engine_Filter_HtmlSpecialChars(),
                  new Engine_Filter_EnableLinks(),
                  new Engine_Filter_Censor(),
              ),
          ));
        } else {
          $this->addElement('textarea', 'body', array(
              'label' => 'Description',
              'attribs' => array('rows' => 24, 'cols' => 180, 'style' => 'width:300px; max-width:400px;height:120px;'),
              'filters' => array(
               //   'StripTags',
                  //new Engine_Filter_HtmlSpecialChars(),
                  new Engine_Filter_EnableLinks(),
                  new Engine_Filter_Censor(),
              ),
          ));
        }
      }
    }

    if ($overview_checkinpackage && $listingtypeArray->overview && $listingtypeArray->overview_creation && $allowOverview && $allowEdit && !$this->_item) { 
      $this->addElement('Textarea', 'overview', array(
          'label' => 'DASHBOARD_' . $listing_singular_upper . '_OVERVIEW',
                    'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
      ));
    }

    $allowed_upload = Engine_Api::_()->authorization()->getPermission($user_level, 'sitereview_listing', "photo_listtype_$listingtype_id");
    if ($allowed_upload && ($listingtypeArray->photo_type == 'listing')) {
      $label = 'Main Photo';  
      if(stristr($listingtypeArray->title_singular, 'job') || stristr($listingtypeArray->title_singular, 'job')) {
          $label = 'Company Logo';
      }  
      $this->addElement('File', 'photo', array(
          'label' => $label
      ));
      $this->photo->addValidator('Extension', false, 'jpg,jpeg,png,gif');
    }

    $showLocation = 1;
    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($pakage_id, "map"))
        $showLocation = 1;
      else
        $showLocation = 0;
    }

    if ($listingtypeArray->location && !empty($showLocation)) {
      $this->addElement('Text', 'location', array(
          'label' => 'Location',
          'description' => 'Eg: Fairview Park, Berkeley, CA',
          'filters' => array(
              'StripTags',
              new Engine_Filter_Censor(),
              )));
      $this->location->getDecorator('Description')->setOption('placement', 'append');
      $this->addElement('Hidden', 'locationParams', array('order' => 800000));
      
      
      include_once APPLICATION_PATH.'/application/modules/Seaocore/Form/specificLocationElement.php';      
    }

    if ($listingtypeArray->price) {
      $localeObject = Zend_Registry::get('Locale');
      $currencyCode = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
      $currencyName = Zend_Locale_Data::getContent($localeObject, 'nametocurrency', $currencyCode);
      $this->addElement('Text', 'price', array(
          'label' => sprintf(Zend_Registry::get('Zend_Translate')->_('Price (%s)'), $currencyName),
          'validators' => array(
              array('NotEmpty', true),
              array('GreaterThan', false, array(-1))
          //array('GreaterThan', false, array(0))
          ),
          'filters' => array(
              'StripTags',
              new Engine_Filter_Censor(),
              )));
    }

    if (!$this->_item) {
      $customFields = new Sitereview_Form_Custom_Standard(array(
                  'item' => 'sitereview_listing',
                  'decorators' => array(
                      'FormElements'
                      )));
    } else {
      $customFields = new Sitereview_Form_Custom_Standard(array(
                  'item' => $this->getItem(),
                  'decorators' => array(
                      'FormElements'
                      )));
    }

    //START PACKAGE WORK
    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      $packageId = Zend_Controller_Front::getInstance()->getRequest()->getParam('id');
      $packageObject = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $packageId);
      if (!empty($packageObject))
        $profileField_level = $packageObject->profile;
      else
        $profileField_level = 1;

      if ($profileField_level == 2) {
        $fieldsProfile = array("0_0_1", "submit");

        $field_id = array();
        $fieldsProfile_2 = Engine_Api::_()->sitereviewpaidlisting()->getProfileFields();
        $fieldsProfile = array_merge($fieldsProfile, $fieldsProfile_2);

        foreach ($fieldsProfile_2 as $k => $v) {
          $explodeField = explode("_", $v);
          $field_id[] = $explodeField['2'];
        }

        $elements = $customFields->getElements();
        foreach ($elements as $key => $value) {
          $explode = explode("_", $key);
          if ($explode['0'] != "1" && $explode['0'] != "submit") {
            if (in_array($explode['0'], $field_id)) {
              $field_id[] = $explode['2'];
              $fieldsProfile[] = $key;
              continue;
            }
          }

          if (!in_array($key, $fieldsProfile)) {
            $customFields->removeElement($key);
            $customFields->addElement('Hidden', $key, array(
               'order' => 93224,
                "value" => "",
            ));
          }
        }
      } elseif ($profileField_level == 0) {
        $elements = $customFields->getElements();
        foreach ($elements as $key => $value) {
          $customFields->removeElement($key);
          $customFields->addElement('Hidden', $key, array(
             'order' => 93225,
              "value" => "",
          ));
        }
      }
    }

    //END PACKAGE WORK
    $customFields->removeElement('submit');
    if ($customFields->getElement($defaultProfileId)) {
      $customFields->getElement($defaultProfileId)
              ->clearValidators()
              ->setRequired(false)
              ->setAllowEmpty(true);
    }

    $this->addSubForms(array(
        'fields' => $customFields
    ));

    $availableLabels = array(
        'everyone' => 'Everyone',
        'registered' => 'All Registered Members',
        'owner_network' => 'Friends and Networks',
        'owner_member_member' => 'Friends of Friends',
        'owner_member' => 'Friends Only',
        'owner' => 'Just Me',
    );
    $orderPrivacyHiddenFields = 786590;
    $view_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $user, "auth_view_listtype_$listingtype_id");
    $view_options = array_intersect_key($availableLabels, array_flip($view_options));

    if (count($view_options) > 1) {
      $this->addElement('Select', 'auth_view', array(
          'label' => 'View Privacy',
          'description' => Zend_Registry::get('Zend_Translate')->_("Who may see this $listing_singular_lc?"),
          'multiOptions' => $view_options,
          'value' => key($view_options),
      ));
      $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
    } elseif (count($view_options) == 1) {
      $this->addElement('Hidden', 'auth_view', array(
          'value' => key($view_options),
          'order' => ++$orderPrivacyHiddenFields,
      ));
    } else {
      $this->addElement('Hidden', 'auth_view', array(
          'value' => "everyone",
          'order' => ++$orderPrivacyHiddenFields,
      ));
    }

    $comment_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $user, "auth_comment_listtype_$listingtype_id");
    $comment_options = array_intersect_key($availableLabels, array_flip($comment_options));

    if (count($comment_options) > 1) {
      $this->addElement('Select', 'auth_comment', array(
          'label' => 'Comment Privacy',
          'description' => Zend_Registry::get('Zend_Translate')->_("Who may comment on this $listing_singular_lc?"),
          'multiOptions' => $comment_options,
          'value' => key($comment_options),
      ));
      $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
    } elseif (count($comment_options) == 1) {
      $this->addElement('Hidden', 'auth_comment', array(
          'value' => key($comment_options),
          'order' => ++$orderPrivacyHiddenFields,
      ));
    } else {
      $this->addElement('Hidden', 'auth_comment', array('value' => "everyone",
          'order' => ++$orderPrivacyHiddenFields));
    }

    $availableLabels = array(
        'registered' => 'All Registered Members',
        'owner_network' => 'Friends and Networks',
        'owner_member_member' => 'Friends of Friends',
        'owner_member' => 'Friends Only',
        'owner' => 'Just Me',
    );

    $topic_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $user, "auth_topic_listtype_$listingtype_id");
    $topic_options = array_intersect_key($availableLabels, array_flip($topic_options));

    if (count($topic_options) > 1) {
      $this->addElement('Select', 'auth_topic', array(
          'label' => 'Discussion Topic Privacy',
          'description' => Zend_Registry::get('Zend_Translate')->_("Who may post discussion topics for this $listing_singular_lc?"),
          'multiOptions' => $topic_options,
          'value' => key($topic_options),
      ));
      $this->auth_topic->getDecorator('Description')->setOption('placement', 'append');
    } elseif (count($topic_options) == 1) {
      $this->addElement('Hidden', 'auth_topic', array(
          'value' => key($topic_options),
          'order' => ++$orderPrivacyHiddenFields,
      ));
    } else {
      $this->addElement('Hidden', 'auth_topic', array(
          'value' => 'registered',
          'order' => ++$orderPrivacyHiddenFields,
      ));
    }

    $photo_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $user, "auth_photo_listtype_$listingtype_id");
    $photo_options = array_intersect_key($availableLabels, array_flip($photo_options));

    $can_show_photo_list = true;
    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($pakage_id, "photo")) {
        $can_show_photo_list = false;
      }
    }

    if (count($photo_options) > 1 && $can_show_photo_list) {
      $this->addElement('Select', 'auth_photo', array(
          'label' => 'Photo Privacy',
          'description' => Zend_Registry::get('Zend_Translate')->_("Who may upload photos for this $listing_singular_lc?"),
          'multiOptions' => $photo_options,
          'value' => key($photo_options),
      ));
      $this->auth_photo->getDecorator('Description')->setOption('placement', 'append');
    } elseif (count($photo_options) == 1 && $can_show_photo_list) {
      $this->addElement('Hidden', 'auth_photo', array(
          'value' => key($photo_options),
          'order' => ++$orderPrivacyHiddenFields,
      ));
    } else {
      $this->addElement('Hidden', 'auth_photo', array(
          'value' => 'registered',
          'order' => ++$orderPrivacyHiddenFields,
      ));
    }

    $videoEnable = Engine_Api::_()->sitereview()->enableVideoPlugin();
    if ($videoEnable) {

      $video_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $user, "auth_video_listtype_$listingtype_id");
      $video_options = array_intersect_key($availableLabels, array_flip($video_options));

      $can_show_video_list = true;
      if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
        if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($pakage_id, "video")) {
          $can_show_video_list = false;
        }
      }

      if (count($video_options) > 1 && $can_show_video_list) {
        $this->addElement('Select', 'auth_video', array(
            'label' => 'Video Privacy',
            'description' => Zend_Registry::get('Zend_Translate')->_("Who may create videos for this $listing_singular_lc?"),
            'multiOptions' => $video_options,
            'value' => key($video_options),
        ));
        $this->auth_video->getDecorator('Description')->setOption('placement', 'append');
      } elseif (count($video_options) == 1 && $can_show_video_list) {
        $this->addElement('Hidden', 'auth_video', array(
            'value' => key($video_options),
            'order' => ++$orderPrivacyHiddenFields,
        ));
      } else {
        $this->addElement('Hidden', 'auth_video', array(
            'value' => 'registered',
            'order' => ++$orderPrivacyHiddenFields,
        ));
      }
    }

    if ((Engine_Api::_()->hasModuleBootstrap('siteevent') && Engine_Api::_()->getDbtable('modules', 'siteevent')->getIntegratedModules(array('enabled' => 1, 'item_type' => 'sitereview_listing_' . $listingtype_id, 'item_module' => 'sitereview')))) {
      $event_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $user, "auth_event_listtype_$listingtype_id");
      $event_options = array_intersect_key($availableLabels, array_flip($event_options));

      if (count($event_options) > 1 && Engine_Api::_()->authorization()->getAdapter('levels')->isAllowed('sitereview_listing', $user, "event_listtype_$listingtype_id")) {
        $this->addElement('Select', 'auth_event', array(
            'label' => 'Event Privacy',
            'description' => Zend_Registry::get('Zend_Translate')->_("Who may create event for this $listing_singular_lc?"),
            'multiOptions' => $event_options,
            'value' => key($event_options),
        ));
        $this->auth_event->getDecorator('Description')->setOption('placement', 'append');
      } elseif (count($event_options) == 1) {
        $this->addElement('Hidden', 'auth_event', array(
            'value' => key($event_options),
            'order' => ++$orderPrivacyHiddenFields,
        ));
      } else {
        $this->addElement('Hidden', 'auth_event', array(
            'value' => 'registered',
            'order' => ++$orderPrivacyHiddenFields,
        ));
      }
    }


    //START SITECROWDFUNDING PLUGIN WORK
      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitecrowdfunding') && (Engine_Api::_()->hasModuleBootstrap('sitecrowdfundingintegration') && Engine_Api::_()->getDbtable('modules', 'sitecrowdfunding')->getIntegratedModules(array('enabled' => 1, 'item_type' => 'sitereview_listing_' . $listingtype_id, 'item_module' => 'sitereview')))) {

        $options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $user, "auth_sprcreate_listtype_$listingtype_id");
        $options_create = array_intersect_key($availableLabels, array_flip($options));
            $userlevel_id = $user->level_id;
            if (!empty($options_create)) {
                $can_show_list = true;
                $can_create = Engine_Api::_()->authorization()->getPermission($userlevel_id, 'sitereview_listing', 'sprcreate');
                if (!$can_create) {
                    $can_show_list = false;
                    $this->addElement('Hidden', 'auth_sprcreate', array(
                       'order' => 93226,
                        'value' => @array_search(@end($options_create), $options_create)
                    ));
                }
                if ($can_show_list) {

                    if (count($options_create) > 1) {
                        $this->addElement('Select', 'auth_sprcreate', array(
                            'label' => 'Crowdfunding Projects Creation Privacy',
                            'description' => 'Who may create projects for this listing?',
                            'multiOptions' => $options_create,
                            'value' => @array_search(@end($options_create), $options_create),
                        ));
                        $this->auth_sprcreate->getDecorator('Description')->setOption('placement', 'append');
                    } elseif (count($options_create) == 1) {
                        $this->addElement('Hidden', 'auth_sprcreate', array(
                            'value' => key($options_create),
                            'order' => ++$orderPrivacyHiddenFields,
                        ));
                    } else {
                        $this->addElement('Hidden', 'auth_sprcreate', array(
                            'value' => 'registered',
                            'order' => ++$orderPrivacyHiddenFields,
                        ));
                    }
                }
            } else {
                $this->addElement('Hidden', 'auth_sprcreate', array(
                    'value' => 'registered',
                    'order' => ++$orderPrivacyHiddenFields,
                ));
            }
        }
      //END SITECROWDFUNDING PLUGIN WORK




        if ((Engine_Api::_()->hasModuleBootstrap('document') && Engine_Api::_()->getDbtable('modules', 'document')->getIntegratedModules(array('enabled' => 1, 'item_type' => 'sitereview_listing_' . $listingtype_id, 'item_module' => 'sitereview')))) {
      $document_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $user, "auth_doc_listtype_$listingtype_id");
      $document_options = array_intersect_key($availableLabels, array_flip($document_options));

      if (count($document_options) > 1 && Engine_Api::_()->authorization()->getAdapter('levels')->isAllowed('sitereview_listing', $user, "doc_listtype_$listingtype_id")) {
        $this->addElement('Select', 'auth_doc', array(
            'label' => 'Document Privacy',
            'description' => Zend_Registry::get('Zend_Translate')->_("Who may create document for this $listing_singular_lc?"),
            'multiOptions' => $document_options,
            'value' => key($document_options),
        ));
        $this->auth_doc->getDecorator('Description')->setOption('placement', 'append');
      } elseif (count($document_options) == 1) {
        $this->addElement('Hidden', 'auth_doc', array(
            'value' => key($document_options),
            'order' => ++$orderPrivacyHiddenFields,
        ));
      } else {
        $this->addElement('Hidden', 'auth_doc', array(
            'value' => 'registered',
            'order' => ++$orderPrivacyHiddenFields,
        ));
      }
    }
    
    //NETWORK BASE PAGE VIEW PRIVACY
    if (Engine_Api::_()->sitereview()->listBaseNetworkEnable()) {
      // Make Network List
      $table = Engine_Api::_()->getDbtable('networks', 'network');
      $select = $table->select()
              ->from($table->info('name'), array('network_id', 'title'))
              ->order('title');
      $result = $table->fetchAll($select);

      $networksOptions = array('0' => 'Everyone');
      foreach ($result as $value) {
        $networksOptions[$value->network_id] = $value->title;
      }

      if (count($networksOptions) > 0) {
        $this->addElement('Multiselect', 'networks_privacy', array(
            'label' => 'Networks Selection',
            'description' => Zend_Registry::get('Zend_Translate')->_("Select the networks, members of which should be able to see your $listing_singular_lc. (Press Ctrl and click to select multiple networks. You can also choose to make your $listing_singular_lc viewable to everyone.)"),
//            'attribs' => array('style' => 'max-height:150px; '),
            'multiOptions' => $networksOptions,
            'value' => array(0)
        ));
      } else {
        
      }
    }

    $this->addElement('Radio', 'end_date_enable', array(
        'label' => 'End Date',
        'multiOptions' => array("0" => "No end date.", "1" => "End $listing_singular_lc on a specific date. (Please select date by clicking on the calendar icon below.)"),
        'description' => "When should this $listing_singular_lc end?",
        'value' => 0,
        'onclick' => "updateTextFields(this)",
    ));
    // End time
    $end = new Engine_Form_Element_CalendarDateTime('end_date');
    $end->setAllowEmpty(false);
    $date = (string) date('Y-m-d');
    $end->setValue($date . ' 00:00:00');
    $this->addElement($end);

    if ($listingtypeArray->show_status) {
      $this->addElement('Select', 'draft', array(
          'label' => 'Status',
          'multiOptions' => array("0" => "Published", "1" => "Saved As Draft"),
          'description' => 'If this ' . $listing_singular_lc . ' is published, it cannot be switched back to draft mode.',
          'onchange' => 'checkDraft();'
      ));
      $this->draft->getDecorator('Description')->setOption('placement', 'append');
    }

    $tempValue = !empty($listingTypeInfo) ? $listingTypeInfo : false;
    $this->addElement('Hidden', 'listing_info', array(
       'order' => 93227,
        'value' => $tempValue
    ));

    if ($listingtypeArray->edit_creationdate && (!$this->_item || ($this->_item && (time() < strtotime($this->_item->creation_date)) || ($this->_item->draft) ))) {
      $creation_date = new Engine_Form_Element_CalendarDateTime('creation_date');
      $creation_date->setLabel("Publishing Date");
      $creation_date->setAllowEmpty(false);

      if (!$this->_item) {
        $now = time();
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($user->timezone);
        $creation_date->setValue(date("Y-m-d H:i:s", ($now + 3600)));
        date_default_timezone_set($oldTz);
      }

      $this->addElement($creation_date);
    }

    if ($listingtypeArray->show_browse) {
      $this->addElement('Checkbox', 'search', array(
          //'label' => "Show this $listing_singular_lc in search results",
          'label' => "Show this $listing_singular_lc on browse page and in various blocks.",
          'value' => 1,
      ));
    }

    $this->addElement('Button', 'execute', array(
        'label' => 'Submit',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), "sitereview_general_listtype_$listingtype_id", true),
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addDisplayGroup(array(
        'execute',
        'cancel',
            ), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper'
        ),
    ));
  }

}

<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Locationsearch.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Locationsearch extends Fields_Form_Search {

  protected $_searchForm;
  protected $_fieldType = 'sitereview_listing';
  protected $_value;
  protected $_listingTypeId;

  public function getListingTypeId() {
    return $this->_listingTypeId;
  }

  public function setListingTypeId($listingtype_id) {
    $this->_listingTypeId = $listingtype_id;
    return $this;
  }

  public function getValue($name = null) {
    return $this->_value;
  }

  public function setValue($item) {
    $this->_value = $item;
    return $this;
  }

  public function init() {

    $this->_value = unserialize($this->_value);

    //GET LISTING TYPE ID
    $listingtype_id = $this->getListingTypeId();
    if (empty($listingtype_id)) {
      $this->_listingTypeId = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }

    $front = Zend_Controller_Front::getInstance();
    $module = $front->getRequest()->getModuleName();
    $controller = $front->getRequest()->getControllerName();
    $action = $front->getRequest()->getActionName();

    // Add custom elements
    $this->setAttribs(array(
                'id' => 'filter_form',
                'class' => '',
            ))
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setMethod('POST');

    $this->_searchForm = Engine_Api::_()->getDbTable('searchformsetting', 'seaocore');

    $this->getMemberTypeElement();

    $this->getAdditionalOptionsElement();

    parent::init();

    $this->loadDefaultDecorators();
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;

    if ($module == 'sitereview' && $controller == 'index' && $action != 'map') {
      $this->setAction($view->url(array('action' => 'map'), 'sitereview_general', true))->getDecorator('HtmlTag')->setOption('class', '');
    }
  }

  public function getMemberTypeElement() {

    $multiOptions = array('' => ' ');
    $profileTypeFields = Engine_Api::_()->fields()->getFieldsObjectsByAlias($this->_fieldType, 'profile_type');
    if (count($profileTypeFields) !== 1 || !isset($profileTypeFields['profile_type']))
      return;
    $profileTypeField = $profileTypeFields['profile_type'];

    $options = $profileTypeField->getOptions();

    foreach ($options as $option) {
      $multiOptions[$option->option_id] = $option->label;
    }

    $this->addElement('hidden', 'profile_type', array(
        'order' => 10001,
        'class' =>
        'field_toggle' . ' ' .
        'parent_' . 0 . ' ' .
        'option_' . 0 . ' ' .
        'field_' . $profileTypeField->field_id . ' ',
        'onchange' => 'changeFields($(this));',
        'multiOptions' => $multiOptions,
    ));
    return $this->profile_type;
  }

  public function getAdditionalOptionsElement() {

    $front = Zend_Controller_Front::getInstance();
    $module = $front->getRequest()->getModuleName();
    $controller = $front->getRequest()->getControllerName();
    $action = $front->getRequest()->getActionName();

    //GET API
    $settings = Engine_Api::_()->getApi('settings', 'core');

    //GET LISTING TYPE ID
    $listingtype_id = $this->getListingTypeId();
    if (empty($listingtype_id)) {
      $this->_listingTypeId = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $this->_listingTypeId);
    $listing_plural_uc = ucfirst($listingtypeArray->title_plural);
    $subform = new Zend_Form_SubForm(array(
                'name' => 'extra',
                'order' => 19999999,
                'decorators' => array(
                    'FormElements',
                )
            ));
    Engine_Form::enableForm($subform);

    $i = 5000;

    $this->addElement('Text', 'search', array(
        'label' => 'What',
        'autocomplete' => 'off',
        'description' => '(Enter keywords or Listing name)',
        'order' => 1,
         'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
    ));
    $this->search->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));

    $this->addElement('Text', 'location', array(
        'label' => 'Where',
        'autocomplete' => 'off',
        'description' => '(address, city, state or country)',
        'order' => 2,
        'onclick' => 'locationPage();',
         'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
    ));
    $this->location->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));

    //$flage = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proximity.search.kilometer', 0);
    $flage = Engine_Api::_()->seaocore()->geoUserSettings('sitereview');
    if ($flage) {
      $locationLable = "Within Kilometers";
      $locationOption = array(
          '0' => '',
          '1' => '1 Kilometer',
          '2' => '2 Kilometers',
          '5' => '5 Kilometers',
          '10' => '10 Kilometers',
          '20' => '20 Kilometers',
          '50' => '50 Kilometers',
          '100' => '100 Kilometers',
          '250' => '250 Kilometers',
          '500' => '500 Kilometers',
          '750' => '750 Kilometers',
          '1000' => '1000 Kilometers',
      );
    } else {
      $locationLable = "Within Miles";
      $locationOption = array(
          '0' => '',
          '1' => '1 Mile',
          '2' => '2 Miles',
          '5' => '5 Miles',
          '10' => '10 Miles',
          '20' => '20 Miles',
          '50' => '50 Miles',
          '100' => '100 Miles',
          '250' => '250 Miles',
          '500' => '500 Miles',
          '750' => '750 Miles',
          '1000' => '1000 Miles',
      );
    }
    $this->addElement('Select', 'locationmiles', array(
        'label' => $locationLable,
        'multiOptions' => $locationOption,
        'value' => '0',
        'order' => 3,
    ));

    //Check for Location browse page.
    if ($module == 'list' && $controller == 'index' && $action != 'map') {
      $subform->addElement('Button', 'done', array(
          'label' => 'Search',
          'type' => 'submit',
          'ignore' => true,
      ));
      $this->addSubForm($subform, $subform->getName());
    } else {
      $subform->addElement('Button', 'done', array(
          'label' => 'Search',
          'type' => 'submit',
          'ignore' => true,
          'onclick' => 'return locationSearch();'
      ));
      $this->addSubForm($subform, $subform->getName());
    }

    // Element: cancel
    $this->addElement('Cancel', 'advances_search', array(
        'label' => 'Advanced search',
        'ignore' => true,
        'link' => true,
        'order' => 4,
        'onclick' => 'advancedSearchLists();',
        'decorators' => array('ViewHelper'),
    ));

    $this->addElement('hidden', 'advanced_search', array( 'order' => $i++,
        'value' => 0
    ));

    $this->addDisplayGroup(array('advances_search', 'locationmiles', 'search', 'done', 'location'), 'grp3');
    $button_group = $this->getDisplayGroup('grp3');
    $button_group->setDecorators(array(
        'FormElements',
        'Fieldset',
        array('HtmlTag', array('tag' => 'li', 'id' => 'group3', 'style' => 'width:100%;'))
    ));

    $group2 = array();

    if (!empty($this->_value['street'])) {
      $this->addElement('Text', 'sitereview_street', array(
          'label' => 'Street',
          'autocomplete' => 'off',
          'order' => 5,
           'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
      ));
      $group2[] = 'sitereview_street';
    }

    if (!empty($this->_value['city'])) {
      $this->addElement('Text', 'sitereview_city', array(
          'label' => 'City',
          'autocomplete' => 'off',
          'order' => 6,
                     'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),

      ));
      $group2[] = 'sitereview_city';
    }

    if (!empty($this->_value['state'])) {
      $this->addElement('Text', 'sitereview_state', array(
          'label' => 'State',
          'autocomplete' => 'off',
          'order' => 7,
                     'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
      ));
      $group2[] = 'sitereview_state';
    }

    if (!empty($this->_value['country'])) {
      $this->addElement('Text', 'sitereview_country', array(
          'label' => 'Country',
          'autocomplete' => 'off',
          'order' => 8,
      ));
      $group2[] = 'sitereview_country';
    }

    if (!empty($group2)) {
      $this->addDisplayGroup($group2, 'grp2');
      $button_group = $this->getDisplayGroup('grp2');
      $button_group->setDecorators(array(
          'FormElements',
          'Fieldset',
          array('HtmlTag', array('tag' => 'li', 'id' => 'group2', 'style' => 'width:100%;'))
      ));
    }

    if ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2) {
      $multiOptionsOrderBy = array(
          '' => "",
          'title' => "Alphabetic",
          'creation_date' => 'Most Recent',
          'view_count' => 'Most Viewed',
          'like_count' => "Most Liked",
          'comment_count' => "Most Commented",
          'review_count' => "Most Reviewed",
          'rating_avg' => "Most Rated",
      );
    } elseif ($listingtypeArray->reviews == 1) {
      $multiOptionsOrderBy = array(
          '' => "",
          'title' => "Alphabetic",
          'creation_date' => 'Most Recent',
          'view_count' => 'Most Viewed',
          'like_count' => "Most Liked",
          'comment_count' => "Most Commented",
          'rating_avg' => "Most Rated",
      );
    } else {
      $multiOptionsOrderBy = array(
          '' => "",
          'title' => "Alphabetic",
          'creation_date' => 'Most Recent',
          'view_count' => 'Most Viewed',
          'like_count' => "Most Liked",
          'comment_count' => "Most Commented",
      );
    }

    $this->addElement('Select', 'orderby', array(
        'label' => 'Browse By',
        'multiOptions' => $multiOptionsOrderBy,
        'order' => 9,
    ));

    $this->addElement('Select', 'closed', array(
        'label' => 'Status',
        'multiOptions' => array(
            '' => "All $listing_plural_uc",
            '0' => "Only Open $listing_plural_uc",
            '1' => "Only Closed $listing_plural_uc"
        ),
        'order' => 10,
    ));

    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $show_multiOptions = array();
    $show_multiOptions["1"] = "Everyone's $listing_plural_uc";
    $show_multiOptions["2"] = "Only My Friends' $listing_plural_uc";
    $show_multiOptions["4"] = "$listing_plural_uc I Like";
    $value_deault = 1;
    $enableNetwork = $settings->getSetting('sitereview.network', 0);
    if (empty($enableNetwork)) {
      $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
      $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      $viewerNetwork = $networkMembershipTable->fetchRow(array('user_id = ?' => $viewer_id));

      if (!empty($viewerNetwork)) {
        $show_multiOptions["3"] = 'Only My Networks';
        $browseDefaulNetwork = $settings->getSetting('sitereview.default.show', 0);

        if (!isset($_GET['show']) && !empty($browseDefaulNetwork)) {
          $value_deault = 3;
        } elseif (isset($_GET['show'])) {
          $value_deault = $_GET['show'];
        }
      }
    }

    if (!empty($viewer_id)) {
      $this->addElement('Select', 'show', array(
          'label' => 'Show',
          'multiOptions' => $show_multiOptions,
          'order' => 11,
          'value' => $value_deault,
      ));
    }

    if ($listingtypeArray->reviews) {
      if ($listingtypeArray->reviews == 3) {
        $multiOptions = array(
            '' => '',
            'rating_avg' => 'Any Reviews',
            'rating_editor' => 'Editor Reviews',
            'rating_users' => 'User Reviews',
        );
      } elseif ($listingtypeArray->reviews == 2) {
        $multiOptions = array(
            '' => '',
            'rating_users' => 'User Reviews',
        );
      } elseif ($listingtypeArray->reviews == 1) {
        $multiOptions = array(
            '' => '',
            'rating_editor' => 'Editor Reviews',
        );
      }

      $this->addElement('Select', 'has_review', array(
          'label' => "$listing_plural_uc Having",
          'multiOptions' => $multiOptions,
          'order' => 12,
          'value' => '',
      ));
    }

    $this->addElement('Checkbox', 'has_photo', array(
        'label' => "Only $listing_plural_uc With Photos",
        'order' => 13,
    ));

    $categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 0, $listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name'));
    if (count($categories) != 0) {
      $categories_prepared[0] = "";
      foreach ($categories as $category) {
        $categories_prepared[$category->category_id] = $category->category_name;
      }

      $this->addElement('Select', 'category_id', array(
          'label' => 'Category',
          'multiOptions' => $categories_prepared,
          'order' => 20,
          'onchange' => "showFields(this.value, 1); addOptions(this.value, 'cat_dependency', 'subcategory_id', 0);",
      ));

      $this->addElement('Select', 'subcategory_id', array(
          'RegisterInArrayValidator' => false,
          'order' => 21,
          'decorators' => array(array('ViewScript', array(
                      'viewScript' => 'application/modules/Sitereview/views/scripts/_subCategory.tpl',
                      'class' => 'form element')))
      ));
    }

    $this->addElement('Hidden', 'page', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'tag', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'tag_id', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'start_date', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'end_date', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'categoryname', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'subcategoryname', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'subsubcategoryname', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'Latitude', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'Longitude', array(
        'order' => $i++,
    ));

    $this->addDisplayGroup(array('profile_type', 'orderby', 'show', 'has_photo', 'closed', 'has_review', 'category_id'), 'grp1');
    $button_group = $this->getDisplayGroup('grp1');
    $button_group->setDecorators(array(
        'FormElements',
        'Fieldset',
        array('HtmlTag', array('tag' => 'li', 'id' => 'group1', 'style' => 'width:100%;'))
    ));

    return $this;
  }

}
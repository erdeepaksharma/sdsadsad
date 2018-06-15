<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Search.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Search extends Sitereview_Form_Searchfields {

  protected $_fieldType = 'sitereview_listing';
  protected $_searchFormSettings;
  protected $_listingTypeId;
  protected $_hasMobileMode = false;
  protected $_widgetSettings;
 
  public function getWidgetSettings() {
      return $this->_widgetSettings;
  }

  public function setWidgetSettings($widgetSettings) {
      $this->_widgetSettings = $widgetSettings;
      return $this;
  }

  public function getListingTypeId() {
    return $this->_listingTypeId;
  }

  public function setListingTypeId($listingtype_id) {
    $this->_listingTypeId = $listingtype_id;
    return $this;
  }

  public function getHasMobileMode() {
    return $this->_hasMobileMode;
  }

  public function setHasMobileMode($flage) {
    $this->_hasMobileMode = $flage;
    return $this;
  }

  public function init() {
    $this
            ->setAttribs(array(
                'id' => 'filter_form',
                'class' => 'sitereviews_browse_filters field_search_criteria',
                'method' => 'GET'
            ));
    parent::init();
    
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $this->loadDefaultDecorators();

    $front = Zend_Controller_Front::getInstance();
    $module = $front->getRequest()->getModuleName();
    $controller = $front->getRequest()->getControllerName();
    $action = $front->getRequest()->getActionName();

    //GET LISTING TYPE ID
    $listingtype_id = $this->getListingTypeId();
    if (empty($listingtype_id)) {
      $this->_listingTypeId = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }

    //GET SEARCH FORM SETTINGS
    $this->_searchFormSettings = Engine_Api::_()->getDbTable('searchformsetting', 'seaocore')->getModuleOptions('sitereview_listtype_' . $listingtype_id); 
    
    if (!empty($this->_searchFormSettings['category_id']) && !empty($this->_searchFormSettings['category_id']['display'])) {
        $this->getMemberTypeElement();
    }
    $this->getAdditionalOptionsElement();

    if ($module == 'sitereview' && $controller == 'index' && $action == 'manage') {
      $this->setAction($view->url(array('action' => 'manage'), "sitereview_general_listtype_$listingtype_id", true))->getDecorator('HtmlTag')->setOption('class', 'browsesitereviews_criteria');
    } 
    elseif ($module == 'sitereview' && $controller == 'index' && $action == 'top-rated') {
      $this->setAction($view->url(array('action' => 'top-rated'), "sitereview_general_listtype_$listingtype_id", true))->getDecorator('HtmlTag')->setOption('class', 'browsesitereviews_criteria');
    } 
    else {
      $this->setAction($view->url(array('action' => 'index'), "sitereview_general_listtype_$listingtype_id", true))->getDecorator('HtmlTag')->setOption('class', 'browsesitereviews_criteria');
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

    $i = 99980;

    $this->addElement('Hidden', 'page', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'tag', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'tag_id', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'city', array(
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
    
    $this->addElement('Hidden', 'latitude', array(
        'order' => $i++,
    ));
 
    $this->addElement('Hidden', 'longitude', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'Latitude', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'Longitude', array(
        'order' => $i++,
    ));

    $myLocationDetails = Engine_Api::_()->seaocore()->getMyLocationDetailsCookie(); 

    //GET LISTING TYPE ID
    $listingtype_id = $this->getListingTypeId();
    if (empty($listingtype_id)) {
      $this->_listingTypeId = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }

    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $this->_listingTypeId);
    $listing_plural_uc = ucfirst($listingtypeArray->title_plural);

    if (!empty($this->_searchFormSettings['search']) && !empty($this->_searchFormSettings['search']['display'])) {
      $this->addElement('Text', 'search', array(
          'label' => 'Name / Keyword',
          'order' => $this->_searchFormSettings['search']['order'],
          'decorators' => array(
              'ViewHelper',
              array('Label', array('tag' => 'span')),
              array('HtmlTag', array('tag' => 'li'))
          ),
           'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
      ));
    }

    //GET API
    $settings = Engine_Api::_()->getApi('settings', 'core');

    if (!empty($this->_searchFormSettings['location']) && !empty($this->_searchFormSettings['location']['display']) && !empty($listingtypeArray->location)) {
      $this->addElement('Text', 'location', array(
          'label' => 'Location',
          'order' => $this->_searchFormSettings['location']['order'],
          'decorators' => array(
              'ViewHelper',
              array('Label', array('tag' => 'span')),
              array('HtmlTag', array('tag' => 'li'))
          ),
           'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
      ));
       
        $myLocationDetails = Engine_Api::_()->seaocore()->getMyLocationDetailsCookie();
        if (isset($_GET['location'])) {
          $this->location->setValue($_GET['location']);
        } elseif (isset($_GET['locationSearch'])) {
          $this->location->setValue($_GET['locationSearch']);
        } elseif (isset($myLocationDetails['location'])) {
          $this->location->setValue($myLocationDetails['location']);
        }

        if (isset($_GET['location']) || isset($_GET['locationSearch'])) {
          Engine_Api::_()->seaocore()->setMyLocationDetailsCookie($myLocationDetails);
        }

        if (!isset($_GET['location']) && !isset($_GET['locationSearch']) && isset($this->_widgetSettings['locationDetection']) && empty($this->_widgetSettings['locationDetection'])) {
          $this->location->setValue('');
        }


      if (!empty($this->_searchFormSettings['proximity']) && !empty($this->_searchFormSettings['proximity']['display'])) {

        $flage = $settings->getSetting('sitereview.proximity.search.kilometer', 0);
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
            'order' => $this->_searchFormSettings['proximity']['order'] + 1,
            'decorators' => array(
                'ViewHelper',
                array('Label', array('tag' => 'span')),
                array('HtmlTag', array('tag' => 'li'))
            ),
        ));
            
          
        if (isset($_GET['locationmiles'])) {
          $this->locationmiles->setValue($_GET['locationmiles']);
        } elseif (isset($_GET['locationmilesSearch'])) {
          $this->locationmiles->setValue($_GET['locationmilesSearch']);
        } elseif (isset($myLocationDetails['locationmiles'])) {
          $this->locationmiles->setValue($myLocationDetails['locationmiles']);
        }
      }
    }

    if (!empty($this->_searchFormSettings['orderby']) && !empty($this->_searchFormSettings['orderby']['display'])) {

      if ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2) {
        $multiOptionsOrderBy = array(
            '' => "",
            'creation_date' => 'Most Recent',
            'title' => "Alphabetic",
            'view_count' => 'Most Viewed',
            'like_count' => "Most Liked",
            'comment_count' => "Most Commented",
            'review_count' => "Most Reviewed",
            'rating_avg' => "Most Rated",
        );
      } elseif ($listingtypeArray->reviews == 1) {
        $multiOptionsOrderBy = array(
            '' => "",
            'creation_date' => 'Most Recent',
            'title' => "Alphabetic",
            'view_count' => 'Most Viewed',
            'like_count' => "Most Liked",
            'comment_count' => "Most Commented",
            'rating_avg' => "Most Rated",
        );
      } else {
        $multiOptionsOrderBy = array(
            '' => "",
            'creation_date' => 'Most Recent',
            'title' => "Alphabetic",
            'view_count' => 'Most Viewed',
            'like_count' => "Most Liked",
            'comment_count' => "Most Commented",
        );
      }

      $this->addElement('Select', 'orderby', array(
          'label' => 'Browse By',
          'multiOptions' => $multiOptionsOrderBy,
          'onchange' => $this->gethasMobileMode() ? '' : 'searchSitereviews();',
          'order' => $this->_searchFormSettings['orderby']['order'],
          'decorators' => array(
              'ViewHelper',
              array('Label', array('tag' => 'span')),
              array('HtmlTag', array('tag' => 'li'))
          ),
      ));
    } else {
      $this->addElement('hidden', 'orderby', array( 'order' => $i++,
      ));
    }

    if (!empty($this->_searchFormSettings['closed']) && !empty($this->_searchFormSettings['closed']['display'])) {
      $this->addElement('Select', 'closed', array(
          'label' => 'Status',
          'multiOptions' => array(
              '' => Zend_Registry::get('Zend_Translate')->_("All $listing_plural_uc"),
              '0' => Zend_Registry::get('Zend_Translate')->_("Only Open $listing_plural_uc"),
              '1' => Zend_Registry::get('Zend_Translate')->_("Only Closed $listing_plural_uc")
          ),
          'onchange' => $this->gethasMobileMode() ? '' : 'searchSitereviews();',
          'order' => $this->_searchFormSettings['closed']['order'],
          'decorators' => array(
              'ViewHelper',
              array('Label', array('tag' => 'span')),
              array('HtmlTag', array('tag' => 'li'))
          ),
      ));
    }

    if (!empty($this->_searchFormSettings['show']) && !empty($this->_searchFormSettings['show']['display'])) {
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
      //$reviewApi = Engine_Api::_()->sitereview();
      //$expirySettings = $reviewApi->expirySettings($this->_listingTypeId);
      if ($listingtypeArray->expiry) {
        $show_multiOptions["only_expiry"] = "Only Expired $listing_plural_uc";
      }

      $this->addElement('Select', 'show', array(
          'label' => 'Show',
          'multiOptions' => $show_multiOptions,
          'onchange' => $this->gethasMobileMode() ? '' : 'searchSitereviews();',
          'order' => $this->_searchFormSettings['show']['order'],
          'decorators' => array(
              'ViewHelper',
              array('Label', array('tag' => 'span')),
              array('HtmlTag', array('tag' => 'li'))
          ),
          'value' => $value_deault,
      ));
    } else {
      $this->addElement('hidden', 'show', array( 'order' => $i++,
          'value' => 1
      ));
    }

    if (!empty($this->_searchFormSettings['price']) && !empty($this->_searchFormSettings['price']['display']) && $listingtypeArray->price) {
      $subform = new Engine_Form(array(
                  'description' => 'Price',
                  'elementsBelongTo' => 'price',
                  'order' => $this->_searchFormSettings['price']['order'],
                  'decorators' => array(
                      'FormElements',
                      array('Description', array('placement' => 'PREPEND', 'tag' => 'label', 'class' => 'form-label')),
                      //array('Label', array('tag' => 'span')),
                      array('HtmlTag', array('tag' => 'li', 'class' => '', 'id' => 'integer-wrapper'))
                  )
              ));
      //Engine_Form::enableForm($subform);
      //unset($params['options']['label']);
      $params['options']['decorators'] = array('ViewHelper', array('HtmlTag', array('tag' => 'div', 'class' => 'form-element')));
      $params['options']['decorators'] = array('ViewHelper');
      //if($this->gethasMobileMode())
      $params['options']['placeholder'] = 'min';
      $subform->addElement('text', 'min', $params['options']);
      //if($this->gethasMobileMode())
      $params['options']['placeholder'] = 'max';
      $subform->addElement('text', 'max', $params['options']);
      $this->addSubForm($subform, 'price');
    }

    if (!empty($this->_searchFormSettings['category_id']) && !empty($this->_searchFormSettings['category_id']['display'])) {
$translate = Zend_Registry::get('Zend_Translate');
      $categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategoriesByLevel($listingtype_id, 'category', array('category_id', 'category_name'));
      if (count($categories) != 0) {
        $categories_prepared[0] = "";
        foreach ($categories as $category) {
          $categories_prepared[$category->category_id] = $translate->translate($category->category_name);
        }

        if (!$this->gethasMobileMode()) {
          $onChangeEvent = "showFields(this.value, 1); addOptions(this.value, 'cat_dependency', 'subcategory_id', 0);";
          $categoryFiles = 'application/modules/Sitereview/views/scripts/_subCategory.tpl';
        } else {
          $onChangeEvent = "showSRListingFields(this.value, 1);sm4.core.category.set(this.value, 'subcategory');";
          $categoryFiles = 'application/modules/Sitereview/views/sitemobile/scripts/_subCategory.tpl';
        }
        $this->addElement('Select', 'category_id', array(
            'label' => 'Category',
            'order' => $this->_searchFormSettings['category_id']['order'],
            'multiOptions' => $categories_prepared,
            'onchange' => $onChangeEvent,
            'decorators' => array(
                'ViewHelper',
                array('Label', array('tag' => 'span')),
                array('HtmlTag', array('tag' => 'li'))),
        ));

        $this->addElement('Select', 'subcategory_id', array(
            'RegisterInArrayValidator' => false,
            'order' => $this->_searchFormSettings['category_id']['order'] + 1,
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => $categoryFiles,
                        'class' => 'form element')))
        ));
      }
    } else {
            $this->addElement('Hidden', 'category_id', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'subcategory_id', array(
        'order' => $i++,
    ));

    $this->addElement('Hidden', 'subsubcategory_id', array(
        'order' => $i++,
    ));
    }

    if (!empty($this->_searchFormSettings['has_photo']) && !empty($this->_searchFormSettings['has_photo']['display'])) {
      $this->addElement('Checkbox', 'has_photo', array(
          'label' => "Only $listing_plural_uc With Photos",
          'order' => $this->_searchFormSettings['has_photo']['order'],
          'decorators' => array(
              'ViewHelper',
              array('Label', array('placement' => 'APPEND', 'tag' => 'label')),
              array('HtmlTag', array('tag' => 'li'))
          ),
      ));
    }

    if (!empty($this->_searchFormSettings['has_review']) && !empty($this->_searchFormSettings['has_review']['display']) && $listingtypeArray->reviews) {

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
          'onchange' => $this->gethasMobileMode() ? '' : 'searchSitereviews();',
          'order' => $this->_searchFormSettings['has_review']['order'],
          'decorators' => array(
              'ViewHelper',
              array('Label', array('tag' => 'span')),
              array('HtmlTag', array('tag' => 'li'))
          ),
          'value' => '',
      ));
    }
    if ($this->gethasMobileMode()) {
      $this->addElement('Button', 'done', array(
          'label' => 'Search',
          'type' => 'submit',
          'ignore' => true,
          'order' => 999999999,
          'decorators' => array(
              'ViewHelper',
              //array('Label', array('tag' => 'span')),
              array('HtmlTag', array('tag' => 'li'))
          ),
      ));
    } else {
      $this->addElement('Button', 'done', array(
          'label' => 'Search',
          'onclick' => 'searchSitereviews();',
          'ignore' => true,
          'order' => 999999999,
          'decorators' => array(
              'ViewHelper',
              //array('Label', array('tag' => 'span')),
              array('HtmlTag', array('tag' => 'li'))
          ),
      ));
    }
  }

}

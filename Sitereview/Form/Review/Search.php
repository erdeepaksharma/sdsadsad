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
class Sitereview_Form_Review_Search extends Sitereview_Form_Searchfields {

  protected $_fieldType = 'sitereview_review';
  protected $_searchForm;
  protected $_hasMobileMode = false;

  public function getHasMobileMode() {
    return $this->_hasMobileMode;
  }

  public function setHasMobileMode($flage) {
    $this->_hasMobileMode = $flage;
    return $this;
  }

  public function init() {

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $this->setAttribs(array(
                'id' => 'filter_form',
                'class' => 'global_form_box',
            ))
             ->setMethod('get')
            ->setAction($view->url(array(), "sitereview_review_browse", true));

    parent::init();

    $order = 1;

    $this->addElement('Text', 'search', array(
        'label' => 'Search',
        'order' => $order++,
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    ));
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    if ($viewer_id) {
      $this->addElement('Select', 'show', array(
          'label' => 'Show',
          'multiOptions' => array('' => "Everyone's Reviews", 'friends_reviews' => "My Friends' Reviews", 'self_reviews' => "My Reviews", 'featured' => "Featured Reviews"),
          'order' => $order++,
      ));
    }

    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    if (!empty($listingtype_id)) {
      Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
      $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
      if ($listingtypeArray->reviews == 3) {
        $this->addElement('Select', 'type', array(
            'label' => 'Reviews Written By',
            'multiOptions' => array('' => 'Everyone', 'editor' => 'Editors', 'user' => 'Users'),
            'onchange' => "addReviewTypeOptions(this.value);",
            'order' => $order++,
        ));
      }
    } else {
      $this->addElement('Select', 'type', array(
          'label' => 'Reviews Written By',
          'multiOptions' => array('' => 'Everyone', 'editor' => 'Editors', 'user' => 'Users'),
          'onchange' => "addReviewTypeOptions(this.value);",
          'order' => $order++,
      ));
    }

    $params = array();
    $params['visible'] = 1;
    $params['allowUserReview'] = 1;
    $ListingTypesArray = Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypesArray(0, 1, $params);
    if (count($ListingTypesArray) > 2) {
      $this->addElement('Select', 'listingtype_id', array(
          'label' => 'Reviews For',
          'multiOptions' => $ListingTypesArray,
          'onchange' => $this->getHasMobileMode()?"sm4.core.category.set(this.value, 'category');":"addOptions(this.value, 'listingtype_id', 'category_id', 0);",
          'order' => $order++,
      ));
    } else {
      unset($ListingTypesArray['0']);
      $this->addElement('hidden', 'listingtype_id', array(
          'label' => 'Type',
          'value' => key($ListingTypesArray),
          'order' => $order++,
      ));
    }

    $this->addElement('Select', 'category_id', array(
        'RegisterInArrayValidator' => false,
        'order' => $order++,
        'decorators' => array(array('ViewScript', array(
                    'viewScript' => 'application/modules/Sitereview/views'.($this->getHasMobileMode()?'/sitemobile':'').'/scripts/review/_browse_search_category.tpl',
                    'class' => 'form element')))
    ));

    $this->addElement('Hidden', 'categoryname', array(
        'order' => $order++,
    ));

    $this->addElement('Hidden', 'subcategoryname', array(
        'order' => $order++,
    ));

    $this->addElement('Hidden', 'subsubcategoryname', array(
        'order' => $order++,
    ));

    $this->getMemberTypeElement();

    $this->addElement('Select', 'order', array(
        'label' => 'Browse By',
        'order' => $order++ + 50000,
        'multiOptions' => array(
            'recent' => 'Most Recent',
            'rating_highest' => 'Highest Rating',
            'rating_lowest' => 'Lowest Rating',
            'helpfull_most' => 'Most Helpful',
            'replay_most' => 'Most Reply',
            'view_most' => 'Most Viewed'
        ),
    ));
    $this->addElement('Select', 'rating', array(
        'label' => 'Ratings',
        'order' => $order++ + 50000,
        'multiOptions' => array(
            '' => '',
            '5' => sprintf(Zend_Registry::get('Zend_Translate')->_('%1s Star'), 5),
            '4' => sprintf(Zend_Registry::get('Zend_Translate')->_('%1s Star'), 4),
            '3' => sprintf(Zend_Registry::get('Zend_Translate')->_('%1s Star'), 3),
            '2' => sprintf(Zend_Registry::get('Zend_Translate')->_('%1s Star'), 2),
            '1' => sprintf(Zend_Registry::get('Zend_Translate')->_('%1s Star'), 1),
        ),
    ));

    $this->addElement('Checkbox', 'recommend', array(
        'label' => 'Only Recommended Reviews',
        'order' => $order++ + 50000,
    ));
    $this->addElement('Hidden', 'page', array(
        'value' => '1',
        'order' => $order++ + 50000,
    ));
    $this->addElement('Button', 'done', array(
        'label' => 'Search',
        'order' => $order++ + 50000,
        'type' => 'Submit',
        'ignore' => true,
    ));
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
        'order' => 100001,
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

}
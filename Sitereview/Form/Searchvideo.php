
<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Searchvideo.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Searchvideo extends Engine_Form {

  public function init() {

    $this->setAttribs(array(
                'id' => 'filter_form',
                'class' => 'global_form_box',
            ))
            ->setMethod('GET')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
    ;

    $listingTypesFormMultiOptions = Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypesArray(0, 1);

    $this->addElement('Text', 'text', array(
        'label' => 'Video Keywords',
         'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
    ));

    $this->addElement('Hidden', 'tag', array('order' => 6001,));

    $this->addElement('Select', 'orderby', array(
        'label' => 'Browse By',
        'multiOptions' => array(
            'creation_date' => 'Most Recent',
            'view_count' => 'Most Viewed',
            'rating' => 'Highest Rated',
            'comment_count' => 'Most Commented',
            'like_count' => 'Most Liked',
        ),
        'onchange' => 'this.form.submit();',
    ));

    // category field
    if (count($listingTypesFormMultiOptions) > 2) {
      $this->addElement('Select', 'listingtype_id', array(
          'label' => 'Listings',
          'multiOptions' => $listingTypesFormMultiOptions,
          'onchange' => 'this.form.submit();'
      ));
    }
  }

}
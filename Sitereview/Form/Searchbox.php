<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Searchbox.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Searchbox extends Engine_Form {

  public function init() {
    
    $this->addElement('Text', 'title', array(
        'label' => '',
        'autocomplete' => 'off',
         'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
         ));
    $this->addElement('Hidden', 'listing_id', array('order' => 6001,));
  }

}
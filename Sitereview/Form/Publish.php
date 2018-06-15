<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Publish.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Publish extends Engine_Form {

  public function init() {

    //GET LISTING TYPE ID
    $listing_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', null);
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    $listingtypeArray = Engine_Api::_()->getItem('sitereview_listingtype', $sitereview->listingtype_id);//Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
    $listing_singular_uc = ucfirst($listingtypeArray->title_singular);
    $listing_singular_lc = strtolower($listingtypeArray->title_singular);

    $this->setTitle(sprintf(Zend_Registry::get('Zend_Translate')->_("Publish $listing_singular_uc")))
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setAttrib('name', 'sitereviews_publish');
    
    if($listingtypeArray->edit_creationdate && (!$sitereview || ($sitereview && (time() < strtotime($sitereview->creation_date)) || ($sitereview->draft) ))) {
        $creation_date = new Engine_Form_Element_CalendarDateTime('creation_date');
        $creation_date->setLabel("Publishing Date");
        $creation_date->setAllowEmpty(false);        
        $this->addElement($creation_date);       
    }    
      
    if($listingtypeArray->show_browse) {
			$this->addElement('Checkbox', 'search', array(
					'label' => "Show this $listing_singular_lc in search results.",
					'value' => 1,
			));
    } else {
        $this->addElement('Hidden', 'search', array(
             'order' => 523,
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
        'onclick' => 'parent.Smoothbox.close();',
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

<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Metainfo.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Metainfo extends Engine_Form {

  public function init() {

    $sitereview = Engine_Api::_()->getItem('sitereview_listing', Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', null));
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
    $listing_singular_lc = strtolower($listingtypeArray->title_singular);
    $this->setTitle('Meta Keywords')
            ->setDescription("Meta keywords are a great way to provide search engines with information about your $listing_singular_lc so that search engines populate your $listing_singular_lc in search results. Below, you can add meta keywords for this $listing_singular_lc. (The tags entered by you for this $listing_singular_lc will also be added to the meta keywords.)")
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setAttrib('name', 'metainfo');

    $this->addElement('Textarea', 'keywords', array(
        'label' => 'Meta Keywords',
        'description' => 'Separate meta tags with commas.',
         'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
    ));

    $this->keywords->getDecorator('Description')->setOption('placement', 'append');
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Details',
        'type' => 'submit',
        'ignore' => true,
    ));
  }

}
<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Style.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Style extends Engine_Form {

  public function init() {

    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $listing_singular_uc = ucfirst($listingtypeArray->title_singular);
    $listing_singular_lc = strtolower($listingtypeArray->title_singular);
    $this
            ->setTitle("Edit $listing_singular_uc Style")
            ->setDescription("Edit the CSS style of your $listing_singular_lc using the text area below, and then click 'Save Style' to save changes.")
            ->setMethod('post')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $this->addElement('Textarea', 'style', array(
        'label' => "Custom Advanced $listing_singular_uc Style",
        'description' => "Add your own CSS code above to give your $listing_singular_lc a more personalized look.",
         'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
    ));
    $this->style->getDecorator('Description')->setOption('placement', 'APPEND');

    $this->addElement('Button', 'submit', array(
        'label' => 'Save Style',
        'type' => 'submit',
    ));
  }

}
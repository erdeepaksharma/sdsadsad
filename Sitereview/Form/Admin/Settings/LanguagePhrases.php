<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: LanguagePhrases.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Settings_LanguagePhrases extends Engine_Form {

  protected $_isArray = true;
  protected $_elementsBelongTo = 'language_phrases';

  public function init() {

		$this->addElement('Dummy', 'sitereview_language_phrases', array(
				'label' => 'Replace Language Phrases',
        'description' => 'Enter the text for the language phrases mentioned below for this listing type.'
		));
    $this->clearDecorators()
            ->addDecorator('FormElements');
    $elements = Engine_Api::_()->getApi('language', 'sitereview')->getDataWithoutKeyPhase();
    foreach ($elements as $key => $element) {
      if($key == 'text_Where_to_Buy') {
        $description = "For ex: You can replace 'References' in Blog, News, etc. listing types.";
      } elseif($key == 'text_listing') {
        $description = "For ex: You can replace 'Entries', 'Items' in Blog, Fashion, etc. listing types.";
      } elseif($key == 'text_store') {
        $description = "For ex: You can replace 'Site' in Blog, News, etc. listing types.";
      } else {
        $description ="";
      }
      $this->addElement('Text', $key, array(
          'label' => "Text for '$element'",
          'description' => $description,
          'value' => $element,
          'allowEmpty' => false,
          'required' => true
      ));
      $this->$key->addDecorator('Description', array('placement' => 'APPEND', 'class' => 'description', 'escape' => false));
    }
 
  }

}
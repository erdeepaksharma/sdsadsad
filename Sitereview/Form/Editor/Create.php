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
class Sitereview_Form_Editor_Create extends Engine_Form {

  protected $_profileTypeReview;

  public function getProfileTypeReview() {
    return $this->_profileTypeReview;
  }

  public function setProfileTypeReview($profileTypeReview) {
    $this->_profileTypeReview = $profileTypeReview;
    return $this;
  }

  public function init() {

    //GET VIEWER INFO
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $listing_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id');
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    $sitereview_title = "<b>" . $sitereview->title . "</b>";
    $this->loadDefaultDecorators();

    $this->setTitle('Write an Editor Review')
            ->setDescription(sprintf(Zend_Registry::get('Zend_Translate')->_("Give your ratings and opinion for %s below:"), $sitereview_title))
            ->setAttrib('name', 'sitereview_create')
            ->setAttrib('id', 'sitereview_create')
            ->getDecorator('Description')->setOption('escape', false);

    $this->addElement('Textarea', 'pros', array(
        'label' => 'The Good',
        'allowEmpty' => false,
        'attribs' => array('rows' => 3),
        'maxlength' => 500,
        'required' => true,
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    ));

    $this->addElement('Textarea', 'cons', array(
        'label' => 'The Bad',
        'allowEmpty' => false,
        'attribs' => array('rows' => 3),
        'maxlength' => 500,
        'required' => true,
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    ));

    $this->addElement('Textarea', 'title', array(
        'label' => 'The Bottom Line',
        'allowEmpty' => false,
        'attribs' => array('rows' => 3),
        'maxlength' => 500,
        'required' => true,
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    ));

    $profileTypeReview = $this->getProfileTypeReview();
    if (!empty($profileTypeReview)) {

      if (!$this->_item) {
        $customFields = new Sitereview_Form_Custom_Standard(array(
                    'item' => 'sitereview_review',
                    'topLevelId' => 1,
                    'topLevelValue' => $profileTypeReview,
                    'decorators' => array(
                        'FormElements'
                        )));
      } else {
        $customFields = new Sitereview_Form_Custom_Standard(array(
                    'item' => $this->getItem(),
                    'topLevelId' => 1,
                    'topLevelValue' => $profileTypeReview,
                    'decorators' => array(
                        'FormElements'
                        )));
      }

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
    }

    $this->addElement('Textarea', 'body', array(
        'label' => 'Conclusion',
        'allowEmpty' => true,
        'required' => false,
        'required' => true,
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    
    ));

    if ($this->_item && $this->_item->status == 1) {
      $this->addElement('Textarea', 'update_reason', array(
          'label' => 'Reason Of Updation',
          'allowEmpty' => false,
          'attribs' => array('rows' => 3),
          'required' => true,
         'required' => true,
         'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
    
      ));
    }

    $this->addElement('Select', 'status', array(
        'label' => 'Status',
        'multiOptions' => array("1" => "Published", "0" => "Saved As Draft"),
        'description' => 'If this entry is published, it cannot be switched back to draft mode.'
    ));
    $this->status->getDecorator('Description')->setOption('placement', 'append');

    $this->addElement('Button', 'submit', array(
        'label' => 'Submit',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $listing_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', null);

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => Engine_Api::_()->getItem('sitereview_listing', $listing_id)->getHref(),
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addDisplayGroup(array(
        'submit',
        'cancel',
            ), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper'
        ),
    ));
  }

}

<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Global.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Review_Global extends Engine_Form {

  public function init() {

    $this->setTitle('Review Settings')
            ->setDescription('Reviews & ratings are an extremely useful feature that enables you to gather refined ratings, reviews and feedback for the Listings in your community. Below, you can highly configure the settings for reviews & ratings on your site.');

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();

    if ($listingTypeCount > 1) {
      $editorreviewDesc = 'Yes, allow editors to edit all "Editor Reviews" posted in all the listing types of which they are Editors.';
    } else {
      $editorreviewDesc = 'Yes, allow editors to edit all "Editor Reviews".';
    }

    $this->addElement('Radio', 'sitereview_editorreview', array(
        'label' => 'Editing Editor Reviews',
        'description' => 'Do you want to let editors edit all "Editor Reviews"?',
        'multiOptions' => array(
            1 => $editorreviewDesc,
            0 => 'No, editors can only edit their own "Editor Reviews".',
        ),
        'value' => $settings->getSetting('sitereview.editorreview', 0),
    ));

    $this->addElement('Radio', 'sitereview_proscons', array(
        'label' => 'Pros and Cons in User Reviews',
        'description' => 'Do you want Pros and Cons fields in Reviews? (If enabled, reviewers will be able to enter Pros and Cons for the Listings that they review, and the same will be shown in their reviews.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sitereview.proscons', 1),
        'onclick' => 'prosconsInReviews(this.value)',
        'allowEmpty' => true,
        'required' => false,
    ));

    $this->addElement('Radio', 'sitereview_proncons', array(
        'label' => "Required Pros and Cons",
        'description' => 'Do you want to make Pros and Cons fields to be required when reviewers review listings on your site?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sitereview.proncons', 1),
    ));

    $this->addElement('Text', 'sitereview_limit_proscons', array(
        'label' => 'Pros and Cons Character Limit',
        'description' => 'What character limit should be applied to the Pros and Cons fields? (Enter 0 for no character limitation.)',
        'value' => $settings->getSetting('sitereview.limit.proscons', 500),
        'allowEmpty' => false,
        'required' => true,
    ));

    $this->addElement('Radio', 'sitereview_recommend', array(
        'label' => 'Recommended in Reviews',
        'description' => 'Do you want Recommended field in Reviews? (If enabled, reviewers will be able to select if they would recommend that Listing to a friend, and the same will be shown in their review.)',
        'value' => $settings->getSetting('sitereview.recommend', 1),
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'allowEmpty' => true,
        'required' => false,
    ));

    $this->addElement('Radio', 'sitereview_summary', array(
        'label' => 'Required Summary',
        'description' => 'Do you want to make Summary field to be required when reviewers review listings on your site?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sitereview.summary', 1),
    ));


    $this->addElement('Radio', 'sitereview_report', array(
        'label' => 'Report',
        'description' => 'Allow logged-in users to report reviews as inappropriate.',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sitereview.report', 1),
        'allowEmpty' => true,
        'required' => false,
    ));

    $this->addElement('Radio', 'sitereview_share', array(
        'label' => 'Share',
        'description' => 'Allow logged-in users to share reviews in their activity feeds.',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sitereview.share', 1),
        'allowEmpty' => true,
        'required' => false,
    ));

    $this->addElement('Radio', 'sitereview_email', array(
        'label' => 'Email',
        'description' => 'Allow logged-in users to email the review content.',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sitereview.email', 1),
        'allowEmpty' => true,
        'required' => false,
    ));

    $this->addElement('Radio', 'sitereview_captcha', array(
        'label' => 'Enable Captcha',
        'description' => 'Do you want to enable captcha when visitors review listings on your site?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sitereview.captcha', 1),
    ));

    $this->addElement('Textarea', 'sitereview_contact', array(
        'label' => 'Email Addresses',
        'description' => 'Enter the email addresses on which notification emails will be sent when visitors of your site review listings on your site. From Member Level Settings, you can choose if visitors should be able to review listings. (Note: You can add multiple addresses with commas.)',
        'value' => $settings->getSetting('sitereview.contact', $settings->getSetting('core.mail.from', 'email@domain.com')),
        'allowEmpty' => false,
        'required' => true,
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }

}

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
class Sitereview_Form_Review_SitemobileCreate extends Engine_Form {

    public $_error = array();
    protected $_item;
    protected $_profileTypeReview;

    public function getProfileTypeReview() {
        return $this->_profileTypeReview;
    }

    public function setProfileTypeReview($profileTypeReview) {
        $this->_profileTypeReview = $profileTypeReview;
        return $this;
    }

    public function getItem() {
        return $this->_item;
    }

    public function setItem($item) {
        $this->_item = $item;
        return $this;
    }

    public function init() {

        $coreApi = Engine_Api::_()->getApi('settings', 'core');

        //GET WIDGET PARAMETERS
        $sitereview_proscons = $coreApi->getSetting('sitereview.proscons', 1);
        $sitereview_limit_proscons = $coreApi->getSetting('sitereview.limit.proscons', 500);
        $sitereview_recommend = $coreApi->getSetting('sitereview.recommend', 1);

        //GET DECORATORS
        $this->loadDefaultDecorators();

        //GET VIEWER INFO
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //GET LISTING ID
        $getItemListing = $this->getItem();
        $listingtype_id = $getItemListing->listingtype_id;
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
        $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
        $listing_singular_uc = ucfirst($listingtypeArray->title_singular);

        $listing_title = "<b>" . $getItemListing->title . "</b>";

        //IF NOT HAS POSTED THEN THEN SET FORM
        $this->setTitle('Write a Review')
                ->setDescription(sprintf(Zend_Registry::get('Zend_Translate')->_("Give your ratings and opinion for %s below:"), $listing_title))
                ->setAttrib('name', 'sitereview_create')
                ->setAttrib('id', 'sitereview_create')
                ->getDecorator('Description')->setOption('escape', false);
        $this->setAttrib('class', 'seaocore_form_comment');
        if (empty($viewer_id)) {
            $this->addElement('Text', 'anonymous_name', array(
                'label' => 'Name',
                'allowEmpty' => false,
                'required' => true,
                'filters' => array(
                    'StripTags',
                    new Engine_Filter_Censor(),
                    new Engine_Filter_StringLength(array('max' => '63')),
            )));

            $this->addElement('Text', 'anonymous_email', array(
                'label' => 'Email',
                'required' => true,
                'allowEmpty' => false,
                'validators' => array(
                    array('NotEmpty', true),
                    array('EmailAddress', true)),
                'filters' => array(
                            'StripTags',
                         new Engine_Filter_Censor(),
                         ),
            ));
            $this->anonymous_email->getValidator('EmailAddress')->getHostnameValidator()->setValidateTld(false);
        }

        if ($sitereview_proscons) {
            if ($sitereview_limit_proscons) {
                $this->addElement('Textarea', 'pros', array(
                    'label' => 'Pros',
                    'rows' => 2,
                    'description' => Zend_Registry::get('Zend_Translate')->_("What do you like about this $listing_singular_uc?"),
                    'allowEmpty' => !Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proncons', 1),
                    'maxlength' => $sitereview_limit_proscons,
                    'required' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proncons', 1),
                    'filters' => array(
                        'StripTags',
                        new Engine_Filter_Censor(),
                        new Engine_Filter_HtmlSpecialChars(),
                        new Engine_Filter_EnableLinks(),
                    ),
                ));
            } else {
                $this->addElement('Textarea', 'pros', array(
                    'label' => 'Pros',
                    'rows' => 2,
                    'description' => Zend_Registry::get('Zend_Translate')->_("What do you like about this $listing_singular_uc?"),
                    'allowEmpty' => !Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proncons', 1),
                    'required' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proncons', 1),
                    'filters' => array(
                        'StripTags',
                        new Engine_Filter_Censor(),
                        new Engine_Filter_HtmlSpecialChars(),
                        new Engine_Filter_EnableLinks(),
                    ),
                ));
            }
            $this->pros->getDecorator('Description')->setOptions(array('placement' => 'PREPAND', 'escape' => false));

            if ($sitereview_limit_proscons) {
                $this->addElement('Textarea', 'cons', array(
                    'label' => 'Cons',
                    'rows' => 2,
                    'description' => Zend_Registry::get('Zend_Translate')->_("What do you dislike about this $listing_singular_uc?"),
                    'allowEmpty' => !Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proncons', 1),
                    'maxlength' => $sitereview_limit_proscons,
                    'required' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proncons', 1),
                    'filters' => array(
                        'StripTags',
                        new Engine_Filter_Censor(),
                        new Engine_Filter_HtmlSpecialChars(),
                        new Engine_Filter_EnableLinks(),
                    ),
                ));
            } else {
                $this->addElement('Textarea', 'cons', array(
                    'label' => 'Cons',
                    'rows' => 2,
                    'description' => Zend_Registry::get('Zend_Translate')->_("What do you dislike about this $listing_singular_uc?"),
                    'allowEmpty' => !Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proncons', 1),
                    'required' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proncons', 1),
                    'filters' => array(
                        'StripTags',
                        new Engine_Filter_Censor(),
                        new Engine_Filter_HtmlSpecialChars(),
                        new Engine_Filter_EnableLinks(),
                    ),
                ));
            }
            $this->cons->getDecorator('Description')->setOptions(array('placement' => 'PREPAND', 'escape' => false));
        }

        $this->addElement('Textarea', 'title', array(
            'label' => 'One-line summary',
            'rows' => 1,
            'allowEmpty' => false,
            'maxlength' => 63,
            'required' => true,
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_HtmlSpecialChars(),
                new Engine_Filter_EnableLinks(),
            ),
        ));

        $profileTypeReview = $this->getProfileTypeReview();
        if (!empty($profileTypeReview)) {
            $customFields = new Sitereview_Form_Custom_Standard(array(
                'item' => 'sitereview_review',
                'topLevelId' => 1,
                'topLevelValue' => $profileTypeReview,
                'decorators' => array(
                    'FormElements'
            )));

            $customFields->removeElement('submit');

            $this->addSubForms(array(
                'fields' => $customFields
            ));
        }

        $this->addElement('Textarea', 'body', array(
            'label' => 'Summary',
            'rows' => 3,
            'allowEmpty' => !Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.summary', 1),
            'required' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.summary', 1),
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_HtmlSpecialChars(),
                new Engine_Filter_EnableLinks(),
            ),
        ));

        if ($sitereview_recommend) {
            $this->addElement('Radio', 'recommend', array(
                'label' => 'Recommended',
                'description' => sprintf(Zend_Registry::get('Zend_Translate')->_("Would you recommend %s to a friend?"), $listing_title),
                'multiOptions' => array(
                    1 => 'Yes',
                    0 => 'No'
                ),
                'value' => 1
            ));
            $this->recommend->getDecorator('Description')->setOption('escape', false);
        }

        if (empty($viewer_id) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.captcha', 1)) {
            if (Engine_Api::_()->hasModuleBootstrap('siterecaptcha')) {
                Zend_Registry::get('Zend_View')->recaptcha($this);
            } else {
                $this->addElement('captcha', 'captcha', array(
                    'description' => 'Please type the characters you see in the image.',
                    'captcha' => 'image',
                    'required' => true,
                    'captchaOptions' => array(
                        'wordLen' => 6,
                        'fontSize' => '30',
                        'timeout' => '30000',
                        'imgDir' => APPLICATION_PATH . '/public/temporary/',
                        'imgUrl' => $this->getView()->baseUrl() . '/public/temporary',
                        'font' => APPLICATION_PATH . '/application/modules/Core/externals/fonts/arial.ttf'
                )));
            }
        }

        $this->addElement('Button', 'submit', array(
            'label' => 'Submit',
            'order' => 10,
            'type' => 'submit',
            'ignore' => true
        ));
    }

}

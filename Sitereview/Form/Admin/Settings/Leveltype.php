<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Leveltype.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Settings_Leveltype extends Authorization_Form_Admin_Level_Abstract {

    public function init() {

        $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', 1);
        $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingtype_id);
        $isEnabledPackage = Engine_Api::_()->sitereview()->hasPackageEnable($listingtype_id);

        $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
        $listingTypes = $listingTypeTable->getListingTypesArray(0, 0);
        $listingTypeCount = $listingTypeTable->getListingTypeCount();

        if ($listingTypeCount > 1) {
            $this->addElement('Select', 'listingtype_id', array(
                'label' => 'Listing Type',
                'description' => '',
                'onchange' => 'javascript:fetchListingTypeSettings(this.value);',
                'multiOptions' => $listingTypes,
            ));
        }

        parent::init();

        $this->setTitle('Listing Type - Member Level Settings')
                ->setDescription("These settings are applied on a per member level basis. Start by selecting the member level you want to modify, then adjust the settings for that level below.");

        $view_element = "view_listtype_$listingtype_id";
        $this->addElement('Radio', "$view_element", array(
            'label' => 'Allow Viewing of Listings?',
            'description' => 'Do you want to let members view listings? If set to no, some other settings on this page may not apply.',
            'multiOptions' => array(
                2 => 'Yes, allow viewing of all listings, even private ones.',
                1 => 'Yes, allow viewing of listings.',
                0 => 'No, do not allow listings to be viewed.',
            ),
            'value' => ( $this->isModerator() ? 2 : 1 ),
        ));
        if (!$this->isModerator()) {
            unset($this->$view_element->options[2]);
        }

        if (!$this->isPublic()) {

            $create_element = "create_listtype_$listingtype_id";
            $this->addElement('Radio', "$create_element", array(
                'label' => 'Allow Creation of Listings?',
                'description' => 'Do you want to let members create listings? If set to no, some other settings on this page may not apply. This is useful if you want members to be able to view listings, but only want certain levels to be able to create listings.',
                'multiOptions' => array(
                    1 => 'Yes, allow creation of listings.',
                    0 => 'No, do not allow listings to be created.'
                ),
                'value' => 1,
            ));

            $edit_element = "edit_listtype_$listingtype_id";
            $this->addElement('Radio', "$edit_element", array(
                'label' => 'Allow Editing of Listings?',
                'description' => 'Do you want to let members edit listings? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    2 => 'Yes, allow members to edit all listings.',
                    1 => 'Yes, allow members to edit their own listings.',
                    0 => 'No, do not allow members to edit their listings.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if (!$this->isModerator()) {
                unset($this->$edit_element->options[2]);
            }

            $delete_element = "delete_listtype_$listingtype_id";
            $this->addElement('Radio', "$delete_element", array(
                'label' => 'Allow Deletion of Listings?',
                'description' => 'Do you want to let members delete listings? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    2 => 'Yes, allow members to delete all listings.',
                    1 => 'Yes, allow members to delete their own listings.',
                    0 => 'No, do not allow members to delete their listings.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if (!$this->isModerator()) {
                unset($this->$delete_element->options[2]);
            }

            $comment_element = "comment_listtype_$listingtype_id";
            $this->addElement('Radio', "$comment_element", array(
                'label' => 'Allow Commenting on Listings?',
                'description' => 'Do you want to let members of this level comment on listings?',
                'multiOptions' => array(
                    2 => 'Yes, allow members to comment on all listings, including private ones.',
                    1 => 'Yes, allow members to comment on listings.',
                    0 => 'No, do not allow members to comment on listings.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if (!$this->isModerator()) {
                unset($this->$comment_element->options[2]);
            }

            $style_element = "style_listtype_$listingtype_id";
            $this->addElement('Radio', "$style_element", array(
                'label' => 'Allow Custom CSS Styles?',
                'description' => 'If you enable this feature, your members will be able to customize the colors and fonts of their listings by altering their CSS styles.',
                'multiOptions' => array(
                    1 => 'Yes, enable custom CSS styles.',
                    0 => 'No, disable custom CSS styles.',
                ),
                'value' => 1,
            ));

            if (!empty($listingType->overview)) {
                $overview_element = "overview_listtype_$listingtype_id";
                $this->addElement('Radio', "$overview_element", array(
                    'label' => 'Allow Overview?',
                    'description' => 'Do you want to let members enter rich Overview for their listings?',
                    'multiOptions' => array(
                        1 => 'Yes',
                        0 => 'No'
                    ),
                    'value' => 1,
                ));
            }

            if (!empty($listingType->allow_apply)) {
                $apply_element = "apply_listtype_$listingtype_id";
                $this->addElement('Radio', "$apply_element", array(
                    'label' => 'Allow Apply Now?',
                    'description' => 'Do you want to let members to apply now for their listings?',
                    'multiOptions' => array(
                        1 => 'Yes',
                        0 => 'No'
                    ),
                    'value' => 1,
                ));
            }

            $this->addElement('Radio', "contact_listtype_$listingtype_id", array(
                'label' => 'Allow Contact Details',
                'description' => 'Do you want to let members enter contact details for their listings?',
                'multiOptions' => array(
                    1 => 'Yes',
                    0 => 'No'
                ),
                'value' => 1,
            ));

            if (!empty($listingType->where_to_buy)) {
                $overview_element = "where_to_buy_listtype_$listingtype_id";
                $this->addElement('Radio', "$overview_element", array(
                    'label' => "Allow Where to Buy",
                    'description' => "Do you want to let members enter where to buy details for their listings?",
                    'multiOptions' => array(
                        1 => 'Yes',
                        0 => 'No'
                    ),
                    'value' => 1,
                ));
            }

            $this->addElement('Radio', "metakeyword_listtype_$listingtype_id", array(
                'label' => 'Meta Tags / Keywords',
                'description' => 'Do you want to let members enter meta tags / keywords for their listings?',
                'multiOptions' => array(
                    1 => 'Yes',
                    0 => 'No'
                ),
                'value' => 1,
            ));

            $auth_view_element = "auth_view_listtype_$listingtype_id";
            $this->addElement('MultiCheckbox', "$auth_view_element", array(
                'label' => 'Listing View Options',
                'description' => 'Your members can choose from any of the options checked below when they decide who can see their listings. These options appear on your members "Add Listings and "Edit Entry" pages. If you do not check any options, everyone will be allowed to view listings.',
                'multiOptions' => array(
                    'everyone' => 'Everyone',
                    'registered' => 'All Registered Members',
                    'owner_network' => 'Friends and Networks',
                    'owner_member_member' => 'Friends of Friends',
                    'owner_member' => 'Friends Only',
                    'owner' => 'Just Me'
                ),
                'value' => array('everyone', 'registered', 'owner_network', 'owner_member_member', 'owner_member', 'owner')
            ));

            $auth_comment_element = "auth_comment_listtype_$listingtype_id";
            $this->addElement('MultiCheckbox', "$auth_comment_element", array(
                'label' => 'Listing Comment Options',
                'description' => 'Your members can choose from any of the options checked below when they decide who can post comments on their listings. If you do not check any options, everyone will be allowed to post comments on listings.',
                'multiOptions' => array(
                    'registered' => 'All Registered Members',
                    'owner_network' => 'Friends and Networks',
                    'owner_member_member' => 'Friends of Friends',
                    'owner_member' => 'Friends Only',
                    'owner' => 'Just Me'
                ),
                'value' => array('registered', 'owner_network', 'owner_member_member', 'owner_member', 'owner')
            ));

            $topic_element = "topic_listtype_$listingtype_id";
            $this->addElement('Radio', "$topic_element", array(
                'label' => 'Allow Posting of Discusstion Topics?',
                'description' => 'Do you want to let members post discussion topics to listings?',
                'multiOptions' => array(
                    2 => 'Yes, allow discussion topic posting to listings, including private ones.',
                    1 => 'Yes, allow discussion topic posting to listings.',
                    0 => 'No, do not allow discussion topic posting.'
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if (!$this->isModerator()) {
                unset($this->$topic_element->options[2]);
            }
            $auth_topic_element = "auth_topic_listtype_$listingtype_id";
            $this->addElement('MultiCheckbox', "$auth_topic_element", array(
                'label' => 'Discussion Topic Posting Options',
                'description' => 'Your members can choose from any of the options checked below when they decide who can post the discussion topics in their listings. If you do not check any options, everyone will be allowed to post discussion topics to the listings of this member level.',
                'multiOptions' => array(
                    'registered' => 'All Registered Members',
                    'owner_network' => 'Friends and Networks',
                    'owner_member_member' => 'Friends of Friends',
                    'owner_member' => 'Friends Only',
                    'owner' => 'Just Me'
                ),
                'value' => array('registered', 'owner_network', 'owner_member_member', 'owner_member', 'owner')
            ));

            $photo_element = "photo_listtype_$listingtype_id";
            $this->addElement('Radio', "$photo_element", array(
                'label' => 'Allow Uploading of Photos?',
                'description' => 'Do you want to let members upload Photos to listings?',
                'multiOptions' => array(
                    2 => 'Yes, allow photo uploading to listings, including private ones.',
                    1 => 'Yes, allow photo uploading to listings.',
                    0 => 'No, do not allow photo uploading.'
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));

            if (!$this->isModerator()) {
                unset($this->$photo_element->options[2]);
            }
            $auth_photo_element = "auth_photo_listtype_$listingtype_id";
            $this->addElement('MultiCheckbox', "$auth_photo_element", array(
                'label' => 'Photo Upload Options',
                'description' => 'Your members can choose from any of the options checked below when they decide who can upload the photos in their listings. If you do not check any options, everyone will be allowed to upload photos to the listings of this member level.',
                'multiOptions' => array(
                    'registered' => 'All Registered Members',
                    'owner_network' => 'Friends and Networks',
                    'owner_member_member' => 'Friends of Friends',
                    'owner_member' => 'Friends Only',
                    'owner' => 'Just Me'
                ),
                'value' => array('registered', 'owner_network', 'owner_member_member', 'owner_member', 'owner')
            ));

            if (Engine_Api::_()->sitereview()->enableVideoPlugin()) {
                $video_element = "video_listtype_$listingtype_id";
                $this->addElement('Radio', "$video_element", array(
                    'label' => 'Allow Uploading of Videos?',
                    'description' => 'Do you want to let members upload Videos to listings?',
                    'multiOptions' => array(
                        2 => 'Yes, allow video uploading to listings, including private ones.',
                        1 => 'Yes, allow video uploading to listings.',
                        0 => 'No, do not allow video uploading.',
                    ),
                    'value' => ( $this->isModerator() ? 2 : 1 ),
                ));
                if (!$this->isModerator()) {
                    unset($this->$video_element->options[2]);
                }

                $auth_video_element = "auth_video_listtype_$listingtype_id";
                $this->addElement('MultiCheckbox', "$auth_video_element", array(
                    'label' => 'Video Upload Options',
                    'description' => 'Your members can choose from any of the options checked below when they decide who can upload the videos in their listings. If you do not check any options, everyone will be allowed to upload video.',
                    'multiOptions' => array(
                        'registered' => 'All Registered Members',
                        'owner_network' => 'Friends and Networks',
                        'owner_member_member' => 'Friends of Friends',
                        'owner_member' => 'Friends Only',
                        'owner' => 'Just Me'
                    ),
                    'value' => array('registered', 'owner_network', 'owner_member_member', 'owner_member', 'owner')
                ));
            }

            if ((Engine_Api::_()->hasModuleBootstrap('siteevent') && Engine_Api::_()->getDbtable('modules', 'siteevent')->getIntegratedModules(array('enabled' => 1, 'item_type' => 'sitereview_listing_' . $listingtype_id, 'item_module' => 'sitereview')))) {
                $event_element = "event_listtype_$listingtype_id";
                $this->addElement('Radio', "$event_element", array(
                    'label' => 'Events in Listings?',
                    'description' => 'Do you want Events to be available to Listings created by members of this level? This setting will also apply to ability of users of this level to create Events in Listings.',
                    'multiOptions' => array(
                        1 => 'Yes',
                        0 => 'No',
                    ),
                    'value' => 1,
                ));

                $auth_event_element = "auth_event_listtype_$listingtype_id";
                $this->addElement('MultiCheckbox', "$auth_event_element", array(
                    'label' => 'Event Creation Options',
                    'description' => 'Your users can choose from any of the options checked below when they decide who can create the events in their listing. If you do not check any options, everyone will be allowed to create.',
                    'multiOptions' => array(
                        'registered' => 'All Registered Members',
                        'owner_network' => 'Friends and Networks',
                        'owner_member_member' => 'Friends of Friends',
                        'owner_member' => 'Friends Only',
                        'owner' => 'Just Me'
                    ),
                    'value' => array('registered', 'owner_network', 'owner_member_member', 'owner_member', 'owner')
                ));
            }

            //START SITECROWDFUNDING PLUGIN WORK
            $sitecrowdfundingEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitecrowdfunding');
            if ($sitecrowdfundingEnabled && (Engine_Api::_()->hasModuleBootstrap('sitecrowdfundingintegration') && Engine_Api::_()->getDbtable('modules', 'sitecrowdfunding')->getIntegratedModules(array('enabled' => 1, 'item_type' => 'sitereview_listing_' . $listingtype_id, 'item_module' => 'sitereview')))) {
                $sprcreate_element = "sprcreate_listtype_$listingtype_id";
                $this->addElement('Radio', "$sprcreate_element", array(
                    'label' => 'Allow Creation of Projects?',
                    'description' => 'Do you want to allow members of this level to be able to create projects in Listings?',
                    'multiOptions' => array(
                        1 => 'Yes',
                        0 => 'No',
                    ),
                    'value' => 1,
                ));
                $auth_sprcreate_element = "auth_sprcreate_listtype_$listingtype_id";
                $this->addElement('MultiCheckbox', "$auth_sprcreate_element", array(
                    'label' => 'Project Creation Options',
                    'description' => 'Your users can choose from any of the options checked below when they decide who can create projects in their listing. If you do not check any options, everyone will be allowed to create projects.',
                    'multiOptions' => array(
                        'registered' => 'All Registered Members',
                        'owner_network' => 'Friends and Networks',
                        'owner_member_member' => 'Friends of Friends',
                        'owner_member' => 'Friends Only',
                        'owner' => 'Just Me',
                    )
                ));
            }
            //END SITECROWDFUNDING PLUGIN WORK

            if (empty($isEnabledPackage)) {
                $approved_element = "approved_listtype_$listingtype_id";
                $this->addElement('Radio', "$approved_element", array(
                    'label' => 'Listing Approval Moderation',
                    'description' => 'Do you want new Listing to be automatically approved?',
                    'multiOptions' => array(
                        1 => 'Yes, automatically approve Listing.',
                        0 => 'No, site admin approval will be required for all Listing.'
                    ),
                    'value' => 1,
                ));

                $featured_element = "featured_listtype_$listingtype_id";
                $this->addElement('Radio', "$featured_element", array(
                    'label' => 'Listing Featured Moderation',
                    'description' => 'Do you want new Listing to be automatically made featured?',
                    'multiOptions' => array(
                        1 => 'Yes, automatically make Listing featured.',
                        0 => 'No, site admin will be making Listing featured.'
                    ),
                    'value' => 1,
                ));

                $sponsored_element = "sponsored_listtype_$listingtype_id";
                $this->addElement('Radio', "$sponsored_element", array(
                    'label' => 'Listing Sponsored Moderation',
                    'description' => 'Do you want new Listing to be automatically made Sponsored?',
                    'multiOptions' => array(
                        1 => 'Yes, automatically make Listing Sponsored.',
                        0 => 'No, site admin will be making Listing Sponsored.'
                    ),
                    'value' => 1,
                ));
            }
        }

        if ($listingTypeCount == 1) {
            $this->addElement('Radio', "wishlist", array(
                'label' => 'Allow Viewing of Wishlists?',
                'description' => 'Do you want to let members view Wishlists? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    2 => 'Yes, allow members to view all wishlists, even private ones.',
                    1 => 'Yes, allow viewing of wishlists.',
                    0 => 'No, do not allow wishlists to be viewed.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));

            if (!$this->isModerator()) {
                unset($this->wishlist->options[2]);
            }

            if (!$this->isPublic()) {

                $this->addElement('MultiCheckbox', "auth_wishlist", array(
                    'label' => 'Wishlists View Privacy',
                    'description' => 'Your members can choose from any of the options checked below when they decide who can see their wishlists. These options appear on your members\' "Create New Wishlists" and "Edit Wishlists" pages. If you do not check any options, everyone will be allowed to view wishlists.',
                    'multiOptions' => array(
                        'everyone' => 'Everyone',
                        'registered' => 'All Registered Members',
                        'owner_network' => 'Friends and Networks',
                        'owner_member_member' => 'Friends of Friends',
                        'owner_member' => 'Friends Only',
                        'owner' => 'Just Me'
                    )
                ));
            }
        }

        $review_create_element = "review_create_listtype_$listingtype_id";
        $this->addElement('Radio', "$review_create_element", array(
            'label' => 'Allow Writing of Reviews',
            'description' => 'Do you want to let members write reviews for listings?',
            'multiOptions' => array(
                1 => 'Yes, allow members to write reviews.',
                0 => 'No, do not allow members to write reviews.',
            ),
            'value' => 1,
        ));

        $claim_element = "claim_listtype_$listingtype_id";
        $this->addElement('Radio', "$claim_element", array(
            'label' => 'Claim Listings',
            'description' => 'Do you want members of this level to be able to claim listings? (This will also depend on other settings for claiming like in listing type, manage claims, setting while creation of listing, etc.)',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => 1,
        ));

        if (!$this->isPublic()) {

            $review_reply_element = "review_reply_listtype_$listingtype_id";
            $this->addElement('Radio', "$review_reply_element", array(
                'label' => 'Allow Commenting on Reviews?',
                'description' => 'Do you want to let members to comment on Reviews?',
                'multiOptions' => array(
                    1 => 'Yes, allow members to comment on reviews.',
                    0 => 'No, do not allow members to comment on reviews.',
                ),
                'value' => 1,
            ));
            if (!$this->isModerator()) {
                unset($this->$review_reply_element->options[2]);
            }

            $review_update_element = "review_update_listtype_$listingtype_id";
            $this->addElement('Radio', "$review_update_element", array(
                'label' => 'Allow Updating of Reviews?',
                'description' => 'Do you want to let members to update their reviews?',
                'multiOptions' => array(
                    1 => 'Yes, allow members to update their own reviews.',
                    0 => 'No, do not allow members to update their reviews.',
                ),
                'value' => 1,
            ));

            $review_delete_element = "review_delete_listtype_$listingtype_id";
            $this->addElement('Radio', "$review_delete_element", array(
                'label' => 'Allow Deletion of Reviews?',
                'description' => 'Do you want to let members delete reviews?',
                'multiOptions' => array(
                    2 => 'Yes, allow members to delete all reviews.',
                    1 => 'Yes, allow members to delete their own reviews.',
                    0 => 'No, do not allow members to delete their reviews.',
                ),
                'value' => ( $this->isModerator() ? 2 : 0 ),
            ));
            if (!$this->isModerator()) {
                unset($this->$review_delete_element->options[2]);
            }

            $max_element = "max_listtype_$listingtype_id";
            $this->addElement('Text', "$max_element", array(
                'label' => 'Maximum Allowed Listings',
                'description' => 'Enter the maximum number of allowed listings. This field must contain an integer, use zero for unlimited.',
                'validators' => array(
                    array('Int', true),
                    new Engine_Validate_AtLeast(0),
                ),
            ));
        }
    }

}

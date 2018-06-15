<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteapi
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    Core.php 2015-09-17 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Api_Siteapi_Core extends Core_Api_Abstract {
    /*
     * Store all profile types
     */

    protected $_profileFieldsArray;

    /*
     * Flag variable of create
     */
    protected $_create;

    /*
     * Flag variable of search
     */
    protected $_validateSearchProfileFields;

    /**
     * Get the listing create form.
     * 
     * @param listingtype_id, listing, profileType
     * @return array
     */
    public function getListingCreateForm($listingtype_id, $item=array(), $profileType=null) {
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if ($item) {
                $pakage_id = $item->package_id;
            } else {
                $pakage_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', null);
            }
        }

        $viewer = Engine_Api::_()->user()->getViewer();

        if ($listingtype_id != -1 && !empty($listingtype_id))
            $listingTypeObj = $this->setListingTypeInRegistry($listingtype_id);

        $this->_create = 1;
        $createForm = array();
        $listing_singular_uc = @ucfirst($listingTypeObj->title_singular);
        $listing_singular_lc = @strtolower($listingTypeObj->title_singular);
        $listing_singular_upper = @strtoupper($listingTypeObj->title_singular);

        // Get profile fields array
        $profileFields = $this->_getProfileTypes();
        if (!empty($profileFields)) {
            $this->_profileFieldsArray = $profileFields;
        }

        $createFormFields = $this->_getProfileFields();

        $createForm[] = array(
            'type' => 'Text',
            'name' => 'title',
            'label' => $this->translate($listing_singular_uc . " Title"),
            'hasValidator' => 'true'
        );

        if (isset($listingTypeObj->show_tag) && !empty($listingTypeObj->show_tag)) {
            $createForm[] = array(
                'type' => 'Text',
                'name' => 'tags',
                'label' => $this->translate($listing_singular_upper . '_TAG_(Keywords)'),
                'description' => $this->translate('SEPARATE_' . $listing_singular_upper . '_TAGS_WITH_COMMAS'),
            );
        }

        $overview_checkinpackage = true;
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($pakage_id, "overview"))
                $overview_checkinpackage = false;
        }

        $allowOverview = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "overview_listtype_$listingTypeObj->listingtype_id");
        $allowEdit = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "edit_listtype_$listingTypeObj->listingtype_id");

        if ($listingTypeObj->overview && $listingTypeObj->overview_creation && $allowOverview && $allowEdit) {
            $label = $this->translate('Short Description');
        } else {
            $label = $this->translate('Description');
        }

        if ($listingTypeObj->body_allow) {
            if (!empty($listingTypeObj->body_required)) {
                $createForm[] = array(
                    'type' => 'Textarea',
                    'name' => 'body',
                    'label' => $label,
                    'hasValidator' => 'true'
                );
            } else {
                $createForm[] = array(
                    'type' => 'Textarea',
                    'name' => 'body',
                    'label' => $label,
                );
            }
        }
        if ($overview_checkinpackage && $listingTypeObj->overview && $listingTypeObj->overview_creation && $allowOverview && $allowEdit && !$item) {
            $createForm[] = array(
                'type' => 'Textarea',
                'name' => 'overview',
                'label' => $this->translate('DASHBOARD_' . $listing_singular_upper . '_OVERVIEW'),
            );
        }

        $categoryProfileTypeMapping = array();
        if ((!$item || (isset($item->category_id) && empty($item->category_id))) || ($item && $listingTypeObj->category_edit)) {
            $categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 0, $listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name', 'profile_type'));
            if (count($categories) != 0) {
                $categories_prepared[0] = "";
                foreach ($categories as $category) {
                    $categories_prepared[$category->category_id] = $this->translate($category->category_name);
                }
                $createForm[] = array(
                    'type' => 'Select',
                    'name' => 'category_id',
                    'label' => $this->translate('Category'),
                    'multiOptions' => $this->translate($categories_prepared),
                    'hasValidator' => 'true',
                );
                $subCategoriesObj = Engine_Api::_()->getDbTable('categories', 'sitereview')->getSubCategories($item->category_id);

                $getSubCategories[0] = "";
                foreach ($subCategoriesObj as $subcategory) {
                    $getSubCategories[$subcategory->category_id] = $subcategory->category_name;
                }

                if (isset($getSubCategories) && !empty($getSubCategories) && count($getSubCategories) > 1) {
                    $createForm[] = array(
                        'type' => 'Select',
                        'name' => 'subcategory_id',
                        'label' => $this->translate('SubCategory'),
                        'multiOptions' => $this->translate($getSubCategories),
                    );
                }
                $subsubCategoriesObj = Engine_Api::_()->getDbTable('categories', 'sitereview')->getSubCategories($item->subcategory_id);
                $getSubSubCategories[0] = "";
                foreach ($subsubCategoriesObj as $subsubcategory) {
                    $getSubSubCategories[$subsubcategory->category_id] = $subsubcategory->category_name;
                }
                if (isset($getSubSubCategories) && !empty($getSubSubCategories) && count($getSubSubCategories) > 1) {
                    $createForm[] = array(
                        'type' => 'Select',
                        'name' => 'subsubcategory_id',
                        'label' => $this->translate('3rd Level Category'),
                        'multiOptions' => $this->translate($getSubSubCategories),
                    );
                }
            }

            $categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 0, $listingtype_id, 0, 1, 0, 'category_name', 0, array('category_id', 'category_name', 'cat_order', 'profile_type'));

            if (count($categories) != 0) {
                foreach ($categories as $category) {
                    $subCategories = array();
                    $subCategoriesObj = Engine_Api::_()->getDbTable('categories', 'sitereview')->getSubCategories($category->category_id);
                    $getCategories[$category->category_id] = $category->category_name;

                    if (isset($category->profile_type) && !empty($category->profile_type))
                        $categoryProfileTypeMapping[$category->category_id] = $category->profile_type;

                    $getsubCategories = array();
                    $getsubCategories[0] = "";
                    foreach ($subCategoriesObj as $subcategory) {
                        $subsubCategoriesObj = Engine_Api::_()->getDbTable('categories', 'sitereview')->getSubCategories($subcategory->category_id, array('category_id', 'profile_type', 'cat_order', 'category_name'));

                        $subsubCategories = array();
                        $subsubCategories[0] = "";
                        foreach ($subsubCategoriesObj as $subsubcategory) {
                            $subsubCategories[$subsubcategory->category_id] = $subsubcategory->category_name;

                            if (isset($subsubcategory->profile_type) && !empty($subsubcategory->profile_type)) {
                                $categoryProfileTypeMapping[$subsubcategory->category_id] = $subsubcategory->profile_type;
                            }
                        }

                        if (isset($subsubCategories) && count($subsubCategories) > 1) {
                            $subsubCategoriesForm[$subcategory->category_id] = array(
                                'type' => 'Select',
                                'name' => 'subsubcategory_id',
                                'label' => $this->translate('3rd Level Category'),
                                'multiOptions' => $this->translate($subsubCategories),
                            );
                        }
                        $getsubCategories[$subcategory->category_id] = $subcategory->category_name;
                        if (isset($subcategory->profile_type) && !empty($subcategory->profile_type))
                            $categoryProfileTypeMapping[$subcategory->category_id] = $subcategory->profile_type;
                    }

                    if (isset($getsubCategories) && count($getsubCategories) > 1) {
                        $subcategoriesForm = array(
                            'type' => 'Select',
                            'name' => 'subcategory_id',
                            'label' => $this->translate('Sub-Category'),
                            'multiOptions' => $this->translate($getsubCategories),
                        );
                    }
                    if (isset($subcategoriesForm) && !empty($subcategoriesForm) && count($subcategoriesForm) > 0) {
                        $form[$category->category_id]['form'] = $subcategoriesForm;
                        $subcategoriesForm = array();
                    }
                    if (isset($subsubCategoriesForm) && count($subsubCategoriesForm) > 0)
                        $form[$category->category_id]['subsubcategories'] = $subsubCategoriesForm;
                    $subsubCategoriesForm = array();
                }

                $categoriesForm = array(
                    'type' => 'Select',
                    'name' => 'category_id',
                    'allowEmpty' => 0,
                    'label' => $this->translate('Category'),
                    'multiOptions' => $this->translate($getCategories),
                    'hasValidator' => 'true'
                );
            }
        }


        // Set profile fields along with create form on editing
        if (isset($item) && !empty($item) && isset($profileType) && !empty($profileType) && is_array($createFormFields)) {
            if (isset($createFormFields[$profileType]) && !empty($createFormFields[$profileType])) {
                $createForm = array_merge($createForm, $createFormFields[$profileType]);
            }
        }

        $availableLabels = array(
            'everyone' => 'Everyone',
            'registered' => 'All Registered Members',
            'owner_network' => 'Friends and Networks',
            'owner_member_member' => 'Friends of Friends',
            'owner_member' => 'Friends Only',
            'owner' => 'Just Me',
        );
        $view_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $viewer, "auth_view_listtype_$listingtype_id");
        $view_options = array_intersect_key($availableLabels, array_flip($view_options));

        $createForm[] = array(
            'type' => 'Select',
            'name' => 'auth_view',
            'label' => 'View Privacy',
            'description' => $this->translate("Who may see this $listing_singular_lc?"),
            'multiOptions' => $this->translate($view_options),
            'value' => key($view_options),
        );


        $allowed_upload = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "photo_listtype_$listingtype_id");
        if ($allowed_upload && ($listingTypeObj->photo_type == 'listing') && empty($item)) {
            $label = 'Main Photo';
            if (stristr($listingTypeObj->title_singular, 'job') || stristr($listingTypeObj->title_singular, 'job') || $item) {
                $label = 'Company Logo';
            }
            $createForm[] = array(
                'type' => 'File',
                'name' => 'photo',
                'label' => $this->translate($label),
            );
        }

        $showLocation = 1;
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($pakage_id, "map"))
                $showLocation = 1;
            else
                $showLocation = 0;
        }

        if ($listingTypeObj->location && !empty($showLocation)) {
            $createForm[] = array(
                'type' => 'Text',
                'name' => 'location',
                'label' => $this->translate('Location'),
            );
        }

        if ($listingTypeObj->price) {
//            Engine_Api::_()->getApi('Core', 'siteapi')->setLocal();
//            $localeObject = Engine_Api::_()->getApi('Core', 'siteapi')->getLocal();
//            ;
//            $currencyCode = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
            $createForm[] = array(
                'type' => 'Text',
                'name' => 'price',
                'label' => $this->translate('Price')
            );
        }

        $comment_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $viewer, "auth_comment_listtype_$listingtype_id");
        $comment_options = array_intersect_key($availableLabels, array_flip($comment_options));

        if (count($comment_options) > 1) {
            $createForm[] = array(
                'type' => 'Select',
                'name' => 'auth_comment',
                'label' => 'Comment Privacy',
                'description' => $this->translate("Who may comment on this listing?"),
                'multiOptions' => $comment_options,
                'value' => key($comment_options),
            );
        }

        $availableLabels = array(
            'registered' => 'All Registered Members',
            'owner_network' => 'Friends and Networks',
            'owner_member_member' => 'Friends of Friends',
            'owner_member' => 'Friends Only',
            'owner' => 'Just Me',
        );

        $topic_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $viewer, "auth_topic_listtype_$listingtype_id");
        $topic_options = array_intersect_key($availableLabels, array_flip($topic_options));

        if (count($topic_options) > 1) {
            $createForm[] = array(
                'type' => 'Select',
                'name' => 'auth_topic',
                'label' => $this->translate('Discussion Topic Privacy'),
                'description' => $this->translate("Who may post discussion topics for this listing?"),
                'multiOptions' => $this->translate($topic_options),
                'value' => key($topic_options),
            );
        }

        $photo_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $viewer, "auth_photo_listtype_$listingtype_id");
        $photo_options = array_intersect_key($availableLabels, array_flip($photo_options));

        $can_show_photo_list = true;
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($pakage_id, "photo")) {
                $can_show_photo_list = false;
            }
        }

        if (count($photo_options) > 1 && $can_show_photo_list) {
            $createForm[] = array(
                'type' => 'Select',
                'name' => 'auth_photo',
                'label' => 'Photo Privacy',
                'description' => $this->translate("Who may upload photos for this listing?"),
                'multiOptions' => $this->translate($photo_options),
                'value' => key($photo_options),
            );
        }

        $videoEnable = Engine_Api::_()->sitereview()->enableVideoPlugin();
        if ($videoEnable) {

            $video_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $viewer, "auth_video_listtype_$listingtype_id");
            $video_options = array_intersect_key($availableLabels, array_flip($video_options));

            $can_show_video_list = true;
            if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
                if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($pakage_id, "video")) {
                    $can_show_video_list = false;
                }
            }

            if (count($video_options) > 1 && $can_show_video_list) {
                $createForm[] = array(
                    'type' => 'Select',
                    'name' => 'auth_video',
                    'label' => $this->translate('Video Privacy'),
                    'description' => $this->translate("Who may create videos for this listing?"),
                    'multiOptions' => $this->translate($video_options),
                    'value' => key($video_options),
                );
            }
        }

        if ((Engine_Api::_()->hasModuleBootstrap('siteevent') && Engine_Api::_()->getDbtable('modules', 'siteevent')->getIntegratedModules(array('enabled' => 1, 'item_type' => 'sitereview_listing_' . $listingtype_id, 'item_module' => 'sitereview')))) {
            $event_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $viewer, "auth_event_listtype_$listingtype_id");
            $event_options = array_intersect_key($availableLabels, array_flip($event_options));

            if (count($event_options) > 1) {
                $createForm[] = array(
                    'type' => 'Select',
                    'name' => 'auth_event',
                    'label' => $this->translate('Event Privacy'),
                    'description' => $this->translate("Who may create event for this listing?"),
                    'multiOptions' => $this->translate($event_options),
                    'value' => key($event_options),
                );
            }
        }

        //NETWORK BASE PAGE VIEW PRIVACY
        if (Engine_Api::_()->sitereview()->listBaseNetworkEnable()) {
            // Make Network List
            $table = Engine_Api::_()->getDbtable('networks', 'network');
            $select = $table->select()
                    ->from($table->info('name'), array('network_id', 'title'))
                    ->order('title');
            $result = $table->fetchAll($select);

            $networksOptions = array('0' => 'Everyone');
            foreach ($result as $value) {
                $networksOptions[$value->network_id] = $value->title;
            }

            if (count($networksOptions) > 0) {
                $createForm[] = array(
                    'type' => 'Multiselect',
                    'name' => 'networks_privacy',
                    'label' => $this->translate('Networks Selection'),
                    'description' => $this->translate("Select the networks, members of which should be able to see your $listing_singular_lc. (Press Ctrl and click to select multiple networks. You can also choose to make your $listing_singular_lc viewable to everyone.)"),
//            'attribs' => array('style' => 'max-height:150px; '),
                    'multiOptions' => $this->translate($networksOptions),
                    'value' => array(0)
                );
            }
        }

        if ($listingTypeObj->expiry == 1) {
            $createForm[] = array(
                'type' => 'Radio',
                'name' => 'end_date_enable',
                'label' => $this->translate('End Listing on'),
                'multiOptions' => array("0" => "No end date.", "1" => "End $listing_singular_lc on a specific date. (Please select date by clicking on the calendar icon below.)"),
                'description' => $this->translate("When should this $listing_singular_lc end?"),
                'value' => 0,
            );

            $createForm[] = array(
                'type' => 'Date',
                'name' => 'end_date',
                'label' => $this->translate('End Date'),
            );
        }

        if ($listingTypeObj->show_status && ((empty($item) || !empty($item->draft)))) {
            $createForm[] = array(
                'type' => 'Select',
                'name' => 'draft',
                'label' => $this->translate('Status'),
                'multiOptions' => array("0" => "Published", "1" => "Saved As Draft"),
                'description' => $this->translate('If this ' . $listing_singular_lc . ' is published, it cannot be switched back to draft mode.'),
                'value' => 0
            );
        }
        if ($listingTypeObj->edit_creationdate && (!$item || ($item && (time() < strtotime($item->creation_date)) || ($item->draft) ))) {
            $createForm[] = array(
                'type' => 'Date',
                'name' => 'creation_date',
                'label' => $this->translate('Publishing Date'),
            );
        }

        if ($listingTypeObj->show_browse) {
            $createForm[] = array(
                'type' => 'Checkbox',
                'name' => 'search',
                'label' => $this->translate("Show this $listing_singular_lc on browse page and in various blocks."),
                'value' => "1",
            );
        }

        $createForm[] = array(
            'type' => 'Submit',
            'name' => 'submit',
            'label' => $this->translate('Submit'),
        );

        if (isset($createForm) && !empty($createForm))
            $responseForm['form'] = $createForm;

        if (isset($form) && !empty($form))
            $responseForm['subcategories'] = $form;

        if (is_array($createFormFields) && is_array($categoryProfileTypeMapping)) {
            foreach ($categoryProfileTypeMapping as $key => $value) {
                if (isset($createFormFields[$value]) && !empty($createFormFields[$value])) {
                    $createFormFieldsForm[$key] = $createFormFields[$value];
                }
            }
            if (isset($createFormFieldsForm) && !empty($createFormFieldsForm))
                $responseForm['fields'] = $createFormFieldsForm;
        }

        return $responseForm;
    }

    /*
     * Gets the adv search form of passed listingtype_id. 
     * 
     * @param int listingtype_id
     * @return array
     */

    public function getListingSearchForm($listingtype_id = 1, $restapilocation) {
        // Get the search form settings array respective to the listing type.
        $searchFormSettings = Engine_Api::_()->getDbTable('searchformsetting', 'seaocore')->getModuleOptions('sitereview_listtype_' . $listingtype_id);

        $listingTypeObj = $this->setListingTypeInRegistry($listingtype_id);
        $listing_plural_uc = @ucfirst($listingTypeObj->title_plural);

        $searchForm = array();
        if (!empty($searchFormSettings['search']) && !empty($searchFormSettings['search']['display'])) {
            $searchForm[] = array(
                'type' => 'Text',
                'name' => 'search',
                'label' => $this->translate('Name / Keyword'),
            );
        }

        if (!empty($searchFormSettings['location']) && !empty($searchFormSettings['location']['display']) && !empty($listingTypeObj->location)) {
            $locationDefault = Engine_Api::_()->getApi('settings', 'core')->getSetting('seaocore.locationdefault', '');
            $seaocoreLocationSpecific = Engine_Api::_()->getApi('settings', 'core')->getSetting('seaocore.locationspecific', '');
            if ($seaocoreLocationSpecific && !empty($restapilocation) && isset($restapilocation))
                $locationDefault = $restapilocation;

            $searchForm[] = array(
                'type' => 'Text',
                'name' => 'location',
                'label' => $this->translate('Location'),
                'value' => (!empty($locationDefault)) ? $locationDefault : ''
            );

            if (!empty($searchFormSettings['proximity']) && !empty($searchFormSettings['proximity']['display'])) {
                $flage = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proximity.search.kilometer', 0);
                if ($flage) {
                    $locationLable = "Within Kilometers";
                    $locationOption = array(
                        '0' => '',
                        '1' => '1 Kilometer',
                        '2' => '2 Kilometers',
                        '5' => '5 Kilometers',
                        '10' => '10 Kilometers',
                        '20' => '20 Kilometers',
                        '50' => '50 Kilometers',
                        '100' => '100 Kilometers',
                        '250' => '250 Kilometers',
                        '500' => '500 Kilometers',
                        '750' => '750 Kilometers',
                        '1000' => '1000 Kilometers',
                    );
                } else {
                    $locationLable = "Within Miles";
                    $locationOption = array(
                        '0' => '',
                        '1' => '1 Mile',
                        '2' => '2 Miles',
                        '5' => '5 Miles',
                        '10' => '10 Miles',
                        '20' => '20 Miles',
                        '50' => '50 Miles',
                        '100' => '100 Miles',
                        '250' => '250 Miles',
                        '500' => '500 Miles',
                        '750' => '750 Miles',
                        '1000' => '1000 Miles',
                    );
                }
                $searchForm[] = array(
                    'type' => 'Select',
                    'name' => 'locationmiles',
                    'label' => $this->translate($locationLable),
                    'multiOptions' => $this->translate($locationOption),
                );
            }
        }

        if (!empty($searchFormSettings['orderby']) && !empty($searchFormSettings['orderby']['display'])) {
            if ($listingTypeObj->reviews == 3 || $listingTypeObj->reviews == 2) {
                $multiOptionsOrderBy = array(
                    '' => "",
                    'creation_date' => 'Most Recent',
                    'title' => "Alphabetic",
                    'view_count' => 'Most Viewed',
                    'like_count' => "Most Liked",
                    'comment_count' => "Most Commented",
                    'review_count' => "Most Reviewed",
                    'rating_avg' => "Most Rated",
                );
            } elseif ($listingTypeObj->reviews == 1) {
                $multiOptionsOrderBy = array(
                    '' => "",
                    'creation_date' => 'Most Recent',
                    'title' => "Alphabetic",
                    'view_count' => 'Most Viewed',
                    'like_count' => "Most Liked",
                    'comment_count' => "Most Commented",
                    'rating_avg' => "Most Rated",
                );
            } else {
                $multiOptionsOrderBy = array(
                    '' => "",
                    'creation_date' => 'Most Recent',
                    'title' => "Alphabetic",
                    'view_count' => 'Most Viewed',
                    'like_count' => "Most Liked",
                    'comment_count' => "Most Commented",
                );
            }

            $searchForm[] = array(
                'type' => 'Select',
                'name' => 'orderby',
                'label' => $this->translate('Browse By'),
                'multiOptions' => $this->translate($multiOptionsOrderBy),
            );
        }

        if (!empty($searchFormSettings['closed']) && !empty($searchFormSettings['closed']['display'])) {
            $searchForm[] = array(
                'type' => 'Select',
                'name' => 'closed',
                'label' => 'Status',
                'multiOptions' => array(
                    '' => $this->translate("All $listing_plural_uc"),
                    '0' => $this->translate("Only Open $listing_plural_uc"),
                    '1' => $this->translate("Only Closed $listing_plural_uc")
                ),
            );
        }

        if (!empty($searchFormSettings['show']) && !empty($searchFormSettings['show']['display'])) {
            $show_multiOptions = array();
            $show_multiOptions["1"] = "Everyone's $listing_plural_uc";
            $show_multiOptions["2"] = "Only My Friends' $listing_plural_uc";
            $show_multiOptions["4"] = "$listing_plural_uc I Like";
            $value_default = 1;
            $enableNetwork = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.network', 0);
            if (empty($enableNetwork)) {
                $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
                $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
                $viewerNetwork = $networkMembershipTable->fetchRow(array('user_id = ?' => $viewer_id));

                if (!empty($viewerNetwork)) {
                    $show_multiOptions["3"] = 'Only My Networks';
                    $browseDefaulNetwork = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.default.show', 0);
                }
            }

            if ($listingTypeObj->expiry) {
                $show_multiOptions["only_expiry"] = "Only Expired $listing_plural_uc";
            }

            $searchForm[] = array(
                'type' => 'Select',
                'name' => 'show',
                'label' => $this->translate('Show'),
                'multiOptions' => $this->translate($show_multiOptions),
                'value' => $value_default,
            );
        }

        if (!empty($searchFormSettings['price']) && !empty($searchFormSettings['price']['display']) && $listingTypeObj->price) {
            $searchForm[] = array(
                'type' => 'Text',
                'name' => 'min_price',
                'label' => $this->translate('Min Price'),
            );

            $searchForm[] = array(
                'type' => 'Text',
                'name' => 'max_price',
                'label' => $this->translate('Max Price'),
            );
        }

        if (!empty($searchFormSettings['has_photo']) && !empty($searchFormSettings['has_photo']['display'])) {
            $searchForm[] = array(
                'type' => 'Checkbox',
                'name' => 'has_photo',
                'label' => $this->translate("Only $listing_plural_uc With Photos"),
            );
        }


        $categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 0, $listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name', 'profile_type'));
        if (count($categories) != 0) {
            $categories_prepared[0] = "";

            // Set category title in array
            foreach ($categories as $category) {
                $categories_prepared[$category->category_id] = Engine_Api::_()->getApi('Core', 'siteapi')->translate($category->category_name);
            }

            $searchForm[] = array(
                'type' => 'Select',
                'name' => 'category_id',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Category'),
                'multiOptions' => $categories_prepared,
                'hasValidator' => 'true',
            );
        }

        if (!empty($searchFormSettings['has_review']) && !empty($searchFormSettings['has_review']['display']) && $listingTypeObj->reviews) {
            if ($listingTypeObj->reviews == 3) {
                $multiOptions = array(
                    '' => '',
                    'rating_avg' => 'Any Reviews',
                    'rating_users' => 'User Reviews',
                );
            } elseif ($listingTypeObj->reviews == 2) {
                $multiOptions = array(
                    '' => '',
                    'rating_users' => 'User Reviews',
                );
            }
            $searchForm[] = array(
                'type' => 'Select',
                'name' => 'review',
                'label' => $this->translate("$listing_plural_uc Having Reviews"),
                'multiOptions' => $this->translate($multiOptions),
                'value' => '',
            );
        }

        $searchForm[] = array(
            'type' => 'Submit',
            'name' => 'done',
            'label' => $this->translate("Search"),
        );

        $responseForm['form'] = $searchForm;

        // Set the category info in response array.
        $responseForm = $this->_getCategoriesSearchForm($listingtype_id, $responseForm);

        $searchFormFields = $this->getSearchProfileFields();

        if (is_array($searchFormFields) && is_array($categoryProfileTypeMapping)) {
            foreach ($categoryProfileTypeMapping as $key => $value) {
                if (isset($searchFormFields[$value]) && !empty($searchFormFields[$value])) {
                    $searchFormFieldsForm[$key] = $searchFormFields[$value];
                }
            }
            if (isset($searchFormFieldsForm) && !empty($searchFormFieldsForm))
                $responseForm['fields'] = $searchFormFieldsForm;
        }
        return $responseForm;
    }

    /**
     * Set the profile fields value to newly created listing.
     * 
     * @return array
     */
    public function setProfileFields($sitereview, $data) {
        // Iterate over values
        $values = Engine_Api::_()->fields()->getFieldsValues($sitereview);

        $fVals = $data;
        $privacyOptions = Fields_Api_Core::getFieldPrivacyOptions();
        foreach ($fVals as $key => $value) {
            if (strstr($key, 'oauth'))
                continue;
            $parts = explode('_', $key);
            if (count($parts) < 3)
                continue;
            list($parent_id, $option_id, $field_id) = $parts;

            $valueParts = explode(',', $value);

            // Array mode
            if (is_array($valueParts) && count($valueParts) > 1) {
                // Lookup
                $valueRows = $values->getRowsMatching(array(
                    'field_id' => $field_id,
                    'item_id' => $sitereview->getIdentity()
                ));
                // Delete all
                foreach ($valueRows as $valueRow) {
                    $valueRow->delete();
                }
                if ($field_id == 0)
                    continue;
                // Insert all
                $indexIndex = 0;
                if (is_array($valueParts) || !empty($valueParts)) {
                    foreach ((array) $valueParts as $singleValue) {

                        $valueRow = $values->createRow();
                        $valueRow->field_id = $field_id;
                        $valueRow->item_id = $sitereview->getIdentity();
                        $valueRow->index = $indexIndex++;
                        $valueRow->value = $singleValue;
                        $valueRow->save();
                    }
                } else {
                    $valueRow = $values->createRow();
                    $valueRow->field_id = $field_id;
                    $valueRow->item_id = $sitereview->getIdentity();
                    $valueRow->index = 0;
                    $valueRow->value = '';
                    $valueRow->save();
                }
            }

            // Scalar mode
            else {
                try {
                    // Lookup
                    $valueRows = $values->getRowsMatching(array(
                        'field_id' => $field_id,
                        'item_id' => $sitereview->getIdentity()
                    ));
                    // Delete all
                    $prevPrivacy = null;
                    foreach ($valueRows as $valueRow) {
                        $valueRow->delete();
                    }

                    // Remove value row if empty
                    if (empty($value)) {
                        if ($valueRow) {
                            $valueRow->delete();
                        }
                        continue;
                    }

                    if ($field_id == 0)
                        continue;
                    // Lookup
                    $valueRow = $values->getRowMatching(array(
                        'field_id' => $field_id,
                        'item_id' => $sitereview->getIdentity(),
                        'index' => 0
                    ));
                    // Create if missing
                    $isNew = false;
                    if (!$valueRow) {
                        $isNew = true;
                        $valueRow = $values->createRow();
                        $valueRow->field_id = $field_id;
                        $valueRow->item_id = $sitereview->getIdentity();
                    }
                    $valueRow->value = htmlspecialchars($value);
                    $valueRow->save();
                } catch (Exception $ex) {
                    
                }
            }
        }

        return;
    }

    /*
     * Search profile fields
     * 
     * @return array
     */

    public function getSearchProfileFields() {
        $this->_validateSearchProfileFields = true;
        $this->_profileFieldsArray = $this->_getProfileTypes();

        $getProfileFields = $this->_getProfileFields();
        return $getProfileFields;
    }

    public function getWishlistSearchForm() {

        $wishlistSearch[] = array(
            'type' => 'Text',
            'name' => 'search',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Search'),
        );

        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        if ($viewer_id) {
            $wishlistSearch[] = array(
                'type' => 'Select',
                'name' => 'search_wishlist',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Wishlists'),
                'multiOptions' => array(
                    '' => '',
                    'my_wishlists' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('My Wishlist'),
                    'friends_wishlists' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('My Friends Wishlists'),
                ),
            );
        }

        $wishlistSearch[] = array(
            'type' => 'Text',
            'name' => 'text',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate("Member's Name/Email"),
        );

        $wishlistSearch[] = array(
            'type' => 'Select',
            'name' => 'orderby',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Browse By'),
            'multiOptions' => array(
                'wishlist_id' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Most Recent'),
                'total_item' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Maximum Events'),
                'view_count' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Most Viewed'),
            ),
        );

        $wishlistSearch[] = array(
            'type' => 'Submit',
            'name' => 'done',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Search'),
        );
        return $wishlistSearch;
    }

    /*
     * Gets the info of passed listingtype_id. In case, if you are sending -1 then return all listingtypes info.
     * 
     * @param int listingtype_id
     * @return array
     */

    public function setListingTypeInRegistry($listingtype_id = 1) {
        if ($listingtype_id > 0) {
            $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingtype_id);
            if (!empty($listingType) && $listingType->wishlist) {
                $listingType->wishlist = Engine_Api::_()->authorization()->isAllowed('sitereview_wishlist', null, 'view');
            }
            return $listingType;
        } elseif ($listingtype_id == -1) {
            $allowWishlistView = Engine_Api::_()->authorization()->isAllowed('sitereview_wishlist', null, 'view');
            $listingTypes = Engine_Api::_()->getItemTable('sitereview_listingtype')->fetchAll();
            foreach ($listingTypes as $listingType) {
                if ($listingType->wishlist)
                    $listingType->wishlist = $allowWishlistView;

                $listingTypeObj['listingtypeArray' . $listingType->listingtype_id] = $listingType;
            }
            return $listingTypeObj;
        }

        return false;
    }

    public function getTellAFriendForm() {
        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $tell[] = array(
            'type' => 'Text',
            'name' => 'sender_name',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Your Name'),
            'hasValidator' => 'true'
        );

        $tell[] = array(
            'type' => 'Text',
            'name' => 'sender_email',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Your Email'),
            'hasValidator' => 'true'
        );

        $tell[] = array(
            'type' => 'Text',
            'name' => 'receiver_emails',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('To'),
            'description' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Separate multiple addresses with commas'),
            'hasValidator' => 'true'
        );

        $tell[] = array(
            'type' => 'Textarea',
            'name' => 'message',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Message'),
            'description' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('You can send a personal note in the mail.'),
            'hasValidator' => 'true',
        );

        $tell[] = array(
            'type' => 'Checkbox',
            'name' => 'send_me',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate("Send a copy to my email address."),
        );


        $tell[] = array(
            'type' => 'Submit',
            'name' => 'send',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Tell a Friend'),
        );
        return $tell;
    }

    public function getMessageOwnerForm() {
        $message = array();

        // init title
        $message[] = array(
            'type' => 'Text',
            'name' => 'title',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Subject'),
            'hasValidator' => 'true'
        );

        // init body - plain text
        $message[] = array(
            'type' => 'Textarea',
            'name' => 'body',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Message'),
            'hasValidator' => 'true'
        );

        $message[] = array(
            'type' => 'Submit',
            'name' => 'submit',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Send Message'),
        );
        return $message;
    }

    public function getCreateWishlistForm() {
        if (_CLIENT_TYPE && (_CLIENT_TYPE == 'ios')) {
            $add[] = array(
                "type" => "Label",
                "name" => "create_wishlist_description",
                "label" => $this->translate('You can also add this Listings in a new wishlist below:')
            );
        }
        $add[] = array(
            'type' => 'Text',
            'name' => 'title',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Wishlist Name'),
            'hasValidator' => 'true'
        );


        $add[] = array(
            'type' => 'Textarea',
            'name' => 'body',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Wishlist Note'),
        );

        $availableLabels = array(
            'everyone' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Everyone'),
            'registered' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('All Registered Members'),
            'owner_network' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Friends and Networks'),
            'owner_member_member' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Friends of Friends'),
            'owner_member' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Friends Only'),
            'owner' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Just Me')
        );

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('siteevent_wishlist', $viewer, 'auth_view');
        $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
        $viewOptionsReverse = array_reverse($viewOptions);
        $orderPrivacyHiddenFields = 786590;

        if (count($viewOptions) > 1) {
            $add[] = array(
                'type' => 'Select',
                'name' => 'auth_view',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('View Privacy'),
                'description' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Who may see this wishlist?'),
                'multiOptions' => $viewOptions,
                'value' => key($viewOptionsReverse),
            );
        }

        $add[] = array(
            'type' => 'Submit',
            'name' => 'submit',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Save'),
        );
        return $add;
    }

    public function getAddToWishlistForm() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $wishlistDatas = Engine_Api::_()->getDbtable('wishlists', 'sitereview')->userWishlists($viewer);
        $wishlistDatasCount = Count($wishlistDatas);
        $listing_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', null);
        $event = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

        $wishlistIdsDatas = Engine_Api::_()->getDbtable('wishlistmaps', 'sitereview')->pageWishlists($listing_id, $viewer_id);

        if (!empty($wishlistIdsDatas)) {
            $wishlistIdsDatas = $wishlistIdsDatas->toArray();
            $wishlistIds = array();
            if (_CLIENT_TYPE && (_CLIENT_TYPE == 'ios') && $wishlistDatasCount > 0) {
                $add[] = array(
                    "type" => "Label",
                    "name" => "add_wishlist_description",
                    "label" => $this->translate('Please select the wishlists in which you want to add this listing.')
                );
            }
            foreach ($wishlistIdsDatas as $wishlistIdsData) {
                $wishlistIds[] = $wishlistIdsData['wishlist_id'];
            }
        }

        foreach ($wishlistDatas as $wishlistData) {

            if (in_array($wishlistData->wishlist_id, $wishlistIds)) {
                $add[] = array(
                    'type' => 'Checkbox',
                    'name' => 'inWishlist_' . $wishlistData->wishlist_id,
                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate($wishlistData->title),
                    'value' => 1,
                );
            } else {
                $add[] = array(
                    'type' => 'Checkbox',
                    'name' => 'wishlist_' . $wishlistData->wishlist_id,
                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate($wishlistData->title),
                    'value' => 0,
                );
            }
        }
        if (_CLIENT_TYPE && (_CLIENT_TYPE == 'ios') && $wishlistDatasCount > 0) {
            $add[] = array(
                "type" => "Label",
                "name" => "create_wishlist_description",
                "label" => $this->translate('You can also add this Listings in a new wishlist below:')
            );
        } else {
            $add[] = array(
                "type" => "Label",
                "name" => "create_wishlist_description",
                "label" => $this->translate('You have not created any wishlist yet. Get Started by creating and adding listings.')
            );
        }

        if ($wishlistDatasCount) {
            $add[] = array(
                'type' => 'Text',
                'name' => 'title',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Wishlist Name'),
            );
        } else {
            $add[] = array(
                'type' => 'Text',
                'name' => 'title',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Wishlist Name'),
                'hasValidator' => 'true'
            );
        }

        $add[] = array(
            'type' => 'Textarea',
            'name' => 'body',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Description'),
        );

        $availableLabels = array(
            'everyone' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Everyone'),
            'registered' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('All Registered Members'),
            'owner_network' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Friends and Networks'),
            'owner_member_member' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Friends of Friends'),
            'owner_member' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Friends Only'),
            'owner' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Just Me')
        );

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('siteevent_wishlist', $viewer, 'auth_view');
        $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
        $viewOptionsReverse = array_reverse($viewOptions);
        $orderPrivacyHiddenFields = 786590;

        if (count($viewOptions) > 1) {
            $add[] = array(
                'type' => 'Select',
                'name' => 'auth_view',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('View Privacy'),
                'description' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Who may see this wishlist?'),
                'multiOptions' => $viewOptions,
                'value' => key($viewOptionsReverse),
            );
        }

        $add[] = array(
            'type' => 'Submit',
            'name' => 'submit',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Save'),
        );
        $response['form'] = $add;
        
         if($wishlistDatasCount > 0){
              $response['add_wishlist_description'] = $this->translate('Please select the wishlists in which you want to add this listing.');
             $response['create_wishlist_descriptions'] = $this->translate('You can also add this listing in a new wishlist below.');
         }
         else
            $response['create_wishlist_descriptions'] = $this->translate('You have not created any wishlist yet. Get started by creating and adding entries.');
        return $response;
    }

    /**
     * Get the video create form.
     * 
     * @return array
     */
    public function getVideoCreateForm($subject = null) {
        $videoForm = array();
        $viewer = Engine_Api::_()->user()->getViewer();

        $videoForm[] = array(
            'type' => 'Text',
            'name' => 'title',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Video Title'),
            'hasValidator' => true
        );

        $videoForm[] = array(
            'type' => 'Text',
            'name' => 'tags',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Tags (Keywords)'),
            'description' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Separate tags with commas.')
        );

        $videoForm[] = array(
            'type' => 'Textarea',
            'name' => 'description',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Video Description'),
        );

        $videoForm[] = array(
            'type' => 'Checkbox',
            'name' => 'search',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Show this video entry in search results')
        );

        if (empty($subject)) {

            // Element: Add Type
            $video_options = Array();
            $video_options[2] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Vimeo');

            //My Computer
            $allowed_upload = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $viewer, 'upload');
            $ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
            if (!empty($ffmpeg_path) && $allowed_upload) {
                $video_options[3] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('My Device');
            }
            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey')) {
                $video_options[1] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('YouTube');
            }
            $videoForm[] = array(
                'type' => 'Select',
                'name' => 'type',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Video Source'),
                'multiOptions' => $video_options,
            );

            $videoForm[] = array(
                'type' => 'Text',
                'name' => 'url',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Video Link (URL)'),
                'description' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Paste the web address of the video here.'),
            );

            $videoForm[] = array(
                'type' => 'File',
                'name' => 'filedata',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Add Video')
            );

            $videoForm[] = array(
                'type' => 'Submit',
                'name' => 'submit',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Post Video')
            );
        } else {
            $videoForm[] = array(
                'type' => 'Submit',
                'name' => 'submit',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Save Video')
            );
        }

        return $videoForm;
    }

    public function getReviewCreateForm($settingsReviews) {
        //GET VIEWER INFO
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        //GET EVENT ID
        $getItemEvent = $settingsReviews['item'];
        $sitereview_proscons = $settingsReviews['settingsReview']['sitereview_proscons'];
        $sitereview_limit_proscons = $settingsReviews['settingsReview']['sitereview_limit_proscons'];
        $sitereview_recommend = $settingsReviews['settingsReview']['sitereview_recommend'];

        if ($sitereview_proscons) {
            if ($sitereview_limit_proscons) {
                $createReview[] = array(
                    'type' => 'Textarea',
                    'name' => 'pros',
                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Pros'),
                    'description' => $this->translate("What do you like about this listing?"),
                    'hasValidator' => 'true'
//                   
                );
            } else {
                $createReview[] = array(
                    'type' => 'Textarea',
                    'name' => 'pros',
                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Pros'),
                    'description' => $this->translate("What do you like about this listing?"),
                    'hasValidator' => 'true',
                );
            }


            if ($sitereview_limit_proscons) {
                $createReview[] = array(
                    'type' => 'Textarea',
                    'name' => 'cons',
                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Cons'),
                    'description' => $this->translate("What do you dislike about this listing?"),
                    'hasValidator' => 'true',
                );
            } else {
                $createReview[] = array(
                    'type' => 'Textarea',
                    'name' => 'cons',
                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Cons'),
                    'description' => $this->translate("What do you dislike about this listing?"),
                    'hasValidator' => 'true',
                );
            }
        }

        $createReview[] = array(
            'type' => 'Textarea',
            'name' => 'title',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('One-line summary'),
            'hasValidator' => 'true',
        );
//
//        $profileTypeReview = $this->getProfileTypeReview();
//        if (!empty($profileTypeReview)) {
//            
//            $customFields = $this->getSiteeventFormCustomStandard(array(
//                'item' => 'sitereview_review',
//                'topLevelId' => 1,
//                'topLevelValue' => $profileTypeReview,
//                'decorators' => array(
//                    'FormElements'
//            )));
//
//            $customFields->removeElement('submit');
//
//            $this->addSubForms(array(
//                'fields' => $customFields
//            ));
//        }

        $createReview[] = array(
            'type' => 'Textarea',
            'name' => 'body',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Summary'),
            'hasValidator' => 'true',
        );

        if ($sitereview_recommend) {
            $createReview[] = array(
                'type' => 'Radio',
                'name' => 'recommend',
                'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Recommended'),
                'description' => sprintf($this->translate("Would you recommend %s to a friend?"), $event_title),
                'multiOptions' => array(
                    1 => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Yes'),
                    0 => Engine_Api::_()->getApi('Core', 'siteapi')->translate('No')
                ),
                'value' => '1'
            );
        }

        $createReview[] = array(
            'type' => 'Submit',
            'name' => 'submit',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Submit'),
        );
        return $createReview;
    }

    public function getReviewUpdateForm() {

        $updateReview[] = array(
            'type' => 'Textarea',
            'name' => 'body',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Summary'),
        );

        $updateReview[] = array(
            'type' => 'Submit',
            'name' => 'submit',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Add your Opinion'),
        );
        return $updateReview;
    }

    public function getVideoURL($video, $autoplay = true) {
        // YouTube
        if ($video->type == 1) {
            return 'www.youtube.com/embed/' . $video->code . '?wmode=opaque' . ($autoplay ? "&autoplay=1" : "");
        } elseif ($video->type == 2) { // Vimeo
            return 'player.vimeo.com/video/' . $video->code . '?title=0&amp;byline=0&amp;portrait=0&amp;wmode=opaque' . ($autoplay ? "&amp;autoplay=1" : "");
        } elseif ($video->type == 3) { // Uploded Videos
            $staticBaseUrl = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.staticBaseUrl', null);
            $video_location = Engine_Api::_()->storage()->get($video->file_id, $video->getType())->getHref();
            $getHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
            return (empty($staticBaseUrl)) ? $getHost . $video_location : $video_location;
        }
    }

    /**
     * Return the "Photo Edit" form. 
     * 
     * @return array
     */
    public function getPhotoEditForm($form = array()) {
        $form[] = array(
            'type' => 'Text',
            'name' => 'title',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Title'),
            'hasValidator' => true
        );

        $form[] = array(
            'type' => 'Textarea',
            'name' => 'description',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Description'),
            'hasValidator' => true
        );

        $form[] = array(
            'type' => 'Submit',
            'name' => 'submit',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Submit')
        );

        return $form;
    }

    public function getClaimlistingForm($listingType) {
        $listingTypeObj = $listingType;
        $listing_singular_uc = ucfirst($listingTypeObj->title_singular);
        $listing_singular_lc = strtolower($listingTypeObj->title_singular);

        $form[] = array(
            'type' => 'Text',
            'name' => 'nickname',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Your Name'),
            'hasValidator' => true
        );

        $form[] = array(
            'type' => 'Text',
            'name' => 'email',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Your Email'),
            'hasValidator' => true
        );

        $form[] = array(
            'type' => 'Textarea',
            'name' => 'about',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate("About You and the $listing_singular_uc"),
            'hasValidator' => true
        );

        $form[] = array(
            'type' => 'Text',
            'name' => 'contactno',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Contact Number'),
        );

        $form[] = array(
            'type' => 'Textarea',
            'name' => 'usercomments',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Comments'),
        );
        $description = $this->translate("I have read and agree to the terms of service.");
        $form[] = array(
            'type' => 'Checkbox',
            'name' => 'terms',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Terms of Service'),
            'value' => 0,
            'description' => $description
        );

        $form[] = array(
            'type' => 'Submit',
            'name' => 'send',
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Save'),
        );

        return $form;
    }

    public function createPhoto($params, $file) {

        if ($file instanceof Storage_Model_File) {
            $params['file_id'] = $file->getIdentity();
        } else {

            //GET IMAGE INFO AND RESIZE
            $name = basename($file['tmp_name']);
            $path = dirname($file['tmp_name']);
            $extension = ltrim(strrchr($file['name'], '.'), '.');

            $mainName = $path . '/m_' . $name . '.' . $extension;
            $thumbName = $path . '/t_' . $name . '.' . $extension;
            $thumbLargeName = $path . '/t_l_' . $name . '.' . $extension;

            $image = Engine_Image::factory();
            $image->open($file['tmp_name'])
                    ->resize(self::IMAGE_WIDTH, self::IMAGE_HEIGHT)
                    ->write($mainName)
                    ->destroy();

            $image = Engine_Image::factory();
            $image->open($file['tmp_name'])
                    ->resize(self::THUMB_WIDTH, self::THUMB_HEIGHT)
                    ->write($thumbName)
                    ->destroy();
            $image = Engine_Image::factory();
            $image->open($file['tmp_name'])
                    ->resize(self::THUMB_LARGE_WIDTH, self::THUMB_LARGE_HEIGHT)
                    ->write($thumbLargeName)
                    ->destroy();

            //RESIZE IMAGE (ICON)
            $iSquarePath = $path . '/is_' . $name . '.' . $extension;
            $image = Engine_Image::factory();
            $image->open($file['tmp_name']);

            $size = min($image->height, $image->width);
            $x = ($image->width - $size) / 2;
            $y = ($image->height - $size) / 2;

            $image->resample($x, $y, $size, $size, 48, 48)
                    ->write($iSquarePath)
                    ->destroy();

            //STORE PHOTO
            $photo_params = array(
                'parent_id' => $params['listing_id'],
                'parent_type' => 'sitereview_listing',
            );

            $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);
            $thumbFile = Engine_Api::_()->storage()->create($thumbName, $photo_params);
            $photoFile->bridge($thumbFile, 'thumb.normal');

            $thumbLargeFile = Engine_Api::_()->storage()->create($thumbLargeName, $photo_params);
            $photoFile->bridge($thumbLargeFile, 'thumb.large');

            $iSquare = Engine_Api::_()->storage()->create($iSquarePath, $photo_params);
            $photoFile->bridge($iSquare, 'thumb.icon');
            $params['file_id'] = $photoFile->file_id;
            $params['photo_id'] = $photoFile->file_id;

            //REMOVE TEMP FILES
            @unlink($mainName);
            @unlink($thumbName);
            @unlink($thumbLargeName);
            @unlink($iSquarePath);
        }

        $row = Engine_Api::_()->getDbtable('photos', 'sitereview')->createRow();
        $row->setFromArray($params);
        $row->save();

        return $row;
    }

    /*
     * Get the array of all profile types.
     * 
     * @param profileFields array
     * @return array
     */

    private function _getProfileTypes($profileFields = array()) {
        $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('sitereview_listing');
        if (count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type') {
            $profileTypeField = $topStructure[0]->getChild();
            $options = $profileTypeField->getOptions();

            $options = $profileTypeField->getElementParams('sitereview_listing');
            if (isset($options['options']['multiOptions']) && !empty($options['options']['multiOptions']) && is_array($options['options']['multiOptions'])) {
                // Make exist profile fields array.         
                foreach ($options['options']['multiOptions'] as $key => $value) {
                    if (!empty($key)) {
                        $profileFields[$key] = $value;
                    }
                }
            }
        }
        return $profileFields;
    }

    /*
     * Get the profile fields
     * 
     * @param fieldsForm array
     * @return array
     */

    private function _getProfileFields($fieldsForm = array()) {
        
        foreach ($this->_profileFieldsArray as $option_id => $prfileFieldTitle) {
            if (!empty($option_id)) {
                $mapData = Engine_Api::_()->getApi('core', 'fields')->getFieldsMaps('sitereview_listing');
                $getRowsMatching = $mapData->getRowsMatching('option_id', $option_id);
                $fieldArray = array();
                $getFieldInfo = Engine_Api::_()->fields()->getFieldInfo();
                $getHeadingName = '';
                foreach ($getRowsMatching as $map) {
                    $meta = $map->getChild();
                    $type = $meta->type;

                    if (!empty($type) && ($type == 'heading')) {
                        $getHeadingName = $meta->label;
                        continue;
                    }

                    if (!empty($this->_validateSearchProfileFields) && (!isset($meta->search) || empty($meta->search)))
                        continue;

                    $fieldForm = $getMultiOptions = array();
                    $key = $map->getKey();

                    // Findout respective form element field array.
                    if (isset($getFieldInfo['fields'][$type]) && !empty($getFieldInfo['fields'][$type])) {
                        $getFormFieldTypeArray = $getFieldInfo['fields'][$type];

                        // In case of Generic profile fields.
                        if (isset($getFormFieldTypeArray['category']) && ($getFormFieldTypeArray['category'] == 'generic')) {
                            // If multiOption enabled then perpare the multiOption array.

                            if (($type == 'select') || ($type == 'radio') || (isset($getFormFieldTypeArray['multi']) && !empty($getFormFieldTypeArray['multi']))) {
                                $getOptions = $meta->getOptions();
                                if (!empty($getOptions)) {
                                    foreach ($getOptions as $option) {
                                        $getMultiOptions[$option->option_id] = $option->label;
                                    }
                                }
                            }

                            // Prepare Generic form.
                            $fieldForm['type'] = ucfirst($type);
                            $fieldForm['name'] = $key . '_field_' . $meta->field_id;
                            $fieldForm['label'] = (isset($meta->label) && !empty($meta->label)) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate($meta->label) : '';
                            $fieldForm['description'] = (isset($meta->description) && !empty($meta->description)) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate($meta->description) : '';
                            
                            // Add multiOption, If available.
                            if (!empty($getMultiOptions)) {
                                $fieldForm['multiOptions'] = $getMultiOptions;
                            }
                            // Add validator, If available.
                            if (isset($meta->required) && !empty($meta->required))
                                $fieldForm['hasValidator'] = true;
                               
                            if (COUNT($this->_profileFieldsArray) > 1) {

                                if (isset($this->_create) && !empty($this->_create) && $this->_create == 1) {
                                    $optionCategoryName = Engine_Api::_()->getDbtable('options', 'sitereview')->getProfileTypeLabel($option_id);
                                    $fieldsForm[$option_id][] = $fieldForm;
                                } else {
                                    $fieldsForm[$option_id][] = $fieldForm;
                                   
                                }
                            } else
                                $fieldsForm[$option_id][] = $fieldForm;
                             
                        }else if (isset($getFormFieldTypeArray['category']) && ($getFormFieldTypeArray['category'] == 'specific') && !empty($getFormFieldTypeArray['base'])) { // In case of Specific profile fields.
                            // Prepare Specific form.
                            $fieldForm['type'] = ucfirst($getFormFieldTypeArray['base']);
                            $fieldForm['name'] = $key . '_field_' . $meta->field_id;
                            $fieldForm['label'] = (isset($meta->label) && !empty($meta->label)) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate($meta->label) : '';
                            $fieldForm['description'] = (isset($meta->description) && !empty($meta->description)) ? $meta->description : '';

                            // Add multiOption, If available.
                            if ($getFormFieldTypeArray['base'] == 'select') {
                                $getOptions = $meta->getOptions();
                                foreach ($getOptions as $option) {
                                    $getMultiOptions[$option->option_id] = Engine_Api::_()->getApi('Core', 'siteapi')->translate($option->label);
                                }
                                $fieldForm['multiOptions'] = $getMultiOptions;
                            }

                            // Add validator, If available.
                            if (isset($meta->required) && !empty($meta->required))
                                $fieldForm['hasValidator'] = true;

                            if (COUNT($this->_profileFieldsArray) > 1) {
                                if (isset($this->_create) && !empty($this->_create) && $this->_create == 1) {
                                    $optionCategoryName = Engine_Api::_()->getDbtable('options', 'sitereview')->getProfileTypeLabel($option_id);
                                    $fieldsForm[$option_id][] = $fieldForm;
                                } else {
                                    $fieldsForm[$option_id][] = $fieldForm;
                                }
                            } else
                                $fieldsForm[] = $fieldForm;
                        }
                       
                    }
                }
            }
        }
        
        return $fieldsForm;
    }

    public function getApplyNowForm($listingtype_id) {
        $viewer = Engine_Api::_()->user()->getViewer();

        $listingTypetitle = ucfirst(Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'title_singular'));
        if ($listingTypetitle == 'Job') {
            $fileTitle = 'Resume';
            $postTitle = 'Apply';
        } else {
            $fileTitle = 'File';
            $postTitle = 'Post';
        }


        $applyNowForm[] = array(
            'type' => 'Text',
            'name' => 'sender_name',
            'label' => $this->translate('Your Name'),
            'hasValidator' => true,
        );

        $applyNowForm[] = array(
            'type' => 'Text',
            'name' => 'sender_email',
            'label' => $this->translate('Your Email'),
            'hasValidator' => true,
        );

        $applyNowForm[] = array(
            'type' => 'Text',
            'name' => 'contact',
            'label' => $this->translate('Contact'),
            'hasValidator' => true
        );

        $applyNowForm[] = array(
            'type' => 'File',
            'name' => 'filename',
            'label' => $this->translate($fileTitle),
        );

        $applyNowForm[] = array(
            'type' => 'textarea',
            'name' => 'body',
            'label' => $this->translate('Message'),
            'hasValidator' => true
        );

        $applyNowForm[] = array(
            'type' => 'Submit',
            'name' => 'submit',
            'label' => $this->translate($postTitle),
        );

        $form['form'] = $applyNowForm;
        $form['formValues']['sender_name'] = $viewer->getTitle();
        $form['formValues']['sender_email'] = $viewer->email;

        return $form;
    }

    public function sendNotifications(Sitereview_Model_Listing $sitereview, $listingTypeID) {
        if (!empty($sitereview->draft)) {
            return $this;
        }

        //GET REVIEW OWNER
        $owner = $sitereview->getOwner('user');
        $listingtype_id = $sitereview->listingtype_id;

        $listingType = $this->setListingTypeInRegistry($listingTypeID);
        $title_singular = strtolower($listingType->title_singular);

        //GET NOTIFICATION TABLE
        $notificationTable = Engine_Api::_()->getDbtable('notifications', 'activity');
        $getSubTable = Engine_Api::_()->getDbtable('subscriptions', 'sitereview');

        //GET ALL SUBSCRIBERS
        $identities = $getSubTable->select()
                ->from($this, 'subscriber_user_id')
                ->where('user_id = ?', $sitereview->owner_id)
                ->where('listingtype_id =?', $sitereview->listingtype_id)
                ->query()
                ->fetchAll(Zend_Db::FETCH_COLUMN);

        if (empty($identities) || count($identities) <= 0) {
            return $this;
        }

        $users = Engine_Api::_()->getItemMulti('user', $identities);

        if (empty($users) || count($users) <= 0) {
            return $this;
        }

        //SEND NOTIFICATIONS
        foreach ($users as $user) {
            $notificationTable->addNotification($user, $owner, $sitereview, 'sitereview_subscribed_new', array("listingtype" => $title_singular));
        }

        return $this;
    }

    public function getInformation($sitereview, $edit=0) {
        $profileFields = $this->_getProfileTypes();
        
        if (!empty($profileFields)) {
            $this->_profileFieldsArray = $profileFields;
        }
        if (isset($edit) && !empty($edit))
            $information = $this->getProfileInfo($sitereview, $edit);
        else {
            $information = $this->getProfileInfo($sitereview);
           ;
            foreach ($information as $key => $value) {
                if (isset($value) && !empty($value) && is_array($value)) {
                    $information[$key] = @implode(", ", $value);
                }
            }
        }
        return $information;
    }

    // Get the Profile Fields Information, which will show on profile page.
    public function getProfileInfo($subject, $setKeyAsResponse = false) {
        $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sitereview')->defaultProfileId();
        // Getting the default Profile Type id.
        $getFieldId = (isset($subject->profile_type)) ? $subject->profile_type : $defaultProfileId;
        // Start work to get form values.
        $values = Engine_Api::_()->fields()->getFieldsValues($subject);
        
        $fieldValues = array();
       
        // In case if Profile Type available. like User module.
        if (!empty($getFieldId)) {
            // Set the default profile type.
            $this->_profileFieldsArray[$getFieldId] = $getFieldId;
            $_getProfileFields = $this->_getProfileFields();
            
            $specificProfileFields[$getFieldId] = $_getProfileFields[$getFieldId];
            
            foreach ($specificProfileFields as $heading => $tempValue) {
                foreach ($tempValue as $value) {
                    $key = $value['name'];
                    $label = $value['label'];
                    $type = $value['type'];
                    $parts = @explode('_', $key);
                   
                    if (count($parts) < 3)
                        continue;
                     
                    list($parent_id, $option_id, $field_id) = $parts;
                    $valueRows = $values->getRowsMatching(array(
                        'field_id' => $field_id,
                        'item_id' => $subject->getIdentity()
                    ));

                    if (!empty($valueRows)) {
                        foreach ($valueRows as $fieldRow) {
                            $tempValue = $fieldRow->value;
                            // In case of Select or Multi send the respective label.
                            if (isset($value['multiOptions']) && !empty($value['multiOptions']) && isset($value['multiOptions'][$fieldRow->value]))
                                $tempValue = !empty($setKeyAsResponse) ? $fieldRow->value : $value['multiOptions'][$fieldRow->value];
                            $tempKey = !empty($setKeyAsResponse) ? $key : $label;
                            if (isset($fieldValues[$tempKey]) && !empty($fieldValues[$tempKey])) {
                                if (is_array($fieldValues[$tempKey])) {
                                    $fieldValues[$tempKey][] = $tempValue;
                                } else {
                                    $fieldValues[$tempKey] = array($fieldValues[$tempKey], $tempValue);
                                }
                            } else if (isset($value['type']) && !empty($value['type']) && ($value['type'] == 'Multi_checkbox' || $value['type'] == 'Multiselect') && isset($value['multiOptions']) && !empty($value['multiOptions'])) {
                                $fieldValues[$tempKey][] = $tempValue;
                            } else {
                                $fieldValues[$tempKey] = $tempValue;
                            }
                        }
                    }
                }
               
            }
        } else { // In case, If there are no Profile Type available and only Profile Fields are available. like Classified.
            $getType = $subject->getType();
            $_getProfileFields = $this->_getProfileFields();
            
            foreach ($_getProfileFields as $value) {
                $key = $value['name'];
                $label = $value['label'];
                $parts = @explode('_', $key);

                if (count($parts) < 3)
                    continue;

                list($parent_id, $option_id, $field_id) = $parts;

                $valueRows = $values->getRowsMatching(array(
                    'field_id' => $field_id,
                    'item_id' => $subject->getIdentity()
                ));

                if (!empty($valueRows)) {
                    foreach ($valueRows as $fieldRow) {
                        if (!empty($fieldRow->value)) {
                            $tempKey = !empty($setKeyAsResponse) ? $key : $label;
                            $fieldValues[$tempKey] = $fieldRow->value;
                        }
                    }
                }
            }
        }
        return $fieldValues;
    }

    /*
     * Set listing photo
     */

    public function setPhoto($photo, $subject, $needToUplode = false) {
        try {
            if ($photo instanceof Zend_Form_Element_File) {
                $file = $photo->getFileName();
            } else if (is_array($photo) && !empty($photo['tmp_name'])) {
                $file = $photo['tmp_name'];
            } else if (is_string($photo) && file_exists($photo)) {
                $file = $photo;
            } else {
                throw new Group_Model_Exception('invalid argument passed to setPhoto');
            }
        } catch (Exception $e) {
            
        }

        $imageName = $photo['name'];
        $name = basename($file);
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';

        $params = array(
            'parent_type' => 'sitereview_listing',
            'parent_id' => $subject->getIdentity()
        );

        // Save
        $storage = Engine_Api::_()->storage();

        // Resize image (main)
        $image = Engine_Image::factory();
        $image->open($file)
                ->resize(720, 720)
                ->write($path . '/m_' . $imageName)
                ->destroy();

        // Resize image (profile)
        $image = Engine_Image::factory();
        $image->open($file)
                ->resize(200, 400)
                ->write($path . '/p_' . $imageName)
                ->destroy();

        // Resize image (normal)
        $image = Engine_Image::factory();
        $image->open($file)
                ->resize(140, 160)
                ->write($path . '/in_' . $imageName)
                ->destroy();

        // Resize image (icon)
        $image = Engine_Image::factory();
        $image->open($file);

        $size = min($image->height, $image->width);
        $x = ($image->width - $size) / 2;
        $y = ($image->height - $size) / 2;

        $image->resample($x, $y, $size, $size, 48, 48)
                ->write($path . '/is_' . $imageName)
                ->destroy();

        // Store
        $iMain = $storage->create($path . '/m_' . $imageName, $params);
        $iProfile = $storage->create($path . '/p_' . $imageName, $params);
        $iIconNormal = $storage->create($path . '/in_' . $imageName, $params);
        $iSquare = $storage->create($path . '/is_' . $imageName, $params);

        $iMain->bridge($iProfile, 'thumb.profile');
        $iMain->bridge($iIconNormal, 'thumb.normal');
        $iMain->bridge($iSquare, 'thumb.icon');

        // Remove temp files
        @unlink($path . '/p_' . $imageName);
        @unlink($path . '/m_' . $imageName);
        @unlink($path . '/in_' . $imageName);
        @unlink($path . '/is_' . $imageName);

        // Update row
        if (empty($needToUplode)) {
            $subject->modified_date = date('Y-m-d H:i:s');
            $subject->photo_id = $iMain->file_id;
            $subject->save();
        }

        // Add to album        
        $viewer = Engine_Api::_()->user()->getViewer();
        $photoTable = Engine_Api::_()->getItemTable('sitereview_photo');
        $rows = $photoTable->fetchRow($photoTable->select()->from($photoTable->info('name'), 'order')->order('order DESC')->limit(1));
        $order = 0;
        if (!empty($rows)) {
            $order = $rows->order + 1;
        }
        $sitereviewAlbum = $subject->getSingletonAlbum();
        $photoItem = $photoTable->createRow();
        $photoItem->setFromArray(array(
            'listing_id' => $subject->getIdentity(),
            'album_id' => $sitereviewAlbum->getIdentity(),
            'user_id' => $viewer->getIdentity(),
            'file_id' => $iMain->getIdentity(),
            'collection_id' => $sitereviewAlbum->getIdentity(),
            'order' => $order
        ));
        $photoItem->save();

        return $subject;
    }

    private function translate($message = '') {
        return Engine_Api::_()->getApi('Core', 'siteapi')->translate($message);
    }

    /**
     * Create document
     *
     * @param array file_pass 
     * @return create job and return info
     * */
    public function setFile($file_pass, $listing_id) {
        if ($file_pass instanceof Zend_Form_Element_File) {
            $file = $file_pass->getFileName();
        } else if (is_array($file_pass) && !empty($file_pass['tmp_name'])) {
            $file = $file_pass['tmp_name'];
        } else if (is_string($file_pass) && file_exists($file_pass)) {
            $file = $file_pass;
        } else {
            throw new Sitereview_Model_Exception('invalid argument passed to setFile');
        }
        $params = array(
            'parent_type' => 'sitereview_listing',
            'parent_id' => $listing_id
        );

        @chmod($file, 0777);

        try {
            $storage = Engine_Api::_()->getItemTable('storage_file');
            $job_return = $storage->createFile($file_pass, array(
                'parent_id' => $listing_id,
                'parent_type' => 'sitereview_listing',
            ));

            //REMOVE TEMPORARY FILE
            @unlink($file['tmp_name']);
        } catch (Exception $e) {

            $msg = $e->getMessage();
            return $msg;
        }

        if (!empty($job_return->file_id)) {
            return $job_return->file_id;
        }
    }

    /*
     * Get 2nd & 3rd level categories array.
     * 
     * @param listingtype_id int 
     * @return array
     */

    private function _getCategoriesSearchForm($listingtype_id, $responseForm) {
        $categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 0, $listingtype_id, 0, 1, 0, 'category_name', 0, array('category_id', 'category_name', 'cat_order', 'profile_type'));

        if (count($categories) != 0) {
            foreach ($categories as $category) {
                $subCategories = array();
                $subCategoriesObj = Engine_Api::_()->getDbTable('categories', 'sitereview')->getSubCategories($category->category_id);
                $getCategories[$category->category_id] = $category->category_name;
                if (isset($category->profile_type) && !empty($category->profile_type))
                    $categoryProfileTypeMapping[$category->category_id] = $category->profile_type;

                $getsubCategories = array();

                foreach ($subCategoriesObj as $subcategory) {

                    $subsubCategories = array();

                    $subsubCategoriesObj = Engine_Api::_()->getDbTable('categories', 'sitereview')->getSubCategories($subcategory->category_id, array('category_id', 'profile_type', 'cat_order', 'category_name'));
                    $subsubCategories = array();
                    foreach ($subsubCategoriesObj as $subsubcategory) {
                        $subsubCategories[$subsubcategory->category_id] = $subsubcategory->category_name;
                        if (isset($subsubcategory->profile_type) && !empty($subsubcategory->profile_type))
                            $categoryProfileTypeMapping[$subsubcategory->category_id] = $subsubcategory->profile_type;
                    }

                    if (count($subsubCategories) != 0) {
                        $subsubCategories[0] = "";
                    }
                    if (isset($subsubCategories) && !empty($subsubCategories) && count($subsubCategories) > 1) {
                        $subsubCategoriesForm[$subcategory->category_id] = array(
                            'type' => 'Select',
                            'name' => 'subsubcategory_id',
                            'label' => '3rd Level Category',
                            'multiOptions' => $subsubCategories,
                        );
                    }
                    $getsubCategories[$subcategory->category_id] = $subcategory->category_name;
                    if (isset($subcategory->profile_type) && !empty($subcategory->profile_type))
                        $categoryProfileTypeMapping[$subcategory->category_id] = $subcategory->profile_type;
                }

                if (count($getsubCategories) != 0) {
                    $getsubCategories[0] = "";
                }
                if (isset($getsubCategories) && !empty($getsubCategories) && count($getsubCategories) > 1) {
                    $subcategoriesForm = array(
                        'type' => 'Select',
                        'name' => 'subcategory_id',
                        'label' => 'Sub-Category',
                        'multiOptions' => $getsubCategories,
                    );
                }

                if (isset($subcategoriesForm) && !empty($subcategoriesForm))
                    $form[$category->category_id]['form'] = $subcategoriesForm;
                if (isset($subsubCategoriesForm) && count($subsubCategoriesForm) > 0)
                    $form[$category->category_id]['subsubcategories'] = $subsubCategoriesForm;
                $subsubCategoriesForm = array();
            }
        }

        if (isset($form) && !empty($form))
            $responseForm['categoriesForm'] = $form;

        return $responseForm;
    }

}

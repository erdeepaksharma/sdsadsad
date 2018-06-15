<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: IndexController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_IndexController extends Seaocore_Controller_Action_Standard {

    protected $_navigation;
    protected $_listingType;

    //COMMON ACTION WHICH CALL BEFORE EVERY ACTION OF THIS CONTROLLER
    public function init() {
        //SET LISTING TYPE ID AND OBJECT
        $listingtype_id = $this->_getParam('listingtype_id', null);
        if ($listingtype_id != -1 && !empty($listingtype_id)) {
            Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
            $this->_listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);
            Zend_Registry::isRegistered('sitereviewGetListingType') ? $sitereviewGetListingType = true : $this->_setParam('listing_id', 0);

            //AUTHORIZATION CHECK
            if ($this->_getParam('action', null) != 'categories')
                if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
                    return;
        }

        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
            //FOR UPDATE EXPIRATION
            if ((Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereviewpaidlisting.task.updateexpiredlistings') + 900) <= time()) {
                Engine_Api::_()->sitereviewpaidlisting()->updateExpiredListings($listingtype_id);
            }
        }
    }

    //NONE USER SPECIFIC METHODS
    public function indexAction() {
        //GET PAGE OBJECT
        $listingtype_id = $this->_listingType->listingtype_id;
        $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
        $pageSelect = $pageTable->select()->where('name = ?', "sitereview_index_index_listtype_$listingtype_id");
        $pageObject = $pageTable->fetchRow($pageSelect);
        $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');

        //SET META PARAMS
        $params = array();
        $listing_type_title = '';
        if (empty($pageObject->title)) {
            $listing_type_title = ucfirst($this->_listingType->title_plural);
            $params['default_title'] = $title = Zend_Registry::get('Zend_Translate')->_('Browse ' . $listing_type_title);
        }
        $description = '';
        if (!empty($pageObject->description)) {
            //$params['description'] = $description = $pageObject->description;
        } else {
            $listing_type_singular = strtolower($listingTypeTable->getListingTypeColumn($listingtype_id, 'title_singular'));
            $params['description'] = $description = Zend_Registry::get('Zend_Translate')->_('This is the ' . $listing_type_singular . ' browse page.');
        }

        //GET LISTING CATEGORY TABLE
        $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $browseCategories = Zend_Registry::isRegistered('sitereviewBrowseCategories') ? Zend_Registry::get('sitereviewBrowseCategories') : null;

        $category_id = $request->getParam('category_id', null);
        if (empty($browseCategories)) {
            $this->view->categoryError = $this->view->translate("There are no categories found for this page.");
            return;
        }

        if (!empty($category_id)) {
            if ($listing_type_title)
                $params['listing_type_title'] = $title = $listing_type_title;
            $meta_title = $tableCategory->getCategory($category_id)->meta_title;
            if (empty($meta_title)) {
                if(Engine_Api::_()->getItem('sitereview_category', $category_id)){
                    $params['categoryname'] = Engine_Api::_()->getItem('sitereview_category', $category_id)->getCategorySlug();
                }
            } else {
                $params['categoryname'] = $meta_title;
            }

            $meta_description = $tableCategory->getCategory($category_id)->meta_description;
            if (!empty($meta_description))
                $params['description'] = $meta_description;

            $meta_keywords = $tableCategory->getCategory($category_id)->meta_keywords;
            if (empty($meta_keywords)) {
                $params['categoryname_keywords'] = Engine_Api::_()->getItem('sitereview_category', $category_id)->getCategorySlug();
            } else {
                $params['categoryname_keywords'] = $meta_keywords;
            }

            $subcategory_id = $request->getParam('subcategory_id', null);

            if (!empty($subcategory_id)) {
                $meta_title = $tableCategory->getCategory($subcategory_id)->meta_title;
                if (empty($meta_title)) {
                    $params['subcategoryname'] = Engine_Api::_()->getItem('sitereview_category', $subcategory_id)->getCategorySlug();
                } else {
                    $params['subcategoryname'] = $meta_title;
                }
                $meta_description = $tableCategory->getCategory($subcategory_id)->meta_description;
                if (!empty($meta_description))
                    $params['description'] = $meta_description;

                $meta_keywords = $tableCategory->getCategory($subcategory_id)->meta_keywords;
                if (empty($meta_keywords)) {
                    $params['subcategoryname_keywords'] = Engine_Api::_()->getItem('sitereview_category', $subcategory_id)->getCategorySlug();
                } else {
                    $params['subcategoryname_keywords'] = $meta_keywords;
                }

                $subsubcategory_id = $request->getParam('subsubcategory_id', null);

                if (!empty($subsubcategory_id)) {
                    $meta_title = $tableCategory->getCategory($subsubcategory_id)->meta_title;
                    if (empty($meta_title)) {
                        $params['subsubcategoryname'] = Engine_Api::_()->getItem('sitereview_category', $subsubcategory_id)->getCategorySlug();
                    } else {
                        $params['subsubcategoryname'] = $meta_title;
                    }
                    $meta_description = $tableCategory->getCategory($subsubcategory_id)->meta_description;
                    if (!empty($meta_description))
                        $params['description'] = $meta_description;

                    $meta_keywords = $tableCategory->getCategory($subsubcategory_id)->meta_keywords;
                    if (empty($meta_keywords)) {
                        $params['subsubcategoryname_keywords'] = Engine_Api::_()->getItem('sitereview_category', $subsubcategory_id)->getCategorySlug();
                    } else {
                        $params['subsubcategoryname_keywords'] = $meta_keywords;
                    }
                }
            }
            //
        }

//    $params['default_site_title'] = '';
//    if (!empty($category_id)) {
//        $params['default_title'] = '';
//    }
        //SET META TITLE
        Engine_Api::_()->sitereview()->setMetaTitles($params);

        //SET META TITLE
        Engine_Api::_()->sitereview()->setMetaDescriptionsBrowse($params);

        //GET LOCATION
        if (isset($_GET['location']) && !empty($_GET['location'])) {
            $params['location'] = $_GET['location'];
        }

        //GET TAG
        if (isset($_GET['tag']) && !empty($_GET['tag'])) {
            $params['tag'] = $_GET['tag'];
        }

        //GET TAG
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $params['search'] = $_GET['search'];
        }

        //GET LISTING TITLE
        $params['listing_type_title'] = $listingTypeTable->getListingTypeColumn($listingtype_id, 'title_plural');

        $params['page'] = 'browse';

        $searchForm = new Sitereview_Form_Search(array('type' => 'sitereview_listing', 'listingTypeId' => $listingtype_id));
        Zend_Registry::set('Sitereview_Form_Search', $searchForm);

        //SET META KEYWORDS
        Engine_Api::_()->sitereview()->setMetaKeywords($params);

        $this->_helper->content
                ->setContentName("sitereview_index_index_listtype_$listingtype_id")
                ->setNoRender()
                ->setEnabled();
    }

    //NONE USER SPECIFIC METHODS
    public function topRatedAction() {
        //GET PAGE OBJECT
        $listingtype_id = $this->_listingType->listingtype_id;
        $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
        $pageSelect = $pageTable->select()->where('name = ?', "sitereview_index_rated_listtype_$listingtype_id");
        $pageObject = $pageTable->fetchRow($pageSelect);
        $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');

        //SET META PARAMS
        $params = array();
        $listing_type_title = '';
        if (empty($pageObject->title)) {
            $listing_type_title = ucfirst($this->_listingType->title_plural);
            $params['default_title'] = $title = Zend_Registry::get('Zend_Translate')->_('Browse Top Rated ' . $listing_type_title);
        }
        $description = '';
        if (!empty($pageObject->description)) {
            //$params['description'] = $description = $pageObject->description;
        } else {
            $listing_type_singular = strtolower($listingTypeTable->getListingTypeColumn($listingtype_id, 'title_singular'));
            $params['description'] = $description = Zend_Registry::get('Zend_Translate')->_('This is the ' . $listing_type_singular . ' browse page.');
        }

        //GET LISTING CATEGORY TABLE
        $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $browseCategories = Zend_Registry::isRegistered('sitereviewBrowseCategories') ? Zend_Registry::get('sitereviewBrowseCategories') : null;

        $category_id = $request->getParam('category_id', null);
        if (empty($browseCategories)) {
            $this->view->categoryError = $this->view->translate("There are no categories found for this page.");
            return;
        }

        if (!empty($category_id)) {
            if ($listing_type_title)
                $params['listing_type_title'] = $title = $listing_type_title;
            $meta_title = $tableCategory->getCategory($category_id)->meta_title;
            if (empty($meta_title)) {
                $params['categoryname'] = Engine_Api::_()->getItem('sitereview_category', $category_id)->getCategorySlug();
            } else {
                $params['categoryname'] = $meta_title;
            }

            $meta_description = $tableCategory->getCategory($category_id)->meta_description;
            if (!empty($meta_description))
                $params['description'] = $meta_description;

            $meta_keywords = $tableCategory->getCategory($category_id)->meta_keywords;
            if (empty($meta_keywords)) {
                $params['categoryname_keywords'] = Engine_Api::_()->getItem('sitereview_category', $category_id)->getCategorySlug();
            } else {
                $params['categoryname_keywords'] = $meta_keywords;
            }

            $subcategory_id = $request->getParam('subcategory_id', null);

            if (!empty($subcategory_id)) {
                $meta_title = $tableCategory->getCategory($subcategory_id)->meta_title;
                if (empty($meta_title)) {
                    $params['subcategoryname'] = Engine_Api::_()->getItem('sitereview_category', $subcategory_id)->getCategorySlug();
                } else {
                    $params['subcategoryname'] = $meta_title;
                }
                $meta_description = $tableCategory->getCategory($subcategory_id)->meta_description;
                if (!empty($meta_description))
                    $params['description'] = $meta_description;

                $meta_keywords = $tableCategory->getCategory($subcategory_id)->meta_keywords;
                if (empty($meta_keywords)) {
                    $params['subcategoryname_keywords'] = Engine_Api::_()->getItem('sitereview_category', $subcategory_id)->getCategorySlug();
                } else {
                    $params['subcategoryname_keywords'] = $meta_keywords;
                }

                $subsubcategory_id = $request->getParam('subsubcategory_id', null);

                if (!empty($subsubcategory_id)) {
                    $meta_title = $tableCategory->getCategory($subsubcategory_id)->meta_title;
                    if (empty($meta_title)) {
                        $params['subsubcategoryname'] = Engine_Api::_()->getItem('sitereview_category', $subsubcategory_id)->getCategorySlug();
                    } else {
                        $params['subsubcategoryname'] = $meta_title;
                    }
                    $meta_description = $tableCategory->getCategory($subsubcategory_id)->meta_description;
                    if (!empty($meta_description))
                        $params['description'] = $meta_description;

                    $meta_keywords = $tableCategory->getCategory($subsubcategory_id)->meta_keywords;
                    if (empty($meta_keywords)) {
                        $params['subsubcategoryname_keywords'] = Engine_Api::_()->getItem('sitereview_category', $subsubcategory_id)->getCategorySlug();
                    } else {
                        $params['subsubcategoryname_keywords'] = $meta_keywords;
                    }
                }
            }
        }

        //SET META TITLE
        Engine_Api::_()->sitereview()->setMetaTitles($params);

        //SET META TITLE
        Engine_Api::_()->sitereview()->setMetaDescriptionsBrowse($params);

        //GET LOCATION
        if (isset($_GET['location']) && !empty($_GET['location'])) {
            $params['location'] = $_GET['location'];
        }

        //GET TAG
        if (isset($_GET['tag']) && !empty($_GET['tag'])) {
            $params['tag'] = $_GET['tag'];
        }

        //GET TAG
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $params['search'] = $_GET['search'];
        }

        //GET LISTING TITLE
        $params['listing_type_title'] = $listingTypeTable->getListingTypeColumn($listingtype_id, 'title_plural');

        $params['page'] = 'browse';

        //SET META KEYWORDS
        Engine_Api::_()->sitereview()->setMetaKeywords($params);

        $this->_helper->content
                ->setContentName("sitereview_index_top-rated_listtype_$listingtype_id")
                ->setNoRender()
                ->setEnabled();
    }

    //NONE USER SPECIFIC METHODS
    public function homeAction() {

        //GET PAGE OBJECT
        $listingtype_id = $this->_listingType->listingtype_id;
//    $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
//    $pageSelect = $pageTable->select()->where('name = ?', "sitereview_index_home_listtype_$listingtype_id");
//    $pageObject = $pageTable->fetchRow($pageSelect);
        //GET LISTING TITLE
        $params['listing_type_title'] = $this->_listingType->title_plural;
        //SET META KEYWORDS
        Engine_Api::_()->sitereview()->setMetaKeywords($params);
        $this->_helper->content
                //->setContentName($pageObject->page_id)
                ->setContentName("sitereview_index_home_listtype_$listingtype_id")
                ->setNoRender()
                ->setEnabled();
    }

    //ACTION FOR BROWSE LOCATION PAGES.
    public function mapAction() {

        //GET PAGE OBJECT
        $listingtype_id = $this->_listingType->listingtype_id;
        $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
        $pageSelect = $pageTable->select()->where('name = ?', "sitereview_index_map_listtype_$listingtype_id");
        $pageObject = $pageTable->fetchRow($pageSelect);

        //GET LISTING TITLE
        $params['listing_type_title'] = ucfirst($this->_listingType->title_plural);

        //SET META KEYWORDS
        Engine_Api::_()->sitereview()->setMetaKeywords($params);

        $enableLocation = Engine_Api::_()->sitereview()->enableLocation($listingtype_id);

        if (empty($enableLocation)) {
            return $this->_forwardCustom('notfound', 'error', 'core');
        } else {
            $this->_helper->content->setContentName($pageObject->page_id)->setNoRender()->setEnabled();
        }
    }

    //NONE USER SPECIFIC METHODS
    public function categoriesAction() {

        //GET PAGE OBJECT
        $listingtype_id = $this->_getParam('listingtype_id', null);
//    $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
//    $pageSelect = $pageTable->select()->where('name = ?', "sitereview_index_categories");
//    $pageObject = $pageTable->fetchRow($pageSelect);
        //GET LISTING TITLE
        if ($listingtype_id) {
            $siteinfo = $this->view->layout()->siteinfo;
            $titles = $siteinfo['title'];
            $keywords = $siteinfo['keywords'];
            $listing_type_title = $this->_listingType->title_plural;
            if (!empty($titles))
                $titles .= ' - ';
            $titles .= $listing_type_title;
            $siteinfo['title'] = $titles;

            if (!empty($keywords))
                $keywords .= ' - ';
            $keywords .= $listing_type_title;
            $siteinfo['keywords'] = $keywords;

            $this->view->layout()->siteinfo = $siteinfo;
        }

        $this->_helper->content
//            ->setContentName($pageObject->page_id)
                ->setNoRender()
                ->setEnabled();
    }

    //ACTION FOR SHOWING SPONSORED LISTINGS IN WIDGET
    public function homesponsoredAction() {

        //CORE SETTINGS API
        $settings = Engine_Api::_()->getApi('settings', 'core');

        //SEAOCORE API
        $this->view->seacore_api = Engine_Api::_()->seaocore();

        //RETURN THE OBJECT OF LIMIT PER PAGE FROM CORE SETTING TABLE
        $this->view->sponserdSitereviewsCount = $limit_sitereview = $_GET['curnt_limit'];
        $limit_sitereview_horizontal = $limit_sitereview * 2;

        $values = array();
        $values = $this->_getAllParams();
        $listingtype_id = $values['listingtype_id'] = $_GET['listingtype_id'];

        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);

        //GET COUNT
        $totalCount = $_GET['total'];

        //RETRIVE THE VALUE OF START INDEX
        $startindex = $_GET['startindex'];

        if ($startindex > $totalCount) {
            $startindex = $totalCount - $limit_sitereview;
        }

        if ($startindex < 0) {
            $startindex = 0;
        }

        $this->view->sponsoredIcon = $this->_getParam('sponsoredIcon', 1);
        $this->view->showOptions = $this->_getParam('showOptions', array("category", "rating", "review", "compare", "wishlist"));
        $this->view->featuredIcon = $this->_getParam('featuredIcon', 1);
        $this->view->newIcon = $this->_getParam('newIcon', 1);
        //RETRIVE THE VALUE OF BUTTON DIRECTION
        $this->view->direction = $_GET['direction'];
        $values['start_index'] = $startindex;
        $sitereviewTable = Engine_Api::_()->getDbTable('listings', 'sitereview');
        $this->view->totalItemsInSlide = $values['limit'] = $limit_sitereview_horizontal;
//    $this->view->listing_type = $listing_type = $this->_getParam('listing_type', 'all');
        $this->view->popularity = $values['popularity'] = $this->_getParam('popularity', 'creation_date');
        $this->view->fea_spo = $fea_spo = $this->_getParam('fea_spo', null);
        if ($fea_spo == 'featured') {
            $values['featured'] = 1;
        } elseif ($fea_spo == 'newlabel') {
            $values['newlabel'] = 1;
        } elseif ($fea_spo == 'sponsored') {
            $values['sponsored'] = 1;
        } elseif ($fea_spo == 'fea_spo') {
            $values['sponsored_or_featured'] = 1;
        }
        //GET LISTINGS
        $this->view->sitereviews = $sitereviewTable->getListing('', $values);
        $this->view->count = count($this->view->sitereviews);
        $this->view->vertical = $_GET['vertical'];
        $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');
        $this->view->title_truncation = $this->_getParam('title_truncation', 50);
        $this->view->blockHeight = $this->_getParam('blockHeight', 245);
        $this->view->blockWidth = $this->_getParam('blockWidth', 150);
    }

    //ACTION FOR VIEW LISTING PROFILE PAGE
    public function viewAction() {

        //GET VIEWER ID
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //GET LISTING ID AND OBJECT
        $listing_id = $this->_getParam('listing_id');
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $this->_getParam('listing_id'));
        $sitereviewViewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.view.type', false);

        if (empty($sitereview) || empty($sitereviewViewType)) {
            return $this->_forwardCustom('notfound', 'error', 'core');
        }

        //PAGE INTERGRATION PLUGIN PRIVACY WORK
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitepageintegration') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitebusinessintegration') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitegroupintegration') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitestoreintegration')) {

            $listing_view = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitepageint.listing.view', 0);
            $addable_integration = Engine_Api::_()->getApi('settings', 'core')->getSetting('addable.integration', 1);
            if (!empty($listing_view) && $addable_integration != 0) {
                $itemPrivacyCheck = Engine_Api::_()->seaocore()->itemPrivacyCheck($sitereview);
                if ($itemPrivacyCheck) {
                    return $this->_forwardCustom('requireauth', 'error', 'core');
                }
            }
        }

        $listingtype_id = $this->_listingType->listingtype_id;

        //WHO CAN VIEW THE LISTINGS
        if (!$this->_helper->requireAuth()->setAuthParams($sitereview, null, "view_listtype_$listingtype_id")->isValid()) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        //GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }

        //GET LEVEL SETTING
        $can_view = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "view_listtype_$listingtype_id");

        //AUTHORIZATION CHECK
        if ($can_view != 2 && ((time() < strtotime($sitereview->creation_date) || !empty($sitereview->draft) || empty($sitereview->approved)) && ($sitereview->owner_id != $viewer_id) || (Engine_Api::_()->sitereview()->hasPackageEnable() && (isset($sitereview->expiration_date) && $sitereview->expiration_date !== "2250-01-01 00:00:00" && strtotime($sitereview->expiration_date) < time())))) {
            return $this->_forwardCustom('notfound', 'error', 'core');
        }

        if ($can_view != 2 && ($sitereview->owner_id != $viewer_id)) {
            $reviewApi = Engine_Api::_()->sitereview();
            $expirySettings = $reviewApi->expirySettings($listingtype_id);
            if ($expirySettings == 2) {
                $approveDate = $reviewApi->adminExpiryDuration($listingtype_id);
                if ($approveDate > $sitereview->approved_date) {
                    return $this->_forwardCustom('requireauth', 'error', 'core');
                }
            }
        }

        //WE WILL GENERATE ACTIVITY FEED WITHIN 2 DAYS OF PUBLISH DATE
        if ($this->_listingType->edit_creationdate) {
            $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
//        $now = new DateTime(date("Y-m-d H:i:s"));
//        $ref = new DateTime($view->locale()->toDate($sitereview->creation_date));
            $now = new DateTime(date("Y-m-d H:i:s"));
            $ref = new DateTime($sitereview->creation_date);
            $diff = $now->diff($ref);

            $action_id = Engine_Api::_()->getDbtable('actions', 'activity')->select()->from('engine4_activity_actions', 'action_id')->where('type = ?', "sitereview_new_listtype_$sitereview->listingtype_id")->where('object_type = ?', 'sitereview_listing')->where('object_id = ?', $sitereview->listing_id)->query()->fetchColumn();

            if (empty($action_id) && $sitereview->draft == 0 && (time() >= strtotime($sitereview->creation_date) && $diff->days <= 2)) {
                $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($sitereview->getOwner(), $sitereview, 'sitereview_new_listtype_' . $sitereview->listingtype_id);

                if ($action != null) {
                    Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sitereview);
                }
            }
        }
        //SET SITEREVIEW SUBJECT
        Engine_Api::_()->core()->setSubject($sitereview);
//OPEN TAB IN NEW PAGE
        if ($this->renderWidgetCustom("sitereview_index_view_listtype_$listingtype_id"))
            return;
        //ADD CSS
        $this->view->headLink()
                ->prependStylesheet($this->view->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
        $this->view->headLink()
                ->prependStylesheet($this->view->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereviewprofile.css');
        $this->view->headScript()
                ->appendFile($this->view->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/core.js');



        if ($this->_listingType->profile_tab) {
            $this->view->headLink()
                    ->prependStylesheet($this->view->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview_tabs.css');
            $script = <<<EOF
      en4.core.runonce.add(function() {
          $$('.tabs_alt').addClass('sr_main_tabs_alt');
      });
EOF;
            $this->view->headScript()->appendScript($script);
        }

        //INCREMENT IN NUMBER OF VIEWS
        if (!$sitereview->getOwner()->isSelf($viewer)) {
            $sitereview->view_count++;
            $sitereview->save();
            $params = array();
            $params['resource_id'] = $sitereview->listing_id;
            $params['resource_type'] = $sitereview->getType();
            $params['viewer_id'] = 0;
            $params['type'] = 'editor';
            $isEditorReviewed = Engine_Api::_()->getDbTable('reviews', 'sitereview')->canPostReview($params);
            if ($isEditorReviewed) {
                $review = Engine_Api::_()->getItem('sitereview_review', $isEditorReviewed);
                $review->view_count++;
                $review->save();
            }
        }

        //SET LISTING VIEW DETAILS
        if (!empty($viewer_id)) {
            Engine_Api::_()->getDbtable('vieweds', 'sitereview')->setVieweds($listing_id, $viewer_id);
        }

        //GET SITEREVIEW OWNER LEVEL ID
        $owner_level_id = Engine_Api::_()->getItem('user', $sitereview->owner_id)->level_id;

        //PROFILE STYLE IS ALLOWED OR NOT
        $style_perm = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('sitereview_listing', $owner_level_id, "style_listtype_$listingtype_id");
        if ($style_perm) {

            //GET STYLE TABLE
            $tableStyle = Engine_Api::_()->getDbtable('styles', 'core');

            //MAKE QUERY
            $getStyle = $tableStyle->select()
                    ->from($tableStyle->info('name'), array('style'))
                    ->where('type = ?', 'sitereview_listing')
                    ->where('id = ?', $sitereview->getIdentity())
                    ->query()
                    ->fetchColumn();

            if (!empty($getStyle)) {
                $this->view->headStyle()->appendStyle($getStyle);
            }
        }

        if (null != ($tab = $this->_getParam('tab'))) {
            //provide widgties page
            $friend_tab_function = <<<EOF
                                        var tab_content_id_sitestore = "$tab";
                                        this.onload = function()
                                        {
                                                tabContainerSwitch($('main_tabs').getElement('.tab_' + tab_content_id_sitestore));
                                        }
EOF;
            $this->view->headScript()->appendScript($friend_tab_function);
        }

        //GET PAGE OBJECT
        //$pageTable = Engine_Api::_()->getDbtable('pages', 'core');
        //$pageSelect = $pageTable->select()->where('name = ?', "sitereview_index_view_listtype_$listingtype_id");
        //$pageObject = $pageTable->fetchRow($pageSelect);
        //SET META PARAMS
        $params = array();

        //GET LISTING CATEGORY TABLE
        //$tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');

        $category_id = $sitereview->category_id;
        if (!empty($category_id)) {

            $params['categoryname'] = Engine_Api::_()->getItem('sitereview_category', $category_id)->getCategorySlug();

            $subcategory_id = $sitereview->subcategory_id;

            if (!empty($subcategory_id)) {

                $params['subcategoryname'] = ucfirst(Engine_Api::_()->getItem('sitereview_category', $subcategory_id)->getCategorySlug());

                $subsubcategory_id = $sitereview->subsubcategory_id;

                if (!empty($subsubcategory_id)) {

                    $params['subsubcategoryname'] = Engine_Api::_()->getItem('sitereview_category', $subsubcategory_id)->getCategorySlug();
                }
            }
        }

        //GET LOCATION
        if (!empty($sitereview->location) && $this->_listingType->location) {
            $params['location'] = $sitereview->location;
        }

        //GET KEYWORDS
        $params['keywords'] = Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getColumnValue($sitereview->getIdentity(), 'keywords');

        //SET META KEYWORDS
        Engine_Api::_()->sitereview()->setMetaKeywords($params);

        if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
            Zend_Registry::set('setFixedCreationFormBack', 'Back');
        }

        //NAVIGATION WORK FOR FOOTER.(DO NOT DISPLAY NAVIGATION IN FOOTER ON VIEW PAGE.)
        if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
            if (!Zend_Registry::isRegistered('sitemobileNavigationName')) {
                Zend_Registry::set('sitemobileNavigationName', 'setNoRender');
            }
        }

        $this->_helper->content
                //->setContentName($pageObject->page_id)
                ->setContentName("sitereview_index_view_listtype_$listingtype_id")
                ->setNoRender()
                ->setEnabled();
    }

    public function getDefaultListingAction() {
        $isAjax = $this->_getParam('isAjax', null);
        $type = $this->_getParam('type', null);
        $listing_id = $this->_getParam('listing_id', null);
        $isListingTypeModEnabled = false; //Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype');
        if (!empty($isListingTypeModEnabled)) {
            $flagValue = '';
            $tempStr = null;
            $this->view->getClassName = $type;
            $getListingOrder = $this->_getParam('getListingOrder', null);
            $tempFlagArray = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereviewltype.cat.attempt', false);
            $sitereviewGetAttemptType = Zend_Registry::isRegistered('sitereviewGetAttemptType') ? Zend_Registry::get('sitereviewGetAttemptType') : null;
            $tempFlagArray = !empty($tempFlagArray) ? @unserialize($tempFlagArray) : array();
            $getLtypeAttempt = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.ltype.attempt', false);
            $sitereviewViewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.viewtype', false);
            $getFieldsType = Engine_Api::_()->sitereview()->getFieldsType('sitereviewlistingtype');
            $sitereviewlistingtypeLsettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereviewlistingtype.lsettings', false);
            $sitereviewLsettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.lsettings', false);
            $sitereviewViewAttempt = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.view.attempt', false);
            $sitereviewCatListing = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.cat.listing', false);
            $sitereviewViewAttempt = !empty($sitereviewGetAttemptType) ? $sitereviewGetAttemptType : @convert_uudecode($sitereviewViewAttempt);

            if (!empty($getListingOrder) && $getListingOrder == 1) {
                $this->view->getListingOrder = 0;
                $this->view->defaultListingView = false;
            }
            if (!empty($getListingOrder) && $getListingOrder == 2) {
                $this->view->getListingOrder = 1;
                $this->view->defaultListingView = true;
            }
            if (!empty($getListingOrder) && $getListingOrder == 3) {
                $this->view->getListingOrder = 2;
                $this->view->defaultListingView = true;
            }
            if (!empty($getListingOrder) && $getListingOrder == 4) {
                $this->view->getListingOrder = 3;
                $this->view->defaultListingView = false;
            }
            if (!empty($getListingOrder) && $getListingOrder == 5) {
                $this->view->getListingOrder = 4;
                $this->view->defaultListingView = true;
            }

            $tempGetFinalNumber = $sitereviewSponsoredOrder = $sitereviewFeaturedOrder = 0;
            for ($tempFlag = 0; $tempFlag < strlen($sitereviewLsettings); $tempFlag++) {
                $sitereviewFeaturedOrder += @ord($sitereviewLsettings[$tempFlag]);
            }

            for ($tempFlag = 0; $tempFlag < strlen($sitereviewViewAttempt); $tempFlag++) {
                $sitereviewSponsoredOrder += @ord($sitereviewViewAttempt[$tempFlag]);
            }

            if (!empty($listing_id)) {
                $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
                if (empty($sitereview)) {
                    $this->view->setListingType = false;
                } else {
                    $this->view->setListingType = $sitereview;
                }
            }

            $tempGetFinalNumber = $sitereviewSponsoredOrder . $sitereviewFeaturedOrder;
            $tempGetFinalNumber = (int) $tempGetFinalNumber;
            $tempGetFinalNumber += $getLtypeAttempt;
            $tempGetFinalNumber = (string) $tempGetFinalNumber;

            for ($tempFlag = 0; $tempFlag < 6; $tempFlag++) {
                $tempStr .= $tempGetFinalNumber[$tempFlag];
            }

            foreach ($tempFlagArray as $key) {
                $flagValue .= $sitereviewlistingtypeLsettings[$key];
            }

            if (!empty($sitereviewCatListing) || !empty($sitereviewViewType) || (!empty($tempStr) && !empty($flagValue) && ($tempStr == $flagValue))) {
                $this->view->getListingType = true;
            } else {
                $getHostTypeArray = array();
                $requestListType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.request.listtype', false);
                if (!empty($requestListType)) {
                    $getHostTypeArray = @unserialize($requestListType);
                }
                $getHostTypeArray[] = str_replace("www.", "", strtolower($_SERVER['HTTP_HOST']));
                $getHostTypeArray = @serialize($getHostTypeArray);
                Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.request.listtype', $getHostTypeArray);

                $getReviewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.get.ltype', false);
                if (empty($getReviewType)) {
                    $TempLtype[] = '1';
                    $TempLtype[] = str_replace("www.", "", strtolower($_SERVER['HTTP_HOST']));
                    $TempLtype[] = date("Y-m-d H:i:s");
                    $TempLtype[] = $_SERVER['REQUEST_URI'];
                    $TempLtype = @serialize($TempLtype);
                    Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.get.ltype', $TempLtype);
                }

//        foreach ($getFieldsType as $key => $value) {
//          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
//        }
                $this->view->getListingType = false;
            }
        }
        $this->view->getListingType = true;
    }

    //ACTION FOR MANAGING THE LISTINGS
    public function manageAction() {

        //ONLY LOGGED IN USER CAN VIEW THIS PAGE
        if (!$this->_helper->requireUser()->isValid())
            return;

        //GET VIEWER
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $this->view->autoContentLoad = $isappajax = $this->_getParam('isappajax', false);
        //GET LISTING TYPE ID
        $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;
        $this->view->listingtypeArray = $listingtypeArray = $this->_listingType;

        $this->view->listing_singular_uc = $listing_singular_uc = ucfirst($listingtypeArray->title_singular);
        $this->view->listing_singular_lc = $listing_singular_lc = strtolower($listingtypeArray->title_singular);
        $this->view->listing_plural_lc = $listing_plural_lc = strtolower($listingtypeArray->title_plural);
        $this->view->listing_plural_uc = $listing_plural_uc = ucfirst($listingtypeArray->title_plural);
        $this->view->claimLink = $listingtypeArray->claimlink;
        $this->view->claimListing = Engine_Api::_()->getDbtable('claims', 'sitereview')->getMyClaimListings($viewer_id, $listingtype_id);

        //CREATION PRIVACY CHECK
        if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "create_listtype_$listingtype_id")->isValid())
            return;

        //SEND LISTING TYPE TITLE TO TPL
        $this->view->title = ucfirst($this->_listingType->title_singular);

        //GET NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_main_listtype_$listingtype_id");

        //GET EDIT AND DELETE SETTINGS
        $this->view->can_edit = $this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "edit_listtype_$listingtype_id")->checkRequire();
        $this->view->can_delete = $this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "delete_listtype_$listingtype_id")->checkRequire();

        //ABLE TO UPLOAD VIDEO OR NOT        
        $this->view->allowed_upload_video = 1;
        $allowed_upload_videoEnable = Engine_Api::_()->sitereview()->enableVideoPlugin();
        if (empty($allowed_upload_videoEnable))
            $this->view->allowed_upload_video = 0;

        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video', 1)) {
            //CHECK FOR SOCIAL ENGINE CORE VIDEO PLUGIN
            $allowed_upload_video_video = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'create');
            if (empty($allowed_upload_video_video))
                $this->view->allowed_upload_video = 0;
        }

        $allowed_upload_video = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "video_listtype_$listingtype_id");
        if (empty($allowed_upload_video))
            $this->view->allowed_upload_video = 0;

        //MAKE FORM
        $this->view->form = $form = new Sitereview_Form_Search();
        $form->removeElement('show');

        //PROCESS FORM
        unset($form->getElement('orderby')->options['']);
        if ($form->isValid($this->_getAllParams())) {
            $values = $form->getValues();
        } else {
            $values = array();
        }

        //MAKE DATA ARRAY
        $values['user_id'] = $viewer_id;
        $values['type'] = 'manage';
        $values['listingtype_id'] = $listingtype_id;
//    $values['orderby'] = 'listing_id';
        $values['orderby'] = $this->_getParam('orderby', 'listing_id');
        if (empty($values['page']) && $this->_getParam('page', false))
            $values['page'] = $this->_getParam('page', false);
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $params = $request->getParams();

        $this->view->category_id = $values['category_id'] = isset($params['category_id']) ? $params['category_id'] : 0;
        $this->view->subcategory_id = $values['subcategory_id'] = isset($params['subcategory_id']) ? $params['subcategory_id'] : 0;
        $this->view->subsubcategory_id = $values['subsubcategory_id'] = isset($params['subsubcategory_id']) ? $params['subsubcategory_id'] : 0;

        //GET CUSTOM FIELD VALUES
        $customFieldValues = array_intersect_key($values, $form->getFieldElements());

        //GET PAGINATOR
        $this->view->paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->getSitereviewsPaginator($values, $customFieldValues);
        $this->view->paginator->setItemCountPerPage(10);
        $this->view->paginator->setCurrentPageNumber($values['page']);
        $this->view->current_count = $this->view->paginator->getTotalItemCount();

        $this->view->formValues = $values;
        $this->view->page = !empty($values['page']) ? $values['page'] : 1;
        $this->view->totalPages = ceil(($this->view->current_count) / 10);
        $form->populate($values);

        $categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 0, $listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name', 'category_slug'));
        $categories_slug[0] = "";
        if (count($categories) != 0) {
            foreach ($categories as $category) {
                $categories_slug[$category->category_id] = $category->getCategorySlug();
            }
        }
        $this->view->categories_slug = $categories_slug;

        //MAXIMUM ALLOWED LISTINGS
        $this->view->quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "max_listtype_$listingtype_id");

        $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');

        //if (Engine_API::_()->seaocore()->isSiteMobileModeEnabled() && !$isappajax) {
        $this->_helper->content
                // ->setContentName($pageObject->page_id)
                ->setContentName("sitereview_index_manage_listtype_$listingtype_id")
                ->setEnabled();
        //}
    }

    //ACTION FOR CREATING A NEW LISTING
    public function createAction() {
        //ONLY LOGGED IN USER CAN CREATE
        if (!$this->_helper->requireUser()->isValid())
            return;

        //GET LISTING TYPE ID
        $package_id = $this->_getParam('id', 0);
        $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;
        $this->view->listing_singular_lc = strtolower($this->_listingType->title_singular);
        $this->view->listing_singular_uc = $listing_singular_uc = ucfirst($this->_listingType->title_singular);
        $this->view->listing_plural_lc = strtolower($this->_listingType->title_plural);
        $this->view->show_editor = $this->_listingType->show_editor;

        //SITEMOBILE_MODULE_NOT_SUPPORT_DESC_FOR_SOMEPAGES
        //if(!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
        $this->_helper->content
                ->setContentName("sitereview_index_create_listtype_$listingtype_id")
                //->setNoRender()
                ->setEnabled();

        //}
        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $this->view->level_id = $viewer->level_id;
        global $sitereviewGetCategory;
        global $sitereview_is_approved;
        //CHECK FOR CREATION PRIVACY
        if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "create_listtype_$listingtype_id")->isValid())
            return;

        //GET DEFAULT PROFILE TYPE ID
        $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sitereview')->defaultProfileId();

        //SEND LISTING TYPE TITLE TO TPL
        $this->view->title = $this->_listingType->title_plural;

        //GET NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_main_listtype_$listingtype_id");

        //MAKE FORM
        $this->view->form = $form = new Sitereview_Form_Create(array('defaultProfileId' => $defaultProfileId));

        if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
            //CLEAR CACHE ON FORM DISPLAY, ALL FIELDS SHOULD BE EMPTY.(FOR SITEMOBILE)
            $this->view->clear_cache = true;
            $this->view->noDomCache = true;
        }
        if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
            Zend_Registry::set('setFixedCreationForm', true);
            Zend_Registry::set('setFixedCreationHeaderTitle', "Post New $listing_singular_uc");
            Zend_Registry::set('setFixedCreationHeaderSubmit', 'Save');
            $this->view->form->setAttrib('name', 'sitereviews_create');
            Zend_Registry::set('setFixedCreationFormId', '#sitereviews_create');
            $this->view->form->removeElement('execute');
            $this->view->form->removeElement('cancel');
            $form->setTitle('');
        }

        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {

            $this->view->allow_review = $this->_listingType->reviews;
            $this->view->overview = $this->_listingType->overview;
            $this->view->wishlist = $this->_listingType->wishlist;
            $this->view->location = $this->_listingType->location;
            $this->view->package_description = $this->_listingType->package_description;
            $this->view->viewer = Engine_Api::_()->user()->getViewer();

            //REDIRECT
            $package_id = $this->_getParam('id');
            if (empty($package_id)) {
                return $this->_forwardCustom('notfound', 'error', 'core');
            }
            $this->view->package = $package = Engine_Api::_()->getItemTable('sitereviewpaidlisting_package')->fetchRow(array('package_id = ?' => $package_id, 'listingtype_id = ?' => $listingtype_id, 'enabled = ?' => '1'));
            if (empty($this->view->package)) {
                return $this->_forwardCustom('notfound', 'error', 'core');
            }

            if (!empty($package->level_id) && !in_array($viewer->level_id, explode(",", $package->level_id))) {
                return $this->_forwardCustom('notfound', 'error', 'core');
            }
        } elseif (isset($this->_listingType->package) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
            $package_id = Engine_Api::_()->getItemtable('sitereviewpaidlisting_package')->fetchRow(array('listingtype_id = ?' => $listingtype_id, 'defaultpackage = ?' => 1))->package_id;
        }

        //GET VIEWER
        $listValues = array();

        //GET TINYMCE SETTINGS
        $this->view->upload_url = "";
        $albumEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album');
        if (Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create') && $albumEnabled) {
            $this->view->upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'upload-photo'), 'sitereview_general_listtype_' . $this->_listingType->listingtype_id, true);
        }

        $orientation = $this->view->layout()->orientation;
        if ($orientation == 'right-to-left') {
            $this->view->directionality = 'rtl';
        } else {
            $this->view->directionality = 'ltr';
        }

        $local_language = $this->view->locale()->getLocale()->__toString();
        $local_language = explode('_', $local_language);
        $this->view->language = $local_language[0];

        //COUNT SITEREVIEW CREATED BY THIS USER AND GET ALLOWED COUNT SETTINGS
        $values['user_id'] = $viewer_id;
        $values['listingtype_id'] = $listingtype_id;
        $paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->getSitereviewsPaginator($values);
        $this->view->current_count = $paginator->getTotalItemCount();
        $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "max_listtype_$listingtype_id");

        // CUSTOM WORK
        $this->view->packageQuota = 0;
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            $tempPackageRow = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $this->_getParam('id', null));
            $this->view->packageQuota = $packageQuota = $tempPackageRow->max_listing;
            $this->view->packageListingCount = $tempPackageRow->getListingCount();
        }
        // CUSTOM WORK

        $sitereviewLsettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.lsettings', false);
        $sitereviewGetAttemptType = Zend_Registry::isRegistered('sitereviewGetAttemptType') ? Zend_Registry::get('sitereviewGetAttemptType') : null;
        $sitereviewListingTypeOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.listingtype.order', false);
        $sitereviewProfileOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.profile.order', false);
        $sitereviewViewAttempt = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.view.attempt', false);
        $sitereviewViewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.viewtype', false);
        $sitereviewViewAttempt = !empty($sitereviewGetAttemptType) ? $sitereviewGetAttemptType : @convert_uudecode($sitereviewViewAttempt);
        $this->view->category_count = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 1, $listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id'));
        $sitereviewCategoryType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.category.type', false);

        $sitereview_host = str_replace("www.", "", strtolower($_SERVER['HTTP_HOST']));

        $this->view->sitereview_render = 'sitereview_form';
        $this->view->expiry_setting = $expiry_setting = Engine_Api::_()->sitereview()->expirySettings($listingtype_id);
        $tempGetFinalNumber = $sitereviewSponsoredOrder = $sitereviewFeaturedOrder = 0;
        for ($tempFlag = 0; $tempFlag < strlen($sitereviewLsettings); $tempFlag++) {
            $sitereviewFeaturedOrder += @ord($sitereviewLsettings[$tempFlag]);
        }

        for ($tempFlag = 0; $tempFlag < strlen($sitereviewViewAttempt); $tempFlag++) {
            $sitereviewSponsoredOrder += @ord($sitereviewViewAttempt[$tempFlag]);
        }
        $sitereviewListingTypeOrder += $sitereviewFeaturedOrder + $sitereviewSponsoredOrder;

        // Check method/data validitiy
        if (!$this->getRequest()->isPost()) {
            return;
        }

        $photoOrder = array_search('photo', array_keys($form->getElements())) - 1;

        $tempPost = $this->getRequest()->getPost();
        if (isset($form->photo)) {
            $tempForm = $form;
            $photoEl = $form->photo;

            if (isset($tempPost['photo'])) {
                unset($tempPost['photo']);
                $form->removeElement('photo');
            }
            if (!$form->isValid($tempPost)) {
                $form->addElement($photoEl->setOrder($photoOrder));
                return;
            }
        } else {
            if (!$form->isValid($tempPost)) {
                return;
            }
        }


        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.duplicatetitle', 1)) {
            $isListingExists = Engine_Api::_()->getDbTable('listings', 'sitereview')->getListingColumn(array('listingtype_id' => $listingtype_id, 'title' => $_POST['title']));

            if ($isListingExists) {
                $error = $this->view->translate("Please choose the different listing title as listing with same title already exists.");
                $error = Zend_Registry::get('Zend_Translate')->_($error);

                $form->getDecorator('errors')->setOption('escape', false);
                $form->addError($error);
                return;
            }
        }

        //CATEGORY IS REQUIRED FIELD
        if (empty($_POST['category_id']) || empty($sitereviewGetCategory)) {
            $error = $this->view->translate('Please complete Category field - it is required.');
            $error = Zend_Registry::get('Zend_Translate')->_($error);

            $form->getDecorator('errors')->setOption('escape', false);
            $form->addError($error);
            return;
        }
        $getFieldsType = Engine_Api::_()->sitereview()->getFieldsType('sitereviewlistingtype');
        $getListingRevType = Engine_Api::_()->getApi('listingType', 'sitereview')->getListingReviewType();
        $table = Engine_Api::_()->getItemTable('sitereview_listing');
        $db = $table->getAdapter();
        $db->beginTransaction();
        $user_level = $viewer->level_id;
        try {
            //Create sitereview
            if (!Engine_Api::_()->sitereview()->hasPackageEnable()) {
                $values = array_merge($form->getValues(), array(
                    'listingtype_id' => $listingtype_id,
                    'owner_type' => $viewer->getType(),
                    'owner_id' => $viewer_id,
                    'featured' => Engine_Api::_()->authorization()->getPermission($user_level, 'sitereview_listing', "featured_listtype_$listingtype_id"),
                    'sponsored' => Engine_Api::_()->authorization()->getPermission($user_level, 'sitereview_listing', "sponsored_listtype_$listingtype_id"),
                    'approved' => Engine_Api::_()->authorization()->getPermission($user_level, 'sitereview_listing', "approved_listtype_$listingtype_id")
                ));
            } else {
                $values = array_merge($form->getValues(), array(
                    'listingtype_id' => $listingtype_id,
                    'owner_type' => $viewer->getType(),
                    'owner_id' => $viewer_id,
                    'featured' => $package->featured,
                    'sponsored' => $package->sponsored
                ));

                if ($package->isFree()) {
                    $values['approved'] = $package->approved;
                } else
                    $values['approved'] = 0;
            }

            if (empty($values['listing_info'])) {
                $values = $listValues;
            } else {
                unset($values['listing_info']);
            }

            if (empty($values['subcategory_id'])) {
                $values['subcategory_id'] = 0;
            }

            if (empty($values['subsubcategory_id'])) {
                $values['subsubcategory_id'] = 0;
            }

            if (!empty($values['search']) && (empty($sitereviewViewType))) {
                if (!empty($sitereviewProfileOrder) && !empty($sitereviewListingTypeOrder) && ($sitereviewListingTypeOrder != $sitereviewProfileOrder)) {
                    $getHostTypeArray = array();
                    $requestListType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.request.listtype', false);
                    if (!empty($requestListType)) {
                        $getHostTypeArray = @unserialize($requestListType);
                    }
                    $getHostTypeArray[] = str_replace("www.", "", strtolower($_SERVER['HTTP_HOST']));
                    $getHostTypeArray = @serialize($getHostTypeArray);
                    Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.request.listtype', $getHostTypeArray);

                    $getReviewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.get.ltype', false);
                    if (empty($getReviewType)) {
                        $TempLtype[] = '2';
                        $TempLtype[] = str_replace("www.", "", strtolower($_SERVER['HTTP_HOST']));
                        $TempLtype[] = date("Y-m-d H:i:s");
                        $TempLtype[] = $_SERVER['REQUEST_URI'];
                        $TempLtype = @serialize($TempLtype);
                        Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.get.ltype', $TempLtype);
                    }
                    $values['search'] = 0;
                }
            }

            if (empty($sitereviewCategoryType)) {
                return;
            }

            if ($expiry_setting == 1 && $values['end_date_enable'] == 1) {
                // Convert times
                $oldTz = date_default_timezone_get();
                date_default_timezone_set($viewer->timezone);
                $end = strtotime($values['end_date']);
                date_default_timezone_set($oldTz);
                $values['end_date'] = date('Y-m-d H:i:s', $end);
            } elseif (isset($values['end_date'])) {
                unset($values['end_date']);
            }

            if (Engine_Api::_()->sitereview()->listBaseNetworkEnable()) {
                if (isset($values['networks_privacy']) && !empty($values['networks_privacy'])) {
                    if (in_array(0, $values['networks_privacy'])) {
                        unset($values['networks_privacy']);
                    }
                }
            }

            $sitereview = $table->createRow();
            $sitereview->setFromArray($values);

            if ($sitereview->approved) {
                $sitereview->approved_date = date('Y-m-d H:i:s');
            }

            //START PACKAGE WORK
            if (!empty($sitereview->approved)) {
                if (isset($sitereview->pending))
                    $sitereview->pending = 0;
                $sitereview->approved_date = date('Y-m-d H:i:s');
                if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
//            $sitereview->pending = 0;
                    $expirationDate = $package->getExpirationDate();
                    if (!empty($expirationDate))
                        $sitereview->expiration_date = date('Y-m-d H:i:s', $expirationDate);
                    else
                        $sitereview->expiration_date = '2250-01-01 00:00:00';
                }
            }
            //END PACKAGE WORK

            $sitereview->save();
            if (isset($sitereview->package_id))
                $sitereview->package_id = $package_id;
            $listing_id = $sitereview->listing_id;

            if ($this->_listingType->edit_creationdate && !$sitereview->draft) {
                $oldTz = date_default_timezone_get();
                date_default_timezone_set($viewer->timezone);
                $creation = strtotime($values['creation_date']);
                date_default_timezone_set($oldTz);
                $sitereview->creation_date = date('Y-m-d H:i:s', $creation);
                $sitereview->save();
            }
                $hasparent = false;
                $object = null;
            //START INTERGRATION EXTENSION WORK
            //START PAGE INTEGRATION WORK
            $page_id = $this->_getParam('page_id');
            if (!empty($page_id)) {
                $moduleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitepageintegration');
                if (!empty($moduleEnabled)) {
                    $contentsTable = Engine_Api::_()->getDbtable('contents', 'sitepageintegration');
                    $row = $contentsTable->createRow();
                    $row->owner_id = $viewer_id;
                    $row->resource_owner_id = $sitereview->owner_id;
                    $row->page_id = $page_id;
                    $row->resource_type = 'sitereview_listing';
                    $row->resource_id = $sitereview->listing_id;
                    $row->save();
                }
                $hasparent = true;
                $object = Engine_Api::_()->getItem('sitepage_page', $page_id);
                if (Engine_Api::_()->sitepage()->isPageOwner($object) && Engine_Api::_()->sitepage()->isFeedTypePageEnable()) {
                    $activityFeedType = 'sitereview_admin_new_module_listtype_' . $listingtype_id;
                } elseif ($object->all_post || Engine_Api::_()->sitepage()->isPageOwner($object)) {
                    $activityFeedType = 'sitereview_new_module_listtype_' . $listingtype_id;
                }
            }
            //END PAGE INTEGRATION WORK
            //START BUSINESS INTEGRATION WORK
            $business_id = $this->_getParam('business_id');
            if (!empty($business_id)) {
                $moduleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitebusinessintegration');
                if (!empty($moduleEnabled)) {
                    $contentsTable = Engine_Api::_()->getDbtable('contents', 'sitebusinessintegration');
                    $row = $contentsTable->createRow();
                    $row->owner_id = $viewer_id;
                    $row->resource_owner_id = $sitereview->owner_id;
                    $row->business_id = $business_id;
                    $row->resource_type = 'sitereview_listing';
                    $row->resource_id = $sitereview->listing_id;
                    $row->save();
                }
                $hasparent = true;
                $object = Engine_Api::_()->getItem('sitebusiness_business', $business_id);
                if (Engine_Api::_()->sitebusiness()->isBusinessOwner($object) && Engine_Api::_()->sitebusiness()->isFeedTypeBusinessEnable()) {
                    $activityFeedType = 'sitereview_admin_new_module_listtype_' . $listingtype_id;
                } elseif ($object->all_post || Engine_Api::_()->sitebusiness()->isBusinessOwner($object)) {
                    $activityFeedType = 'sitereview_new_module_listtype_' . $listingtype_id;
                }
            }
            //END BUSINESS INTEGRATION WORK
            //START GROUP INTEGRATION WORK
            $group_id = $this->_getParam('group_id');
            if (!empty($group_id)) {
                $moduleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitegroupintegration');
                if (!empty($moduleEnabled)) {
                    $contentsTable = Engine_Api::_()->getDbtable('contents', 'sitegroupintegration');
                    $row = $contentsTable->createRow();
                    $row->owner_id = $viewer_id;
                    $row->resource_owner_id = $sitereview->owner_id;
                    $row->group_id = $group_id;
                    $row->resource_type = 'sitereview_listing';
                    $row->resource_id = $sitereview->listing_id;
                    $row->save();
                }
                $hasparent = true;
                $object = Engine_Api::_()->getItem('sitegroup_group', $group_id);
                if (Engine_Api::_()->sitegroup()->isGroupOwner($object) && Engine_Api::_()->sitegroup()->isFeedTypeGroupEnable()) {
                    $activityFeedType = 'sitereview_admin_new_module_listtype_' . $listingtype_id;
                } elseif ($object->all_post || Engine_Api::_()->sitegroup()->isGroupOwner($object)) {
                    $activityFeedType = 'sitereview_new_module_listtype_' . $listingtype_id;
                }
            }
            //END GROUP INTEGRATION WORK
            //START STORE INTEGRATION WORK
            $store_id = $this->_getParam('store_id');
            if (!empty($store_id)) {
                $moduleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitestoreintegration');
                if (!empty($moduleEnabled)) {
                    $contentsTable = Engine_Api::_()->getDbtable('contents', 'sitestoreintegration');
                    $row = $contentsTable->createRow();
                    $row->owner_id = $viewer_id;
                    $row->resource_owner_id = $sitereview->owner_id;
                    $row->store_id = $store_id;
                    $row->resource_type = 'sitereview_listing';
                    $row->resource_id = $sitereview->listing_id;
                    $row->save();
                }
                $hasparent = true;
                $object = Engine_Api::_()->getItem('sitestore_store', $store_id);
            }
            //END STORE INTEGRATION WORK
            //END INTERGRATION EXTENSION WORK
            //SET PHOTO
            if (!empty($values['photo'])) {
                $sitereview->setPhoto($form->photo);
                $albumTable = Engine_Api::_()->getDbtable('albums', 'sitereview');
                $album_id = $albumTable->update(array('photo_id' => $sitereview->photo_id), array('listing_id = ?' => $sitereview->listing_id));
            }

            //ADDING TAGS
            $keywords = '';
            if (isset($values['tags']) && !empty($values['tags'])) {
                $tags = preg_split('/[,]+/', $values['tags']);
                $tags = array_filter(array_map("trim", $tags));
                $sitereview->tags()->addTagMaps($viewer, $tags);

                foreach ($tags as $tag) {
                    $keywords .= " $tag";
                }
            }

            //SAVE CUSTOM VALUES AND PROFILE TYPE VALUE
            $customfieldform = $form->getSubForm('fields');
            $customfieldform->setItem($sitereview);
            $customfieldform->saveValues();

            $categoryIds = array();
            $categoryIds[] = $sitereview->category_id;
            $categoryIds[] = $sitereview->subcategory_id;
            $categoryIds[] = $sitereview->subsubcategory_id;
            $sitereview->profile_type = Engine_Api::_()->getDbTable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');

            //NOT SEARCHABLE IF SAVED IN DRAFT MODE
            if (!empty($sitereview->draft)) {
                $sitereview->search = 0;
            }

            $sitereview->save();

            //PRIVACY WORK
            $sitereview_flag_info = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.flag.info', 0);
            if (empty($sitereview_flag_info)) {
                $sitereview_host = convert_uuencode($sitereview_host);
                Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.view.attempt', $sitereview_host);
            }

            $auth = Engine_Api::_()->authorization()->context;

            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

            if (empty($values['auth_view'])) {
                $values['auth_view'] = "everyone";
            }

            if (empty($values['auth_comment'])) {
                $values['auth_comment'] = "everyone";
            }

            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);

            foreach ($roles as $i => $role) {
                $auth->setAllowed($sitereview, $role, "view_listtype_$listingtype_id", ($i <= $viewMax));
                $auth->setAllowed($sitereview, $role, "view", ($i <= $viewMax));
                $auth->setAllowed($sitereview, $role, "comment_listtype_$listingtype_id", ($i <= $commentMax));
                $auth->setAllowed($sitereview, $role, "comment", ($i <= $commentMax));
            }

            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');

            if (empty($values['auth_topic'])) {
                $values['auth_topic'] = "registered";
            }

            if (empty($values['auth_photo'])) {
                $values['auth_photo'] = "registered";
            }

            if (!isset($values['auth_video']) && empty($values['auth_video'])) {
                $values['auth_video'] = "registered";
            }

            if (isset($values['auth_event']) && empty($values['auth_event'])) {
                $values['auth_event'] = "registered";
            }

            if (isset($values['auth_event']) && !empty($values['auth_event'])) {
                $eventMax = array_search($values['auth_event'], $roles);
                foreach ($roles as $i => $roles) {
                    $auth->setAllowed($sitereview, $roles, "event_listtype_$listingtype_id", ($i <= $eventMax));
                }
            }

            if (isset($values['auth_sprcreate']) && empty($values['auth_sprcreate'])) {
                $values['auth_sprcreate'] = "registered";
            }

            if (isset($values['auth_sprcreate']) && !empty($values['auth_sprcreate'])) {
                $projectMax = array_search($values['auth_sprcreate'], $roles);
                foreach ($roles as $i => $roles) {
                    $auth->setAllowed($sitereview, $roles, "sprcreate_listtype_$listingtype_id", ($i <= $projectMax));
                }
            }

            $topicMax = array_search($values['auth_topic'], $roles);
            $photoMax = array_search($values['auth_photo'], $roles);
            $videoMax = array_search($values['auth_video'], $roles);
            foreach ($roles as $i => $roles) {
                $auth->setAllowed($sitereview, $roles, "topic_listtype_$listingtype_id", ($i <= $topicMax));
                $auth->setAllowed($sitereview, $roles, "photo_listtype_$listingtype_id", ($i <= $photoMax));
                $auth->setAllowed($sitereview, $roles, "video_listtype_$listingtype_id", ($i <= $videoMax));
            }

            //COMMIT
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');
        $db->beginTransaction();
        try {
            $row = $tableOtherinfo->getOtherinfo($listing_id);
            $overview = '';
            if (isset($values['overview'])) {
                $overview = $values['overview'];
            }
            if (empty($row))
                Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->insert(array(
                    'listing_id' => $listing_id,
                    'overview' => $overview
                )); //COMMIT
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        if (!empty($listing_id)) {
            $sitereview->setLocation();
        }

        $db->beginTransaction();
        try {

            //START DEFAULT EMAIL TO SUPERADMIN WHEN ANYONE CREATE listing .
            $emails = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.defaultlistingcreate.email', null);
            if ( empty($emails) ) {
                $emails = array();
            } else {
                $emails = explode(",", $emails);
            }
            array_push( $emails , $viewer->email );
            $host = $_SERVER['HTTP_HOST'];
            $newVar = _ENGINE_SSL ? 'https://' : 'http://';
            $object_link = $newVar . $host . $sitereview->getHref();
            $viewerGetTitle = $viewer->getTitle();
            $sender_link = '<a href=' . $newVar . $host . $viewer->getHref() . ">$viewerGetTitle</a>";
            foreach ( $emails as $email ) {
                $email = trim($email);
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($email, 'SITEREVIEW_LISTING_CREATION_MAIL', array(
                                                'object_link' => $object_link,
                                                'sender' => $sender_link,
                                                'object_title' => $sitereview->getTitle(),
                                                'listing_type' => strtolower($this->_listingType->title_singular),
                                                'object_description' => $sitereview->getDescription(),
                                                'queue' => true
                                            ));
            }
            //END DEFAULT EMAIL TO SUPERADMIN WHEN ANYONE CREATE Listing custom code .            

            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting'))
                $sitereview_pending = $sitereview->pending;
            else
                $sitereview_pending = 0;

            if ($sitereview->draft == 0 && $sitereview->search && time() >= strtotime($sitereview->creation_date) && empty($sitereview_pending) && $sitereview->approved) {
                if(!empty($hasparent) && !empty($object)){
                    $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $object, $activityFeedType);
                } else {
                    $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sitereview, 'sitereview_new_listtype_' . $listingtype_id);
                }
                
            if ($action != null) {
                    Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sitereview);
                }
            }

            $users = Engine_Api::_()->getDbtable('editors', 'sitereview')->getAllEditors($listingtype_id, 0, 1);

            foreach ($users as $user_ids) {

                $subjectOwner = Engine_Api::_()->getItem('user', $user_ids->user_id);

                if (!($subjectOwner instanceof User_Model_User)) {
                    continue;
                }

                $host = $_SERVER['HTTP_HOST'];
                $newVar = _ENGINE_SSL ? 'https://' : 'http://';
                $object_link = $newVar . $host . $sitereview->getHref();

                Engine_Api::_()->getApi('mail', 'core')->sendSystem($subjectOwner->email, 'SITEREVIEW_LISTING_CREATION_EDITOR', array(
                    'listing_type' => strtolower($this->_listingType->title_singular),
                    'object_link' => $object_link,
                    'object_title' => $sitereview->getTitle(),
                    'object_description' => $sitereview->getDescription(),
                    'queue' => true
                ));
            }

            //SEND NOTIFICATIONS FOR SUBSCRIBERS
            if ($this->_listingType->subscription)
                Engine_Api::_()->getDbtable('subscriptions', 'sitereview')->sendNotifications($sitereview);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        //UPDATE KEYWORDS IN SEARCH TABLE
        if (!empty($keywords)) {
            Engine_Api::_()->getDbTable('search', 'core')->update(array('keywords' => $keywords), array('type = ?' => 'sitereview_listing', 'id = ?' => $sitereview->listing_id));
        }

        //OVERVIEW IS ENABLED OR NOT
        $allowOverview = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "overview_listtype_$listingtype_id");

        //EDIT IS ENABLED OR NOT
        $alloweEdit = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "edit_listtype_$listingtype_id");


        //CHECK FOR LEVEL SETTING
        if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
            //REDIRECTION TO DASHBOARD PAGES - CONDITIONS
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.create.redirection', 0) && $alloweEdit) {
                if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
                    if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "overview") && $allowOverview && !empty($this->_listingType->overview) && $alloweEdit) {
                        return $this->_helper->redirector->gotoRoute(array('action' => 'overview', 'listing_id' => $sitereview->listing_id, 'saved' => '1'), "sitereview_specific_listtype_$listingtype_id", true);
                    } else if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "photo") && Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "photo_listtype_$listingtype_id")) {
                        return $this->_helper->redirector->gotoRoute(array('listing_id' => $sitereview->listing_id, 'saved' => '1'), "sitereview_albumspecific_listtype_$listingtype_id", true);
                    } else if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "video") && Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer)) {
                        return $this->_helper->redirector->gotoRoute(array('listing_id' => $sitereview->listing_id, 'saved' => '1'), "sitereview_videospecific_listtype_$listingtype_id", true);
                    } else {
                        return $this->_helper->redirector->gotoRoute(array('listing_id' => $sitereview->listing_id, 'slug' => $sitereview->getSlug()), "sitereview_entry_view_listtype_$listingtype_id", true);
                    }
                } else {
                    if ($allowOverview && !empty($this->_listingType->overview) && $alloweEdit) {
                        return $this->_helper->redirector->gotoRoute(array('action' => 'overview', 'listing_id' => $sitereview->listing_id, 'saved' => '1'), "sitereview_specific_listtype_$listingtype_id", true);
                    } else if (Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "photo_listtype_$listingtype_id")) {
                        return $this->_helper->redirector->gotoRoute(array('listing_id' => $sitereview->listing_id, 'saved' => '1'), "sitereview_albumspecific_listtype_$listingtype_id", true);
                    } else if (Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer)) {
                        return $this->_helper->redirector->gotoRoute(array('listing_id' => $sitereview->listing_id, 'saved' => '1'), "sitereview_videospecific_listtype_$listingtype_id", true);
                    } else {
                        return $this->_helper->redirector->gotoRoute(array('listing_id' => $sitereview->listing_id, 'slug' => $sitereview->getSlug()), "sitereview_entry_view_listtype_$listingtype_id", true);
                    }
                }
            } else {//REDIRECTION TO PROFILE PAGE
                return $this->_helper->redirector->gotoRoute(array('listing_id' => $sitereview->listing_id, 'slug' => $sitereview->getSlug()), "sitereview_entry_view_listtype_$listingtype_id", true);
            }
        } else {
            //REDIRECTION TO DASHBOARD.
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.create.redirection', 0) && $alloweEdit) {
                return $this->_forwardCustom('success', 'utility', 'core', array(
                            'redirect' => $this->_helper->url->url(array('action' => 'edit', 'listing_id' => $sitereview->listing_id), "sitereview_specific_listtype_$listingtype_id", true),
                            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your Listing has been created successfully.')),
                ));
            } else {//REDIRECTION TO PROFILE PAGE OF LISTING.
                return $this->_forwardCustom('success', 'utility', 'core', array(
                            'redirect' => $this->_helper->url->url(array('listing_id' => $sitereview->listing_id, 'slug' => $sitereview->getSlug()), "sitereview_entry_view_listtype_$listingtype_id", true),
                            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your Listing has been created successfully.')),
                ));
            }
        }
    }

    //ACTION FOR EDITING THE SITEREVIEW
    public function editAction() {

        if (!$this->_helper->requireUser()->isValid())
            return;

        $this->view->TabActive = "edit";
        $listValues = array();
        $this->view->listing_id = $listing_id = $this->_getParam('listing_id');
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        if (empty($sitereview)) {
            return $this->_forwardCustom('notfound', 'error', 'core');
        }
        // $previous_location = $sitereview->location;
        //GET TINYMCE SETTINGS
        $this->view->upload_url = "";
        $albumEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album');
        if (Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create') && $albumEnabled) {
            $this->view->upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'upload-photo'), 'sitereview_general_listtype_' . $this->_listingType->listingtype_id, true);
        }

        //GET LISTING TYPE ID
        $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;

        $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);
        $this->view->show_editor = $this->_listingType->show_editor;

        $this->view->category_edit = $this->_listingType->category_edit;

        //SITEMOBILE_MODULE_NOT_SUPPORT_DESC_FOR_SOMEPAGES
//    if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting') && Engine_Api::_()->sitereview()->hasPackageEnable()) {
//      Engine_API::_()->sitemobile()->setupRequestError();
//    }
        //if(!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
        $this->_helper->content
                ->setContentName("sitereview_index_edit_listtype_$listingtype_id")
                //->setNoRender()
                ->setEnabled();

        //}

        $sitereviewinfo = $sitereview->toarray();
        $this->view->category_id = $previous_category_id = $sitereview->category_id;
        $this->view->subcategory_id = $subcategory_id = $sitereview->subcategory_id;
        $this->view->subsubcategory_id = $subsubcategory_id = $sitereview->subsubcategory_id;

        $row = Engine_Api::_()->getDbtable('categories', 'sitereview')->getCategory($subcategory_id);
        $this->view->subcategory_name = "";
        if (!empty($row)) {
            $this->view->subcategory_name = $row->category_name;
        }

        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            Engine_Api::_()->core()->setSubject($sitereview);
        }

        if (!$this->_helper->requireSubject()->isValid())
            return;

        if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "edit_listtype_$listingtype_id")->isValid()) {
            return;
        }

        //GET DEFAULT PROFILE TYPE ID
        $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sitereview')->defaultProfileId();

        //GET PROFILE MAPPING ID
        $categoryIds = array();
        $categoryIds[] = $sitereview->category_id;
        $categoryIds[] = $sitereview->subcategory_id;
        $categoryIds[] = $sitereview->subsubcategory_id;
        $this->view->profileType = $previous_profile_type = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');

        if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
            $categoryIds = array();
            $categoryIds[] = $_POST['category_id'];
            if (isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id'])) {
                $categoryIds[] = $_POST['subcategory_id'];
            }
            if (isset($_POST['subsubcategory_id']) && !empty($_POST['subsubcategory_id'])) {
                $categoryIds[] = $_POST['subsubcategory_id'];
            }
            $this->view->profileType = $previous_profile_type = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');
        }

        //MAKE FORM
        $this->view->form = $form = new Sitereview_Form_Edit(array('item' => $sitereview, 'defaultProfileId' => $defaultProfileId));

        $inDraft = 1;
        if (empty($sitereview->draft)) {
            $inDraft = 0;
            $form->removeElement('draft');
        }

        $form->removeElement('photo');
        $this->view->expiry_setting = $expiry_setting = Engine_Api::_()->sitereview()->expirySettings($listingtype_id);

        //SAVE SITEREVIEW ENTRY
        if (!$this->getRequest()->isPost()) {

            if (isset($this->_listingType->show_tag) && $this->_listingType->show_tag) {
                //prepare tags
                $sitereviewTags = $sitereview->tags()->getTagMaps();
                $tagString = '';

                foreach ($sitereviewTags as $tagmap) {

                    if ($tagString != '')
                        $tagString .= ', ';
                    $tagString .= $tagmap->getTag()->getTitle();
                }

                $this->view->tagNamePrepared = $tagString;
                $form->tags->setValue($tagString);
            }


            $form->populate($sitereview->toArray());

            if ($this->_listingType->edit_creationdate && $sitereview->creation_date && ($sitereview->draft || (!$sitereview->draft && (time() < strtotime($sitereview->creation_date))))) {

                $creation_date = strtotime($sitereview->creation_date);
                $oldTz = date_default_timezone_get();
                date_default_timezone_set($viewer->timezone);
                $creation_date = date('Y-m-d H:i:s', $creation_date);
                date_default_timezone_set($oldTz);

                $form->populate(array(
                    'creation_date' => $creation_date,
                ));
            }

            if ($sitereview->end_date && $sitereview->end_date != '0000-00-00 00:00:00') {
                $form->end_date_enable->setValue(1);
                // Convert and re-populate times
                $end = strtotime($sitereview->end_date);
                $oldTz = date_default_timezone_get();
                date_default_timezone_set($viewer->timezone);
                $end = date('Y-m-d H:i:s', $end);
                date_default_timezone_set($oldTz);

                $form->populate(array(
                    'end_date' => $end,
                ));
            } else if (empty($sitereview->end_date) || $sitereview->end_date == '0000-00-00 00:00:00') {
                $date = (string) date('Y-m-d');
                $form->end_date->setValue($date . ' 00:00:00');
            }

            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

            foreach ($roles as $role) {
                if ($form->auth_view) {
                    if (1 == $auth->isAllowed($sitereview, $role, "view_listtype_$listingtype_id")) {
                        $form->auth_view->setValue($role);
                    }
                }

                if ($form->auth_comment) {
                    if (1 == $auth->isAllowed($sitereview, $role, "comment_listtype_$listingtype_id")) {
                        $form->auth_comment->setValue($role);
                    }
                }
            }

            $roles_photo = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');

            foreach ($roles_photo as $role_topic) {
                if ($form->auth_topic) {
                    if (1 == $auth->isAllowed($sitereview, $role_topic, "topic_listtype_$listingtype_id")) {
                        $form->auth_topic->setValue($role_topic);
                    }
                }
            }

            foreach ($roles_photo as $role_photo) {
                if ($form->auth_photo) {
                    if (1 == $auth->isAllowed($sitereview, $role_photo, "photo_listtype_$listingtype_id")) {
                        $form->auth_photo->setValue($role_photo);
                    }
                }
            }

            foreach ($roles_photo as $role_photo) {
                if (isset($form->auth_event) && $form->auth_event) {
                    if (1 == $auth->isAllowed($sitereview, $role_photo, "event_listtype_$listingtype_id")) {
                        $form->auth_event->setValue($role_photo);
                    }
                }
            }

            foreach ($roles_photo as $role_photo) {
                if (isset($form->auth_sprcreate) && $form->auth_sprcreate) {
                    if (1 == $auth->isAllowed($sitereview, $role_photo, "sprcreate_listtype_$listingtype_id")) {
                        $form->auth_sprcreate->setValue($role_photo);
                    }
                }
            }

            $videoEnable = Engine_Api::_()->sitereview()->enableVideoPlugin();
            if ($videoEnable) {
                $roles_video = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
                foreach ($roles_video as $role_video) {
                    if ($form->auth_video) {
                        if (1 == $auth->isAllowed($sitereview, $role_video, "video_listtype_$listingtype_id")) {
                            $form->auth_video->setValue($role_video);
                        }
                    }
                }
            }

            if (Engine_Api::_()->sitereview()->listBaseNetworkEnable()) {
                if (empty($sitereview->networks_privacy)) {
                    $form->networks_privacy->setValue(array(0));
                }
            }
            return;
        }

        //FORM VALIDATION
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.duplicatetitle', 1) && $sitereview->title != $_POST['title']) {
            $isListingExists = Engine_Api::_()->getDbTable('listings', 'sitereview')->getListingColumn(array('listingtype_id' => $listingtype_id, 'title' => $_POST['title']));
            if ($isListingExists) {
                $error = $this->view->translate("Please choose the different listing title as listing with same title already exists.");
                $error = Zend_Registry::get('Zend_Translate')->_($error);

                $form->getDecorator('errors')->setOption('escape', false);
                $form->addError($error);
                return;
            }
        }

        //CATEGORY IS REQUIRED FIELD
        if (isset($_POST['category_id']) && empty($_POST['category_id'])) {
            $error = $this->view->translate('Please complete Category field - it is required.');
            $error = Zend_Registry::get('Zend_Translate')->_($error);

            $form->getDecorator('errors')->setOption('escape', false);
            $form->addError($error);
            return;
        }

        //GET FORM VALUES
        $values = $form->getValues();

        if (empty($values['listing_info'])) {
            $values = $listValues;
        } else {
            unset($values['listing_info']);
        }

        $tags = preg_split('/[,]+/', $values['tags']);
        $tags = array_filter(array_map("trim", $tags));

        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {

            if (Engine_Api::_()->sitereview()->listBaseNetworkEnable() && isset($values['networks_privacy']) && !empty($values['networks_privacy']) && in_array(0, $values['networks_privacy'])) {
                $values['networks_privacy'] = new Zend_Db_Expr('NULL');
                $form->networks_privacy->setValue(array(0));
            }
            if ($expiry_setting == 1 && $values['end_date_enable'] == 1) {
                // Convert times
                $oldTz = date_default_timezone_get();
                date_default_timezone_set($viewer->timezone);
                $end = strtotime($values['end_date']);
                date_default_timezone_set($oldTz);
                $values['end_date'] = date('Y-m-d H:i:s', $end);
            } elseif ($expiry_setting == 1 && isset($values['end_date'])) {
                $values['end_date'] = NULL;
            } elseif (isset($values['end_date'])) {
                unset($values['end_date']);
            }

            if ($this->_listingType->edit_creationdate && $sitereview->creation_date && ($sitereview->draft || (!$sitereview->draft && (time() < strtotime($sitereview->creation_date))))) {
                $oldTz = date_default_timezone_get();
                date_default_timezone_set($viewer->timezone);
                $creation = strtotime($values['creation_date']);
                date_default_timezone_set($oldTz);
                $values['creation_date'] = date('Y-m-d H:i:s', $creation);
            }

            $sitereview->setFromArray($values);
            $sitereview->modified_date = date('Y-m-d H:i:s');
            $sitereview->tags()->setTagMaps($viewer, $tags);
            $sitereview->save();

//       if (empty($sitereview->location)) {
//         Engine_Api::_()->getDbtable('locations', 'sitereview')->delete(array('listing_id =?' => $sitereview->listing_id));
//       } elseif (!empty($sitereview->location) && ($sitereview->location != $previous_location)) {
//         $sitereview->setLocation();
//       }
            //SAVE CUSTOM FIELDS
            $getListingRevType = Engine_Api::_()->getApi('listingType', 'sitereview')->getListingReviewType();
            $customfieldform = $form->getSubForm('fields');
            $customfieldform->setItem($sitereview);
            $customfieldform->saveValues();
            if ($customfieldform->getElement('submit')) {
                $customfieldform->removeElement('submit');
            }

            if (isset($values['category_id']) && !empty($values['category_id'])) {
                $categoryIds = array();
                $categoryIds[] = $sitereview->category_id;
                $categoryIds[] = $sitereview->subcategory_id;
                $categoryIds[] = $sitereview->subsubcategory_id;
                $sitereview->profile_type = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');
                if ($sitereview->profile_type != $previous_profile_type) {

                    $fieldvalueTable = Engine_Api::_()->fields()->getTable('sitereview_listing', 'values');
                    $fieldvalueTable->delete(array('item_id = ?' => $sitereview->listing_id));

                    Engine_Api::_()->fields()->getTable('sitereview_listing', 'search')->delete(array(
                        'item_id = ?' => $sitereview->listing_id,
                    ));

                    if (!empty($sitereview->profile_type) && !empty($previous_profile_type)) {
                        //PUT NEW PROFILE TYPE
                        $fieldvalueTable->insert(array(
                            'item_id' => $sitereview->listing_id,
                            'field_id' => $defaultProfileId,
                            'index' => 0,
                            'value' => $sitereview->profile_type,
                        ));
                    }
                }
                $sitereview->save();
            }

            //NOT SEARCHABLE IF SAVED IN DRAFT MODE
            if (!empty($sitereview->draft)) {
                $sitereview->search = 0;
                $sitereview->save();
            }

            if ($sitereview->draft == 0 && $sitereview->search && $inDraft && time() >= strtotime($sitereview->creation_date)) {
                $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($sitereview->getOwner(), $sitereview, 'sitereview_new_listtype_' . $listingtype_id);

                if ($action != null) {
                    Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sitereview);
                }
            }

            //CREATE AUTH STUFF HERE
            $auth = Engine_Api::_()->authorization()->context;

            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

            if (empty($values['auth_view'])) {
                $values['auth_view'] = "everyone";
            }

            if (empty($values['auth_comment'])) {
                $values['auth_comment'] = "everyone";
            }

            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);

            foreach ($roles as $i => $role) {
                $auth->setAllowed($sitereview, $role, "view_listtype_$listingtype_id", ($i <= $viewMax));
                $auth->setAllowed($sitereview, $role, "view", ($i <= $viewMax));
                $auth->setAllowed($sitereview, $role, "comment_listtype_$listingtype_id", ($i <= $commentMax));
                $auth->setAllowed($sitereview, $role, "comment", ($i <= $commentMax));
            }

            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');

            if ($values['auth_topic'])
                $auth_topic = $values['auth_topic'];
            else
                $auth_topic = "registered";
            $topicMax = array_search($auth_topic, $roles);

            foreach ($roles as $i => $role) {
                $auth->setAllowed($sitereview, $role, "topic_listtype_$listingtype_id", ($i <= $topicMax));
            }

            if ($values['auth_photo'])
                $auth_photo = $values['auth_photo'];
            else
                $auth_photo = "registered";
            $photoMax = array_search($auth_photo, $roles);

            foreach ($roles as $i => $role) {
                $auth->setAllowed($sitereview, $role, "photo_listtype_$listingtype_id", ($i <= $photoMax));
            }

            $roles_video = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
            if (!isset($values['auth_video']) && empty($values['auth_video'])) {
                $values['auth_video'] = "registered";
            }

            $videoMax = array_search($values['auth_video'], $roles_video);
            foreach ($roles_video as $i => $role_video) {
                $auth->setAllowed($sitereview, $role_video, "video_listtype_$listingtype_id", ($i <= $videoMax));
            }

            if (isset($values['auth_event'])) {
                if ($values['auth_event'])
                    $auth_event = $values['auth_event'];
                else
                    $auth_event = "registered";
                $eventMax = array_search($auth_event, $roles);

                foreach ($roles as $i => $role) {
                    $auth->setAllowed($sitereview, $role, "event_listtype_$listingtype_id", ($i <= $eventMax));
                }
            }

            if (isset($values['auth_sprcreate'])) {
                if ($values['auth_sprcreate'])
                    $auth_sprcreate = $values['auth_sprcreate'];
                else
                    $auth_sprcreate = "registered";
                $projectMax = array_search($auth_sprcreate, $roles);

                foreach ($roles as $i => $role) {
                    $auth->setAllowed($sitereview, $role, "sprcreate_listtype_$listingtype_id", ($i <= $projectMax));
                }
            }

            if ($previous_category_id != $sitereview->category_id) {
                Engine_Api::_()->getDbtable('ratings', 'sitereview')->editListingCategory($sitereview->listing_id, $previous_category_id, $sitereview->category_id, $sitereview->getType());
            }

            //SEND NOTIFICATIONS FOR SUBSCRIBERS
            if ($this->_listingType->subscription)
                Engine_Api::_()->getDbtable('subscriptions', 'sitereview')->sendNotifications($sitereview);

            $db->commit();
            $this->view->form = $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'));
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        $sitereview->setLocation();
        $db->beginTransaction();
        try {
            $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
            foreach ($actionTable->getActionsByObject($sitereview) as $action) {
                $actionTable->resetActivityBindings($action);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    //ACTION TO SET OVERVIEW
    public function overviewAction() {

        //ONLY LOGGED IN USER CAN ADD OVERVIEW
        if (!$this->_helper->requireUser()->isValid())
            return;

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING ID AND OBJECT
        $listing_id = $this->_getParam('listing_id');
        $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "overview"))
                return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        //GET LISTING TYPE ID
        $listingtype_id = $this->_listingType->listingtype_id;

        if (empty($this->_listingType->overview)) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }
        Engine_Api::_()->core()->setSubject($sitereview);
        if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
            $this->_helper->content
                    ->setContentName("sitereview_index_editoverview_listtype_$listingtype_id")
                    //->setNoRender()
                    ->setEnabled();
        }

        if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id)) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        if (!Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "overview_listtype_$sitereview->listingtype_id")) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        //SELECTED TAB
        $this->view->TabActive = "overview";

        //MAKE FORM
        $this->view->form = $form = new Sitereview_Form_Overview();

        //IF NOT POSTED
        if (!$this->getRequest()->isPost()) {
            $saved = $this->_getParam('saved');
            if (!empty($saved))
                $this->view->success = Zend_Registry::get('Zend_Translate')->_('Your ' . strtolower($this->_listingType->title_singular) . ' has been successfully created. You can enhance your ' . strtolower($this->_listingType->title_singular) . ' from this Dashboard by creating other components.');
        }

        $listing_id = $sitereview->getIdentity();

        $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');

        //SAVE THE VALUE
        if ($this->getRequest()->isPost()) {


            $row = $tableOtherinfo->getOtherinfo($listing_id);
            $overview = $_POST['overview'];

            if (empty($row)) {
                Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->insert(array(
                    'listing_id' => $listing_id,
                    'overview' => $overview
                )); //COMMIT  
            } else {
                $tableOtherinfo->update(array('overview' => $_POST['overview']), array('listing_id = ?' => $listing_id));
            }
            $this->view->form = $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'));
        }

        //POPULATE FORM
        $values['overview'] = $tableOtherinfo->getColumnValue($listing_id, 'overview');
        $form->populate($values);
    }

    //ACTION FOR EDIT STYLE OF SITEREVIEW
    public function editstyleAction() {

        //ONLY LOGGED IN USER CAN EDIT THE STYLE
        if (!$this->_helper->requireUser()->isValid())
            return;

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        //GET LISTING TYPE ID
        $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;
        //GET LISTING ID AND OBJECT
        $listing_id = $this->_getParam('listing_id');
        $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

        Engine_Api::_()->core()->setSubject($sitereview);
        if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $listingtype_id)) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        if (!Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('sitereview_listing', $viewer->level_id, "style_listtype_$listingtype_id")) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
            $this->_helper->content
                    ->setContentName("sitereview_index_editstyle_listtype_$listingtype_id")
                    //->setNoRender()
                    ->setEnabled();
        }

        if (!Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('sitereview_listing', $viewer->level_id, "style_listtype_$sitereview->listingtype_id")) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);

        //SELECTED TAB
        $this->view->TabActive = "style";

        //MAKE FORM
        $this->view->form = $form = new Sitereview_Form_Style();

        //FETCH EXISTING ROWS
        $tableStyle = Engine_Api::_()->getDbtable('styles', 'core');
        $select = $tableStyle->select()
                ->where('type = ?', 'sitereview_listing')
                ->where('id = ?', $listing_id)
                ->limit();
        $row = $tableStyle->fetchRow($select);

        //CHECK POST
        if (!$this->getRequest()->isPost()) {
            $form->populate(array('style' => ( null == $row ? '' : $row->style )));
            return;
        }

        //FORM VALIDATION
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        //PROCESS
        $style = $form->getValue('style');
        $style = strip_tags($style);

        $forbiddenStuff = array(
            '-moz-binding',
            'expression',
            'javascript:',
            'behaviour:',
            'vbscript:',
            'mocha:',
            'livescript:',
        );
        $style = str_replace($forbiddenStuff, '', $style);

        //SAVE ROW
        if (null == $row) {
            $row = $tableStyle->createRow();
            $row->type = 'sitereview_listing';
            $row->id = $listing_id;
        }
        $row->style = $style;
        $row->save();
        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'));
    }

    //ACTION FOR DELETE LISTING
    public function deleteAction() {

        //LOGGED IN USER CAN DELETE LISTING
        if (!$this->_helper->requireUser()->isValid())
            return;

        //GET LISTING ID AND OBJECT
        $this->view->listing_id = $listing_id = $this->_getParam('listing_id');
        $this->view->listing_singular_uc = $listing_singular_uc = ucfirst($this->_listingType->title_singular);
        $this->view->listing_singular_lc = $listing_singular_lc = strtolower($this->_listingType->title_singular);
        $this->view->listing_plural_lc = $listing_plural_lc = strtolower($this->_listingType->title_plural);
        $this->view->listing_plural_uc = $listing_plural_uc = ucfirst($this->_listingType->title_plural);

        $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

        //GET LISTING TYPE ID
        $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;
        $this->view->title = $this->_listingType->title_plural;

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "delete_listtype_$listingtype_id")->isValid()) {
            return;
        }

        //GET NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_main_listtype_$listingtype_id");
        //DELETE SITEREVIEW AFTER CONFIRMATION
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('confirm') == true) {
            $sitereview->delete();
            if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
                return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'sitereview_general_listtype_' . $listingtype_id, true);
            } else {
                $this->view->message = Zend_Registry::get('Zend_Translate')->_('Deleted Successfully.');
                return $this->_forward('success', 'utility', 'core', array(
                            'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'sitereview_general_listtype_' . $listingtype_id, true),
                            'messages' => Array($this->view->message)
                ));
            }
        }
    }

    //ACTION FOR CLOSE / OPEN LISTING
    public function closeAction() {

        //LOGGED IN USER CAN CLOSE LISTING
        if (!$this->_helper->requireUser()->isValid())
            return;

        //GET LISTING
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $this->_getParam('listing_id'));

        //GET LISTING TYPE ID
        $listingtype_id = $sitereview->listingtype_id;

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "edit_listtype_$listingtype_id")->isValid()) {
            return;
        }

        //BEGIN TRANSCATION
        $db = Engine_Api::_()->getDbTable('listings', 'sitereview')->getAdapter();
        $db->beginTransaction();

        try {
            $sitereview->closed = empty($sitereview->closed) ? 1 : 0;
            $sitereview->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        //RETURN TO MANAGE PAGE
        return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), "sitereview_general_listtype_$listingtype_id", true);
    }

    //ACTION FOR CONSTRUCT TAG CLOUD
    public function tagscloudAction() {

        $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;

        //SEND LISTING TYPE TITLE TO TPL
        $this->view->title = ucfirst($this->_listingType->title_singular);

        $this->view->listing_plural_lc = strtolower($this->_listingType->title_plural);
        $this->view->listing_singular_upper = strtoupper($this->_listingType->title_singular);

        //GET NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
                ->getNavigation('sitereview_main_listtype_' . $listingtype_id);

        //CONSTRUCTING TAG CLOUD
        $tag_array = array();
        $tag_cloud_array = Engine_Api::_()->sitereview()->getTags(0, 1000, 0, $listingtype_id);
        foreach ($tag_cloud_array as $vales) {

            $tag_array[$vales['text']] = $vales['Frequency'];
            $tag_id_array[$vales['text']] = $vales['tag_id'];
        }

        if (!empty($tag_array)) {

            $max_font_size = 18;
            $min_font_size = 12;
            $max_frequency = max(array_values($tag_array));
            $min_frequency = min(array_values($tag_array));
            $spread = $max_frequency - $min_frequency;

            if ($spread == 0) {
                $spread = 1;
            }

            $step = ($max_font_size - $min_font_size) / ($spread);

            $tag_data = array('min_font_size' => $min_font_size, 'max_font_size' => $max_font_size, 'max_frequency' => $max_frequency, 'min_frequency' => $min_frequency, 'step' => $step);

            $this->view->tag_data = $tag_data;
            $this->view->tag_id_array = $tag_id_array;
        }
        $this->view->tag_array = $tag_array;
    }

    public function getListingTypeAction() {
        $isAjax = $this->_getParam('isAjax', null);
        $type = $this->_getParam('type', null);
        $listing_id = $this->_getParam('listing_id', null);
        $getListingOrder = $this->_getParam('getListingOrder', null);
        $tempGetListingType = false;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
        $this->view->getClassName = $type;
        $getFieldsType = Engine_Api::_()->sitereview()->getFieldsType('sitereviewlistingtype');
        $sitereviewLsettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.lsettings', false);
        $sitereviewGetAttemptType = Zend_Registry::isRegistered('sitereviewGetAttemptType') ? Zend_Registry::get('sitereviewGetAttemptType') : null;
        $sitereviewListingTypeOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.listingtype.order', false);
        $sitereviewProfileOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.profile.order', false);
        $sitereviewViewAttempt = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.view.attempt', false);
        $sitereviewViewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.viewtype', false);

        if (!empty($getListingOrder) && $getListingOrder == 1) {
            $this->view->getListingOrder = 0;
            $this->view->defaultListingView = false;
        }
        if (!empty($getListingOrder) && $getListingOrder == 2) {
            $this->view->getListingOrder = 1;
            $this->view->defaultListingView = true;
        }
        if (!empty($getListingOrder) && $getListingOrder == 3) {
            $this->view->getListingOrder = 2;
            $this->view->defaultListingView = true;
        }
        if (!empty($getListingOrder) && $getListingOrder == 4) {
            $this->view->getListingOrder = 3;
            $this->view->defaultListingView = false;
        }
        if (!empty($getListingOrder) && $getListingOrder == 5) {
            $this->view->getListingOrder = 4;
            $this->view->defaultListingView = true;
        }

        $sitereviewViewAttempt = !empty($sitereviewGetAttemptType) ? $sitereviewGetAttemptType : @convert_uudecode($sitereviewViewAttempt);

        $tempGetFinalNumber = $sitereviewSponsoredOrder = $sitereviewFeaturedOrder = 0;
        for ($tempFlag = 0; $tempFlag < strlen($sitereviewLsettings); $tempFlag++) {
            $sitereviewFeaturedOrder += @ord($sitereviewLsettings[$tempFlag]);
        }

        if (!empty($listing_id)) {
            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
            if (empty($sitereview)) {
                $this->view->setListingType = false;
            } else {
                $this->view->setListingType = $sitereview;
            }
        }

        for ($tempFlag = 0; $tempFlag < strlen($sitereviewViewAttempt); $tempFlag++) {
            $sitereviewSponsoredOrder += @ord($sitereviewViewAttempt[$tempFlag]);
        }
        $sitereviewListingTypeOrder += $sitereviewFeaturedOrder + $sitereviewSponsoredOrder;

        if (!empty($getListingOrder) && !empty($listing_id)) {
            $auth = Engine_Api::_()->authorization()->context;
            $this->view->viewEveryone = $auth->getAllowed($listing_id, 'everyone', 'view', true);
            $this->view->commentEveryone = $auth->getAllowed($listing_id, 'everyone', 'comment', true);
            $this->view->getViewEveryone = $auth->getAllowed($listing_id, 'everyone', 'view', true);
            $this->view->getCommentEveryone = $auth->getAllowed($listing_id, 'everyone', 'comment', true);
        }

        if (!empty($sitereviewViewType) || (!empty($sitereviewProfileOrder) && !empty($sitereviewListingTypeOrder) && ($sitereviewListingTypeOrder == $sitereviewProfileOrder))) {
            $this->view->getListingType = $tempGetListingType = true;
        } else {
            $getFieldsType['sitereview.cat.listing'] = 0;
            foreach ($getFieldsType as $key => $value) {
                Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
            }
            $this->view->getListingType = $tempGetListingType = false;
        }
    }

    //ACTION FOR TELL A FRIEND ABOUT LISTING
    public function tellafriendAction() {

        //SET LAYOUT
        $this->_helper->layout->setLayout('default-simple');
        $sitemobile = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobile');
        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewr_id = $viewer->getIdentity();

        //GET FORM
        $this->view->form = $form = new Sitereview_Form_TellAFriend();
        //IF THE MODE IS APP MODE THEN
        if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
            Zend_Registry::set('setFixedCreationForm', true);
            Zend_Registry::set('setFixedCreationFormBack', 'Back');
            Zend_Registry::set('setFixedCreationHeaderTitle', Zend_Registry::get('Zend_Translate')->_('Tell a friend'));
            Zend_Registry::set('setFixedCreationHeaderSubmit', Zend_Registry::get('Zend_Translate')->_('Send'));
            $this->view->form->setAttrib('id', 'tellAFriendFrom');
            Zend_Registry::set('setFixedCreationFormId', '#tellAFriendFrom');
            $this->view->form->removeElement('send');
            $this->view->form->removeElement('cancel');
            $form->setTitle('');
        }
        if (!empty($viewr_id)) {
            $value['sender_email'] = $viewer->email;
            $value['sender_name'] = $viewer->displayname;
            $form->populate($value);
        }

        //FORM VALIDATION
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

            //GET LISTING ID AND OBJECT
            $listing_id = $this->_getParam('listing_id', $this->_getParam('id', null));
            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

            //GET FORM VALUES
            $values = $form->getValues();

            //EXPLODE EMAIL IDS
            $reciver_ids = explode(',', $values['reciver_emails']);
            if (!empty($values['send_me'])) {
                $reciver_ids[] = $values['sender_email'];
            }
            $sender_email = $values['sender_email'];
            $heading = $sitereview->title;

            //CHECK VALID EMAIL ID FORMAT
            $validator = new Zend_Validate_EmailAddress();
            $validator->getHostnameValidator()->setValidateTld(false);

            if (!$validator->isValid($sender_email)) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid sender email address value'));
                return;
            }

            foreach ($reciver_ids as $reciver_id) {
                $reciver_id = trim($reciver_id, ' ');
                if (!$validator->isValid($reciver_id)) {
                    $form->addError(Zend_Registry::get('Zend_Translate')->_('Please enter correct email address of the receiver(s).'));
                    return;
                }
            }

            $sender = $values['sender_name'];
            $message = $values['message'];

            Engine_Api::_()->getApi('mail', 'core')->sendSystem($reciver_ids, 'SITEREVIEW_TELLAFRIEND_EMAIL', array(
                'host' => $_SERVER['HTTP_HOST'],
                'sender' => $sender,
                'heading' => $heading,
                'message' => '<div>' . $message . '</div>',
                'object_link' => $sitereview->getHref(),
                'email' => $sender_email,
                'queue' => false
            ));
            if ($sitemobile && Engine_Api::_()->sitemobile()->checkMode('mobile-mode'))
                $this->_forwardCustom('success', 'utility', 'core', array(
                    'parentRedirect' => $sitereview->getHref(),
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message to your friend has been sent successfully.'))
                ));
            else
                $this->_forwardCustom('success', 'utility', 'core', array(
                    'smoothboxClose' => true,
                    'parentRefreshTime' => '15',
                    'format' => 'smoothbox',
                    'messages' => Zend_Registry::get('Zend_Translate')->_('Your message to your friend has been sent successfully.')
                ));
        }
    }

    //ACTION FOR APPLY NOW FOR LISTING
    public function applynowAction() {

        $param = $this->_getParam('param');
        $request_url = $this->_getParam('request_url');
        $return_url = $this->_getParam('return_url');
        $front = Zend_Controller_Front::getInstance();
        $base_url = $front->getBaseUrl();

        // CHECK USER VALIDATION
        if (!$this->_helper->requireUser()->isValid()) {
            if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
                return;
            }
            $host = (!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://" : "http://";
            if ($base_url == '') {
                $URL_Home = $host . $_SERVER['HTTP_HOST'] . '/login';
            } else {
                if ($request_url)
                    $URL_Home = $host . $_SERVER['HTTP_HOST'] . '/' . $request_url . '/login';
                else
                    $URL_Home = $host . $_SERVER['HTTP_HOST'] . $base_url . '/login';
            }
            if (empty($param)) {
                return $this->_helper->redirector->gotoUrl($URL_Home, array('prependBase' => false));
            } else {
                return $this->_helper->redirector->gotoUrl($URL_Home . '?return_url=' . urlencode($return_url), array('prependBase' => false));
            }
        }

        //SET LAYOUT
        $this->_helper->layout->setLayout('default-simple');
        $sitemobile = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobile');

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewr_id = $viewer->getIdentity();

        //GET LISTING ID AND OBJECT
        $listing_id = $this->_getParam('listing_id', $this->_getParam('id', null));
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        $listingtype_id = $sitereview->listingtype_id;

        $listing_type_singular = ucfirst(Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'title_singular'));

        $params = array();
        $params['listing_id'] = $listing_id;
        $params['viewer_id'] = $viewr_id;

        $this->view->status = $checkStatus = Engine_Api::_()->getDbtable('jobs', 'sitereview')->getApplyStatus($params);

        //CHECK STATUS
        if (!empty($checkStatus)) {
            $lcListingTypeTitle = lcfirst($listing_type_singular);
            echo '<div class="global_form" style="margin:15px 0 0 15px;"><div><div>';
            echo '<div class="form-elements" style="margin-top:10px;"><div class="form-wrapper" style="margin-bottom:10px;">' . $this->view->translate("You have already applied for this $lcListingTypeTitle.") . '</div>';
            echo '<div class="form-wrapper"><button onclick="parent.Smoothbox.close()">' . $this->view->translate("Close") . '</button></div></div></div></div></div>';
        }

        //GET FORM
        $this->view->form = $form = new Sitereview_Form_ApplyNow();

        //FORM VALIDATION
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

            //GET FORM VALUES
            $values = $form->getValues();

            $sender_email = '';
            $sender_name = '';
            $fileName = '';
            $contactNumber = '';
            $body = '';

            if (isset($values['sender_email']))
                $sender_email = $values['sender_email'];
            if (isset($values['sender_name']))
                $sender_name = $values['sender_name'];
            if (isset($values['filename']))
                $fileName = $values['filename'];
            if (isset($values['contact']))
                $contactNumber = $values['contact'];
            if (isset($values['body']))
                $body = $values['body'];
            $listingTitle = $sitereview->title;

            if (empty($sender_name))
                $displayName = $viewer->displayname;
            else
                $displayName = $values['sender_name'];
            if (!empty($fileName)) {
                //FILE EXTENSION SHOULD NOT DIFFER FROM ALLOWED TYPE
                $ext = str_replace(".", "", strrchr($_FILES['filename']['name'], "."));
                if (!in_array($ext, array('pdf', 'txt', 'ps', 'rtf', 'epub', 'odt', 'odp', 'ods', 'odg', 'odf', 'sxw', 'sxc', 'sxi', 'sxd', 'doc', 'ppt', 'pps', 'xls', 'docx', 'pptx', 'ppsx', 'xlsx', 'tif', 'tiff'))) {
                    $error = $this->view->translate("Invalid file extension. Allowed extensions are :'pdf', 'txt', 'ps', 'rtf', 'epub', 'odt', 'odp', 'ods', 'odg', 'odf', 'sxw', 'sxc', 'sxi', 'sxd', 'doc', 'ppt', 'pps', 'xls', 'docx', 'pptx', 'ppsx', 'xlsx', 'tif', 'tiff'");
                    $error = Zend_Registry::get('Zend_Translate')->_($error);

                    $form->getDecorator('errors')->setOption('escape', false);
                    $form->addError($error);
                    return;
                }
            }

            if (!empty($sender_email)) {
                //CHECK VALID EMAIL ID FORMAT
                $validator = new Zend_Validate_EmailAddress();
                $validator->getHostnameValidator()->setValidateTld(false);

                if (!$validator->isValid($sender_email)) {
                    $form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid sender email address value'));
                    return;
                }
            }

            //GET SITEREVIEW JOB TABLE
            $sitereviewJobTable = Engine_Api::_()->getDbtable('jobs', 'sitereview');

            $sitereviewJobRow = $sitereviewJobTable->createRow();
            $sitereviewJobRow->setFromArray($values);
            $sitereviewJobRow->user_id = $viewr_id;
            $sitereviewJobRow->listing_id = $listing_id;
            $sitereviewJobRow->save();

            if (!empty($fileName)) {
                $sitereviewJobRow->setFile($form->filename);
            }

            $user = Engine_Api::_()->getItem('user', $sitereview->owner_id);

            $http = _ENGINE_SSL ? 'https://' : 'http://';

            $listing_title_link = '<a href =' . $http . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('listing_id' => $sitereview->listing_id, 'slug' => $sitereview->getSlug()), "sitereview_entry_view_listtype_$listingtype_id") . ">$sitereview->title</a>";

            $file_id = Engine_Api::_()->getItem('sitereview_job', $sitereviewJobRow->job_id)->file_id;
            if (!empty($file_id)) {
                $service_id = Engine_Api::_()->getItem('storage_file', $file_id)->service_id;
                if ($service_id == 1)
                    $path = $http . $_SERVER['HTTP_HOST'] . Engine_Api::_()->getItem('storage_file', $file_id)->map();
                else
                    $path = Engine_Api::_()->getItem('storage_file', $file_id)->map();
            } else
                $path = '';

            $chekcSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitemailtemplates.check.setting', 0);
            if ((Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemailtemplates') && !empty($chekcSetting)) || empty($path)) {
                $contactInfo = '';
                if (!empty($sender_name))
                    $contactInfo .= 'Name: ' . $sender_name . '<br />';
                if (!empty($sender_email))
                    $contactInfo .= 'Email: ' . $sender_email . '<br />';
                if (!empty($contactNumber))
                    $contactInfo .= 'Contact Number: ' . $contactNumber . '<br />';
                if (!empty($body))
                    $contactInfo .= 'Message: ' . $body;

                //SEND MAIL WORK TO LISTING OWNER
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($user->email, 'SITEREVIEW_APPLYNOW_EMAIL', array(
                    'host' => $_SERVER['HTTP_HOST'],
                    'sender' => $displayName,
                    'listing_type' => $listing_type_singular,
                    'heading' => $listingTitle,
                    'file_path' => $path,
                    'filename' => $fileName,
                    'contact_info' => $contactInfo,
                    'object_link' => $listing_title_link,
                    'queue' => false
                ));
            } else {
                $user_message = 'Hello ' . $user->displayname . ',' . '<br /><br />' .
                        $displayName . ' has applied for the ' . $listing_type_singular . '  ' . $listing_title_link . '.' . ' Please find the summary below:' . '<br /><br />';
                if (!empty($sender_name))
                    $user_message .= 'Name: ' . $sender_name . '<br />';
                if (!empty($sender_email))
                    $user_message .= 'Email: ' . $sender_email . '<br />';
                if (!empty($contactNumber))
                    $user_message .= 'Contact Number: ' . $contactNumber . '<br />';
                if (!empty($body))
                    $user_message .= 'Message: ' . $body;

                if ($path != '') {
                    $subject = $sender_name . ' has applied for ' . $listingTitle;
                    $mailApi = Engine_Api::_()->getApi('mail', 'core');
                    $mail = $mailApi->create();
                    $mail
                            ->setFrom($sender_email, $sender_name)
                            ->setSubject($subject)
                            ->setBodyHtml($user_message);

                    $mail->addTo($user->email);
                    $handle = @fopen($path, "r");
                    while (($buffer = fgets($handle)) !== false) {
                        $content .= $buffer;
                    }
                    $attachment = $mail->createAttachment($content);
                    $attachment->filename = $fileName;

                    $mailApi->send($mail);
                }
            }

            if ($sitemobile && Engine_Api::_()->sitemobile()->checkMode('mobile-mode'))
                $this->_forwardCustom('success', 'utility', 'core', array(
                    'parentRedirect' => $sitereview->getHref(),
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have applied successfully.'))
                ));
            else
                $this->_forwardCustom('success', 'utility', 'core', array(
                    'smoothboxClose' => true,
                    'parentRefreshTime' => '15',
                    'format' => 'smoothbox',
                    'messages' => Zend_Registry::get('Zend_Translate')->_('You have applied successfully.')
                ));
        }
    }

    //ACTION FOR PACKAGE UPDATION
    public function showApplicationAction() {

        //USER VALIDATON
        if (!$this->_helper->requireUser()->isValid())
            return;

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->TabActive = "application";
        $this->view->sitereviews_view_menu = 15;

        if (!empty($_GET['page']))
            $page = $_GET['page'];
        else
            $page = 1;

        //GET LISTING TYPE ID
        $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;

        //GET LISTING ID LISTING OBJECT AND THEN CHECK VALIDATIONS
        $this->view->listing_id = $listing_id = $this->_getParam('listing_id');
        if (empty($listing_id) || empty($this->_listingType->allow_apply) || empty($this->_listingType->show_application)) {
            return $this->_forwardCustom('notfound', 'error', 'core');
        }

        $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $listingtype_id)) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        if (empty($sitereview)) {
            return $this->_forwardCustom('notfound', 'error', 'core');
        }

        $viewer = Engine_Api::_()->user()->getViewer();

        $paginator = Engine_Api::_()->getDbTable('jobs', 'sitereview')->getApplications($listing_id);
        $this->view->paginator = $paginator->setCurrentPageNumber($page);
        $this->view->paginator->setItemCountPerPage(25);

        //GET NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_main_listtype_$listingtype_id");
        $this->view->is_ajax = $this->_getParam('is_ajax', '');
    }

    //ACTION FOR PRINTING THE SITEREVIEW
    public function printAction() {

        //LAYOUT DEFAULT
        $this->_helper->layout->setLayout('default-simple');

        //GET LISTING ID AND OBJECT
        $listing_id = $this->_getParam('listing_id', $this->_getParam('id', null));
        $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

        //IF LISTING IS NOT EXIST
        if (empty($sitereview)) {
            return $this->_forwardCustom('notfound', 'error', 'core');
        }

        $this->view->otherInfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getOtherinfo($listing_id);

        //GET REVIEW TABLE
        $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
        $params = array();
        $params['resource_id'] = $listing_id;
        $params['resource_type'] = $sitereview->getType();
        $params['type'] = 'user';
        $noReviewCheck = $reviewTable->getAvgRecommendation($params);
        if (!empty($noReviewCheck)) {
            $this->view->noReviewCheck = $noReviewCheck->toArray();
            if ($this->view->noReviewCheck)
                $this->view->recommend_percentage = round($noReviewCheck[0]['avg_recommend'] * 100, 3);
        }

        $this->view->listingType = $this->_listingType;
        $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);
        $this->view->listing_singular_lc = strtolower($this->_listingType->title_singular);

        if ($sitereview->category_id != 0) {
            $categoryTable = Engine_Api::_()->getDbtable('categories', 'sitereview');
            $this->view->category_name = $categoryTable->getCategory($sitereview->category_id)->category_name;

            if ($sitereview->subcategory_id != 0) {
                $this->view->subcategory_name = $categoryTable->getCategory($sitereview->subcategory_id)->category_name;

                if ($sitereview->subsubcategory_id != 0) {
                    $this->view->subsubcategory_name = $categoryTable->getCategory($sitereview->subsubcategory_id)->category_name;
                }
            }
        }

        $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
        $this->view->fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($sitereview);
    }

    //ACTION FOR EDIT THE LOCATION
    public function editlocationAction() {

        //GET LISTING ID AND OBJECT
        $this->view->listing_id = $listing_id = $this->_getParam('listing_id');
        $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

        //IF LOCATION SETTING IS ENABLED
        if (!Engine_Api::_()->sitereview()->enableLocation($sitereview->listingtype_id)) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        //START PACKAGE WORK
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "map"))
                return $this->_forwardCustom('requireauth', 'error', 'core');
        }
        //END PACKAGE WORK
        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING TYPE ID
        $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;

        $this->view->listing_singular_uc = $listing_singular_uc = ucfirst($this->_listingType->title_singular);
        $this->view->listing_singular_lc = $listing_singular_lc = strtolower($this->_listingType->title_singular);
        $this->view->listing_plural_lc = $listing_plural_lc = strtolower($this->_listingType->title_plural);
        $this->view->listing_plural_uc = $listing_plural_uc = ucfirst($this->_listingType->title_plural);

        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "edit_listtype_$listingtype_id")->isValid()) {
            return;
        }
        Engine_Api::_()->core()->setSubject($sitereview);
        if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
            $this->_helper->content
                    ->setContentName("sitereview_index_editlocation_listtype_$listingtype_id")
                    //->setNoRender()
                    ->setEnabled();
        }

        //WHICH TAB SHOULD COME ACTIVATE
        $this->view->TabActive = "location";

        //GET LOCATION TABLE
        $locationTable = Engine_Api::_()->getDbtable('locations', 'sitereview');

        //MAKE VALUE ARRAY
        $values = array();
        $value['id'] = $sitereview->listing_id;
        $value['listingtype_id'] = $sitereview->listingtype_id;

        //GET LOCATION
        $this->view->location = $location = $locationTable->getLocation($value);

        if (!empty($location)) {

            //MAKE FORM
            $this->view->form = $form = new Sitereview_Form_Location(array(
                'item' => $sitereview,
                'location' => $location->location
            ));

            //CHECK POST
            if (!$this->getRequest()->isPost()) {
                $form->populate($location->toarray());
                return;
            }

            //FORM VALIDATION
            if (!$form->isValid($this->getRequest()->getPost())) {
                return;
            }

            //GET FORM VALUES
            $values = $form->getValues();

            unset($values['submit']);
            unset($values['location']);
            unset($values['locationParams']);


            //UPDATE LOCATION
            $locationTable->update($values, array('listing_id = ?' => $listing_id));

            $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'));
        }
        $this->view->location = $locationTable->getLocation($value);
    }

    //ACTION FOR EDIT THE LISTING ADDRESS
    public function editaddressAction() {

        //GET LISTING ID AND OBJECT
        $listing_id = $this->_getParam('listing_id');
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        $previous_location = $sitereview->location;
        $listingtype_id = $this->_listingType->listingtype_id;
        $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);

        //IF SITEREVIEW IS NOT EXIST
        if (empty($sitereview)) {
            return $this->_forwardCustom('notfound', 'error', 'core');
        }

        //START PACKAGE WORK
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "map"))
                return $this->_forwardCustom('requireauth', 'error', 'core');
        }
        //END PACKAGE WORK
        //
    //MAKE FORM
        $this->view->form = $form = new Sitereview_Form_Address(array('item' => $sitereview));

        //CHECK POST
        if (!$this->getRequest()->isPost()) {
            $form->populate($sitereview->toArray());
            return;
        }

        //FORM VALIDATION
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {

            $location = $_POST['location'];
            $sitereview->location = $location;
            $sitereview->save();

            //GET LOCATION TABLE
            $locationTable = Engine_Api::_()->getDbtable('locations', 'sitereview');
            if (!empty($location) && $location != $previous_location) {
                $sitereview->setLocation();
                //$locationTable->update(array('location' => $location), array('listing_id = ?' => $listing_id));
            } elseif (empty($location)) {
                $locationTable->delete(array('listing_id = ?' => $listing_id));
            }

            $db->commit();
            if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
                $this->_forwardCustom('success', 'utility', 'core', array(
                    'smoothboxClose' => 500,
                    'parentRefresh' => 500,
                    'messages' => array('Your listing location has been modified successfully.')
                ));
            } else {
                return $this->_forwardCustom('success', 'utility', 'core', array(
                            'redirect' => $this->_helper->url->url(array('action' => 'editlocation', 'listing_id' => $sitereview->listing_id), "sitereview_specific_listtype_$listingtype_id", true),
                            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your listing location has been modified successfully.')),
                ));
            }
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    //ACTION TO GET SUB-CATEGORY
    public function subCategoryAction() {

        //GET CATEGORY ID
        $category_id_temp = $this->_getParam('category_id_temp');

        //INTIALIZE ARRAY
        $this->view->subcats = $data = array();

        //RETURN IF CATEGORY ID IS EMPTY
        if (empty($category_id_temp))
            return;

        //GET CATEGORY TABLE
        $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');

        //GET CATEGORY
        $category = $tableCategory->getCategory($category_id_temp);
        if (!empty($category->category_name)) {
            $categoryName = Engine_Api::_()->getItem('sitereview_category', $category_id_temp)->getCategorySlug();
        }

        //GET SUB-CATEGORY
        $subCategories = $tableCategory->getSubCategories($category_id_temp, array('category_id', 'category_name'));

        foreach ($subCategories as $subCategory) {
            $content_array = array();
            $content_array['category_name'] = Zend_Registry::get('Zend_Translate')->_($subCategory->category_name);
            $content_array['category_id'] = $subCategory->category_id;
            $content_array['categoryname_temp'] = $categoryName;
            $data[] = $content_array;
        }

        $this->view->subcats = $data;
    }

    //ACTION FOR FETCHING SUB-CATEGORY
    public function subsubCategoryAction() {

        //GET SUB-CATEGORY ID
        $subcategory_id_temp = $this->_getParam('subcategory_id_temp');

        //INTIALIZE ARRAY
        $this->view->subsubcats = $data = array();

        //RETURN IF SUB-CATEGORY ID IS EMPTY
        if (empty($subcategory_id_temp))
            return;

        //GET CATEGORY TABLE
        $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');

        //GET SUB-CATEGORY
        $subCategory = $tableCategory->getCategory($subcategory_id_temp);
        if (!empty($subCategory->category_name)) {
            $subCategoryName = Engine_Api::_()->getItem('sitereview_category', $subcategory_id_temp)->getCategorySlug();
        }

        //GET 3RD LEVEL CATEGORIES
        $subCategories = $tableCategory->getSubCategories($subcategory_id_temp, array('category_id', 'category_name'));
        foreach ($subCategories as $subCategory) {
            $content_array = array();
            $content_array['category_name'] = Zend_Registry::get('Zend_Translate')->_($subCategory->category_name);
            $content_array['category_id'] = $subCategory->category_id;
            $content_array['categoryname_temp'] = $subCategoryName;
            $data[] = $content_array;
        }
        $this->view->subsubcats = $data;
    }

    //ACTION FOR LIKES THE LISTING
    public function likesitereviewAction() {

        //GET SETTINGS
        $like_user_str = 0;
        $this->view->resource_type = $resource_type = $this->_getParam('resource_type');
        $this->view->resource_id = $resource_id = $this->_getParam('resource_id');
        $this->view->call_status = $call_status = $this->_getParam('call_status');
        $this->view->page = $page = $this->_getParam('page', 1);
        $search = $this->_getParam('search', '');
        $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax', 0);
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        $this->view->search = $search;
        if (empty($search)) {
            $this->view->search = $this->view->translate('Search Members');
        }

        if ($call_status == 'friend') {

            //GET CORE LIKE TABLE
            $sub_status_table = Engine_Api::_()->getItemTable('core_like');
            $sub_status_name = $sub_status_table->info('name');

            //GET MEMBERSHIP TABLE
            $membership_table = Engine_Api::_()->getDbtable('membership', 'user');
            $member_name = $membership_table->info('name');

            //GET USER TABLE
            $user_table = Engine_Api::_()->getItemTable('user');
            $user_Name = $user_table->info('name');

            //MAKE QUERY
            $sub_status_select = $user_table->select()
                    ->setIntegrityCheck(false)
                    ->from($sub_status_name, array('poster_id'))
                    ->joinInner($member_name, "$member_name . user_id = $sub_status_name . poster_id", NULL)
                    ->joinInner($user_Name, "$user_Name . user_id = $member_name . user_id")
                    ->where($member_name . '.resource_id = ?', $viewer_id)
                    ->where($member_name . '.active = ?', 1)
                    ->where($sub_status_name . '.resource_type = ?', $resource_type)
                    ->where($sub_status_name . '.resource_id = ?', $resource_id)
                    ->where($sub_status_name . '.poster_id != ?', $viewer_id)
                    ->where($sub_status_name . '.poster_id != ?', 0)
                    ->where($user_Name . '.displayname LIKE ?', '%' . $search . '%')
                    ->order('	like_id DESC');
        } else if ($call_status == 'public') {

            //GET CORE LIKE TABLE
            $sub_status_table = Engine_Api::_()->getItemTable('core_like');
            $sub_status_name = $sub_status_table->info('name');

            //GET USER TABLE
            $user_table = Engine_Api::_()->getItemTable('user');
            $user_Name = $user_table->info('name');

            //MAKE QUERY
            $sub_status_select = $user_table->select()
                    ->setIntegrityCheck(false)
                    ->from($sub_status_name, array('poster_id'))
                    ->joinInner($user_Name, "$user_Name . user_id = $sub_status_name . poster_id")
                    ->where($sub_status_name . '.resource_type = ?', $resource_type)
                    ->where($sub_status_name . '.resource_id = ?', $resource_id)
                    ->where($sub_status_name . '.poster_id != ?', 0)
                    ->where($user_Name . '.displayname LIKE ?', '%' . $search . '%')
                    ->order($sub_status_name . '.like_id DESC');
        }

        $fetch_sub = Zend_Paginator::factory($sub_status_select);
        $fetch_sub->setCurrentPageNumber($page);
        $fetch_sub->setItemCountPerPage(10);
        $check_object_result = $fetch_sub->getTotalItemCount();

        $this->view->user_obj = array();
        if (!empty($check_object_result)) {
            $this->view->user_obj = $fetch_sub;
        } else {
            $this->view->no_result_msg = $this->view->translate('No results were found.');
        }

        //TOTAL LIKE FOR THIS CONTENT
        $this->view->public_count = Engine_Api::_()->sitereview()->number_of_like('sitereview_listing', $resource_id);

        //NUMBER OF FRIENDS LIKE THIS CONTENT
        $this->view->friend_count = Engine_Api::_()->sitereview()->friend_number_of_like($resource_type, $resource_id);

        //GET LIKE TITLE
        if ($resource_type == 'member') {
            $this->view->like_title = Engine_Api::_()->getItem('user', $resource_id)->displayname;
        } else {
            $this->view->like_title = Engine_Api::_()->getItem($resource_type, $resource_id)->title;
        }
    }

    //ACTION FOR GLOBALLY LIKE THE LISTING
    public function globallikesAction() {

        //CHECK USER VALIDATION
        if (!$this->_helper->requireUser()->isValid())
            return;

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //GET THE VALUE OF RESOURCE ID AND TYPE 
        $resource_id = $this->_getParam('resource_id');
        $resource_type = $this->_getParam('resource_type');
        $like_id = $this->_getParam('like_id');
        $status = $this->_getParam('smoothbox', 1);
        $this->view->status = true;

        //GET LIKE TABLE
        $likeTable = Engine_Api::_()->getDbTable('likes', 'core');
        $like_name = $likeTable->info('name');

        //GET OBJECT
        $resource = Engine_Api::_()->getItem($resource_type, $resource_id);
        if (empty($like_id)) {

            //CHECKING IF USER HAS MAKING DUPLICATE ENTRY OF LIKING AN APPLICATION.
            $like_id_temp = Engine_Api::_()->sitereview()->check_availability($resource_type, $resource_id);
            if (empty($like_id_temp)) {

                if (!empty($resource)) {
                    $like_id = $likeTable->addLike($resource, $viewer);
                    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitelike'))
                        Engine_Api::_()->sitelike()->setLikeFeed($viewer, $resource);
                }

                $notify_table = Engine_Api::_()->getDbtable('notifications', 'activity');
                $db = $likeTable->getAdapter();
                $db->beginTransaction();
                try {

                    //CREATE THE NEW ROW IN TABLE
                    if ($resource->owner_id != $viewer_id) {
                        $notifyData = $notify_table->createRow();
                        $notifyData->user_id = $resource->owner_id;
                        $notifyData->subject_type = $viewer->getType();
                        $notifyData->subject_id = $viewer->getIdentity();
                        $notifyData->object_type = $resource_type;
                        $notifyData->object_id = $resource_id;
                        $notifyData->type = 'liked';
                        $notifyData->params = $resource->getShortType();
                        $notifyData->date = date('Y-m-d h:i:s', time());
                        $notifyData->save();
                    }
                    $this->view->like_id = $like_id;
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
                $like_msg = $this->view->translate('Successfully Liked.');
            }
        } else {
            if (!empty($resource)) {
                $likeTable->removeLike($resource, $viewer);
                if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitelike'))
                    Engine_Api::_()->sitelike()->removeLikeFeed($viewer, $resource);
            }
            $like_msg = $this->view->translate('Successfully Unliked.');
        }

        if (empty($status)) {
            $this->_forwardCustom('success', 'utility', 'core', array(
                'smoothboxClose' => true,
                'parentRefresh' => true,
                'messages' => array($like_msg))
            );
        }
    }

    //ACTION FOR PUBLISH LISTING
    public function publishAction() {

        //CHECK USER VALIDATION
        if (!$this->_helper->requireUser()->isValid())
            return;

        //GET LISTING ID AND OBJECT
        $listing_id = $this->view->listing_id = $this->_getParam('listing_id');
        $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);
        $this->view->listing_singular_lc = strtolower($this->_listingType->title_singular);
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        $this->view->listingtype = $this->_listingType;

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //GET LISTING TYPE ID
        $listingtype_id = $this->_listingType->listingtype_id;

        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "edit_listtype_$listingtype_id")->isValid()) {
            return;
        }

        //SMOOTHBOX
        if (null == $this->_helper->ajaxContext->getCurrentContext()) {
            $this->_helper->layout->setLayout('default-simple');
        } else {
            //NO LAYOUT
            $this->_helper->layout->disableLayout(true);
        }

        $this->view->form = $form = new Sitereview_Form_Publish();

        //CHECK POST
        if (!$this->getRequest()->isPost()) {
            if ($this->_listingType->edit_creationdate && $sitereview->creation_date) {

                $creation_date = strtotime($sitereview->creation_date);
                $oldTz = date_default_timezone_get();
                date_default_timezone_set($viewer->timezone);
                $creation_date = date('Y-m-d H:i:s', $creation_date);
                date_default_timezone_set($oldTz);

                $form->populate(array(
                    'creation_date' => $creation_date,
                ));
            }
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $this->view->success = false;
        $db = Engine_Api::_()->getDbtable('listings', 'sitereview')->getAdapter();
        $db->beginTransaction();
        try {

            if (!empty($_POST['search'])) {
                $sitereview->search = 1;
            } else {
                $sitereview->search = 0;
            }
            $values = $form->getValues();
            if ($this->_listingType->edit_creationdate && $sitereview->creation_date) {
                $oldTz = date_default_timezone_get();
                date_default_timezone_set($viewer->timezone);
                $creation = strtotime($values['creation_date']);
                date_default_timezone_set($oldTz);
                $values['creation_date'] = date('Y-m-d H:i:s', $creation);
            }

            $sitereview->setFromArray($values);
            $sitereview->modified_date = new Zend_Db_Expr('NOW()');
            $sitereview->draft = 0;
            $sitereview->save();

            if ($sitereview->search && time() >= strtotime($sitereview->creation_date)) {
                $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($sitereview->getOwner(), $sitereview, 'sitereview_new_listtype_' . $listingtype_id);

                if ($action != null) {
                    Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sitereview);
                }
            }

            $db->commit();
            $this->view->success = true;
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }

        $this->_forwardCustom('success', 'utility', 'core', array(
            'smoothboxClose' => 10,
            'parentRefresh' => 10,
            'messages' => array('Successfully Published !')
        ));
    }

    //ACTION FOR GET THE LISTINGS BASED ON SEARCHING
    public function ajaxSearchAction() {

        $listingtype_id = $this->_getParam('listingtype_id', 0);

        //GET LISTINGS AND MAKE ARRAY
        $usersitereviews = Engine_Api::_()->getDbtable('listings', 'sitereview')->getDayItems($this->_getParam('text'), $this->_getParam('limit', 10), $listingtype_id);
        $data = array();
        $mode = $this->_getParam('struct');
        $count = count($usersitereviews);

        $i = 0;
        foreach ($usersitereviews as $usersitereview) {
            $sitereview_url = $this->view->url(array('listing_id' => $usersitereview->listing_id, 'slug' => $usersitereview->getSlug()), "sitereview_entry_view_listtype_$usersitereview->listingtype_id", true);
            $content_photo = $this->view->itemPhoto($usersitereview, 'thumb.icon');
            $i++;
            $data[] = array(
                'id' => $usersitereview->listing_id,
                'label' => $usersitereview->title,
                'photo' => $content_photo,
                'sitereview_url' => $sitereview_url,
                'total_count' => $count,
                'count' => $i
            );
        }

        if (!empty($data) && $i >= 1) {
            if ($data[--$i]['count'] == $count) {
                $data[$count]['id'] = 'stopevent';
                $data[$count]['label'] = $this->_getParam('text');
                $data[$count]['sitereview_url'] = 'seeMoreLink';
                $data[$count]['total_count'] = $count;
            }
        }
        return $this->_helper->json($data);
    }

    //ACTION FOR GET THE LISTINGS BASED ON SEARCHING
    public function getSearchListingsAction() {

        $listingtype_id = $this->_listingType->listingtype_id;

        //GET LISTINGS AND MAKE ARRAY
        $usersitereviews = Engine_Api::_()->getDbtable('listings', 'sitereview')->getDayItems($this->_getParam('text'), $this->_getParam('limit', 10), $listingtype_id);
        $data = array();
        $mode = $this->_getParam('struct');
        $count = count($usersitereviews);
        if ($mode == 'text') {
            $i = 0;
            foreach ($usersitereviews as $usersitereview) {
                $sitereview_url = $this->view->url(array('listing_id' => $usersitereview->listing_id, 'slug' => $usersitereview->getSlug()), "sitereview_entry_view_listtype_$listingtype_id", true);
                $i++;
                $content_photo = $this->view->itemPhoto($usersitereview, 'thumb.icon');
                $data[] = array(
                    'id' => $usersitereview->listing_id,
                    'label' => $usersitereview->title,
                    'photo' => $content_photo,
                    'sitereview_url' => $sitereview_url,
                    'total_count' => $count,
                    'count' => $i
                );
            }
        } else {
            $i = 0;
            foreach ($usersitereviews as $usersitereview) {
                $sitereview_url = $this->view->url(array('listing_id' => $usersitereview->listing_id, 'slug' => $usersitereview->getSlug()), "sitereview_entry_view_listtype_$listingtype_id", true);
                $content_photo = $this->view->itemPhoto($usersitereview, 'thumb.icon');
                $i++;
                $data[] = array(
                    'id' => $usersitereview->listing_id,
                    'label' => $usersitereview->title,
                    'photo' => $content_photo,
                    'sitereview_url' => $sitereview_url,
                    'total_count' => $count,
                    'count' => $i
                );
            }
        }
        if (!empty($data) && $i >= 1) {
            if ($data[--$i]['count'] == $count) {
                $data[$count]['id'] = 'stopevent';
                $data[$count]['label'] = $this->_getParam('text');
                $data[$count]['sitereview_url'] = 'seeMoreLink';
                $data[$count]['total_count'] = $count;
            }
        }
        return $this->_helper->json($data);
    }

    //ACTION FOR MESSAGING THE LISTING OWNER
    public function messageownerAction() {

        //LOGGED IN USER CAN SEND THE MESSAGE
        if (!$this->_helper->requireUser()->isValid())
            return;

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //GET LISTING ID AND OBJECT
        $listing_id = $this->_getParam('listing_id');
        $listing = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        $listingtype_id = $listing->listingtype_id;
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
        $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
        $listing_singular_uc = ucfirst($listingtypeArray->title_singular);
        //OWNER CANT SEND A MESSAGE TO HIMSELF
        if ($viewer_id == $listing->owner_id) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        //MAKE FORM
        $this->view->form = $form = new Messages_Form_Compose();
        if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
            $this->_helper->layout->setLayout('default');
            $this->_setParam('contentType', 'page');
            Zend_Registry::set('setFixedCreationForm', true);
            Zend_Registry::set('setFixedCreationFormBack', 'Back');
            Zend_Registry::set('setFixedCreationHeaderTitle', Zend_Registry::get('Zend_Translate')->_($form->getTitle()));
            Zend_Registry::set('setFixedCreationHeaderSubmit', Zend_Registry::get('Zend_Translate')->_('Send'));
            $this->view->form->setAttrib('id', 'messageOwnerSR');
            Zend_Registry::set('setFixedCreationFormId', '#messageOwnerSR');
            $this->view->form->removeElement('submit');
            $this->view->form->removeElement('cancel');
            $form->setTitle(sprintf(Zend_Registry::get('Zend_Translate')->_('To: %s'), $listing->getOwner()->getTitle()));
            $form->toValues->setLabel('');
        }
        if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode'))
            $form->toValues->setLabel('');
        $form->setDescription('Create your message with the form given below. (This message will be sent to the owner of this ' . $listing_singular_uc . '.)');
        $form->removeElement('to');
        $form->toValues->setValue("$listing->owner_id");

        //CHECK METHOD/DATA
        if (!$this->getRequest()->isPost()) {
            return;
        }

        $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
        $db->beginTransaction();

        try {
            $values = $this->getRequest()->getPost();

            $is_error = 0;
            if (empty($values['title'])) {
                $is_error = 1;
            }

            //SENDING MESSAGE
            if ($is_error == 1) {
                $error = $this->view->translate('Subject is required field !');
                $error = Zend_Registry::get('Zend_Translate')->_($error);

                $form->getDecorator('errors')->setOption('escape', false);
                $form->addError($error);
                return;
            }

            $recipients = preg_split('/[,. ]+/', $values['toValues']);

            //LIMIT RECIPIENTS IF IT IS NOT A SPECIAL SITEREVIEW OF MEMBERS
            $recipients = array_slice($recipients, 0, 1000);

            //CLEAN THE RECIPIENTS FOR REPEATING IDS
            $recipients = array_unique($recipients);

            $user = Engine_Api::_()->getItem('user', $listing->owner_id);

            $listing_title = $listing->title;
            $http = _ENGINE_SSL ? 'https://' : 'http://';
            $listing_title_with_link = '<a href =' . $http . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('listing_id' => $listing_id, 'slug' => $listing->getSlug()), "sitereview_entry_view_listtype_$listingtype_id") . ">$listing_title</a>";

            $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send($viewer, $recipients, $values['title'], $values['body'] . "<br><br>" . $this->view->translate('This message corresponds to the Listing: ') . $listing_title_with_link);

            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $conversation, 'message_new');

            //INCREMENT MESSAGE COUNTER
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');

            $db->commit();

            return $this->_forwardCustom('success', 'utility', 'core', array(
                        'smoothboxClose' => true,
                        'parentRefresh' => true,
                        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message has been sent successfully.'))
            ));
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    //ACTION FOR EDITING THE NOTE
    public function displayAction() {

        //GET TEXT AND LISTING ID
        $text = $this->_getParam('strr');
        $subjectType = $this->_getParam('subjectType');
        $subjectId = $this->_getParam('subjectId');

        if ($subjectType == 'sitereview_listing') {
            Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->update(array('about' => $text), array('listing_id = ?' => $subjectId));
        } else {
            Engine_Api::_()->getDbTable('editors', 'sitereview')->update(array('about' => $text), array('user_id = ?' => $subjectId));
        }

        exit();
    }

    //ACTION FOR UPLOADING IMAGES THROUGH WYSIWYG EDITOR
    public function uploadPhotoAction() {

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        $this->_helper->layout->disableLayout();

        if (!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
            return false;
        }

        if (!Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create')) {
            return false;
        }

        if (!$this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid())
            return;

        if (!$this->_helper->requireUser()->checkRequire()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }
        $fileName = Engine_Api::_()->seaocore()->tinymceEditorPhotoUploadedFileName();
        if (!isset($_FILES[$fileName]) || !is_uploaded_file($_FILES[$fileName]['tmp_name'])) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
            return;
        }

        $db = Engine_Api::_()->getDbtable('photos', 'album')->getAdapter();
        $db->beginTransaction();

        try {
            $viewer = Engine_Api::_()->user()->getViewer();

            $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
            $photo = $photoTable->createRow();
            $photo->setFromArray(array(
                'owner_type' => 'user',
                'owner_id' => $viewer->getIdentity()
            ));
            $photo->save();

            $photo->setPhoto($_FILES[$fileName]);

            $this->view->status = true;
            $this->view->name = $_FILES[$fileName]['name'];
            $this->view->photo_id = $photo->photo_id;
            $this->view->photo_url = $photo->getPhotoUrl();

            $table = Engine_Api::_()->getDbtable('albums', 'album');
            $album = $table->getSpecialAlbum($viewer, 'message');

            $photo->album_id = $album->album_id;
            $photo->save();

            if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
            }

            $auth = Engine_Api::_()->authorization()->context;
            $auth->setAllowed($photo, 'everyone', 'view', true);
            $auth->setAllowed($photo, 'everyone', 'comment', true);
            $auth->setAllowed($album, 'everyone', 'view', true);
            $auth->setAllowed($album, 'everyone', 'comment', true);

            $db->commit();
        } catch (Album_Model_Exception $e) {
            $db->rollBack();
            $this->view->status = false;
            $this->view->error = $this->view->translate($e->getMessage());
            throw $e;
            return;
        } catch (Exception $e) {
            $db->rollBack();
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
            throw $e;
            return;
        }
    }

    public function removeAdsWidgetAction() {

        $content_id = $this->_getParam('content_id', 0);
        if ($content_id) {
            Zend_Db_Table::getDefaultAdapter()->query("DELETE FROM engine4_core_content WHERE content_id = $content_id;");
        }
    }

    public function getMembersAction() {

        $text = $this->_getParam('text');
        $limit = $this->_getParam('limit', 40);

        //GET USER TABLE
        $tableUser = Engine_Api::_()->getDbtable('users', 'user');

        //SELECT
        $select = $tableUser->select()
                ->where('displayname  LIKE ? ', '%' . $text . '%')
                ->order('displayname ASC')
                ->limit($limit);

        //FETCH
        $userlists = $tableUser->fetchAll($select);

        //MAKING DATA
        $data = array();
        $mode = $this->_getParam('struct');
        if ($mode == 'text') {
            foreach ($userlists as $userlist) {
                $data[] = array('id' => $userlist->user_id, 'label' => $userlist->displayname, 'photo' => $this->view->itemPhoto($userlist, 'thumb.icon'));
            }
        } else {
            foreach ($userlists as $userlist) {
                $data[] = array('id' => $userlist->user_id, 'label' => $userlist->displayname, 'photo' => $this->view->itemPhoto($userlist, 'thumb.icon'));
            }
        }
        return $this->_helper->json($data);
    }

}

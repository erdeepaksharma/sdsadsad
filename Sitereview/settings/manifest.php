<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: manifest.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
$module = null;
$controller = null;
$action = null;
$getURL = null;
$request = Zend_Controller_Front::getInstance()->getRequest();
$routes = array();
if (!empty($request)) {
  $module = $request->getModuleName();
  $action = $request->getActionName();
  $controller = $request->getControllerName();
  $getURL = $request->getRequestUri();
}

/* not change this url if is use in js */
$routes['sitereview_compare'] = array(
    'route' => 'compare/*',
    'defaults' => array(
        'module' => 'sitereview',
        'controller' => 'compare',
        'action' => 'compare'
    ),
    'reqs' => array(
        'id' => '\d+',
    ),
);

$routes['sitereview_review_browse'] = array(
    'route' => 'reviews/browse/*',
    'defaults' => array(
        'module' => 'sitereview',
        'controller' => 'review',
        'action' => 'browse'
    ),
);
$routes['sitereview_review_categories'] = array(
    'route' => 'categories/*',
    'defaults' => array(
        'module' => 'sitereview',
        'controller' => 'index',
        'action' => 'categories'
    ),
);
$routes['sitereview_review_editor'] = array(
    'route' => 'editors/:action/*',
    'defaults' => array(
        'module' => 'sitereview',
        'controller' => 'editor',
        'action' => 'home',
    ),
);
$routes['sitereview_review_editor_profile'] = array(
    'route' => 'editor/profile/:username/:user_id',
    'defaults' => array(
        'module' => 'sitereview',
        'controller' => 'editor',
        'action' => 'profile',
    ),
    'reqs' => array(
        'user_id' => '\d+'
    )
);

if (empty($request) || !($module == "default" && ( strpos( $getURL, '/install') !== false))) {

  $cache = null; $cacheRoutes = array();
  if( Zend_Registry::isRegistered('Zend_Cache') ) {
    $cache = Zend_Registry::get('Zend_Cache');
    $cacheRoutes = $cache->load('sitereview_routes');
  }
  if( !$cacheRoutes ) {
    $favouriteSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0);
    $pluralWishlistPhrase = !empty($favouriteSetting) ? 'favourites' : 'wishlists';
    $singularWishlistPhrase = !empty($favouriteSetting) ? 'favourite' : 'wishlist';
  $routes['sitereview_wishlist_general'] = array(
      'route' => $pluralWishlistPhrase . '/:action/*',
      'defaults' => array(
          'module' => 'sitereview',
          'controller' => 'wishlist',
          'action' => 'browse',
          'listingtype_id' => 0,
      ),
      'reqs' => array(
          'action' => '(browse|create|edit|add|cover-photo|delete|remove|print|tell-a-friend|message-owner)',
      ),
  );

  $routes['sitereview_wishlist_view'] = array(
      'route' => $singularWishlistPhrase . '/:wishlist_id/:slug/*',
      'defaults' => array(
          'module' => 'sitereview',
          'controller' => 'wishlist',
          'action' => 'profile',
          'slug' => '',
      ),
      'reqs' => array(
          'wishlist_id' => '\d+'
      )
  );

  $db = Engine_Db_Table::getDefaultAdapter();
  $listingTypes = $db->query("SELECT listingtype_id, slug_plural, slug_singular FROM `engine4_sitereview_listingtypes`")->fetchAll();
  foreach ($listingTypes as $listingType) {
    $listingtype_id = $listingType['listingtype_id'];
    $slug_plural = $listingType['slug_plural'];
    $slug_singular = $listingType['slug_singular'];

    $routesTypeBase = array(
        'sitereview_extended_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/:controller/:action/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'index',
                'action' => 'home',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'controller' => '\D+',
                'action' => '\D+',
            )
        ),
        'sitereview_general_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/:action/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'index',
                'action' => 'home',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '(home|categories|index|top-rated|manage|create|ajaxhomesitereview|tagscloud|get-search-listings|sub-category|subsub-category|map|upload-photo|get-members)',
            ),
        ),
        'sitereview_editor_general_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/editor/:action/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'editor',
                'action' => 'home',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '(home|similar-items|add-items|categories)',
            ),
        ),
        'sitereview_priceinfo_listtype_' . $listingtype_id => array(
            'route' => $slug_singular . '/priceinfo/:action/:id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'price-info',
                'action' => 'index',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '(index|add|edit|delete|redirect)',
            ),
        ),
        'sitereview_specific_listtype_' . $listingtype_id => array(
            'route' => $slug_singular . '/:action/:listing_id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'index',
                'action' => 'view',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '(messageowner|tellafriend|print|delete|publish|close|edit|overview|editstyle|editlocation|editaddress|applynow|show-application)',
                'listing_id' => '\d+',
            )
        ),
        'sitereview_dashboard_listtype_' . $listingtype_id => array(
            'route' => $slug_singular . '/:action/:listing_id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'dashboard',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '(contact|change-photo|remove-photo|meta-detail|download-application|delete-application|multi-delete-application|application-detail|choose-project)',
                'listing_id' => '\d+',
            )
        ),
        'sitereview_entry_view_listtype_' . $listingtype_id => array(
            'route' => $slug_singular . '/:listing_id/:slug/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'index',
                'action' => 'view',
                'listingtype_id' => $listingtype_id,
                'slug' => ''
            ),
            'reqs' => array(
                'listing_id' => '\d+'
            )
        ),
        'sitereview_image_specific_listtype_' . $listingtype_id => array(
            'route' => $slug_singular . '/photo/view/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'photo',
                'action' => 'view',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '(view|remove)',
            ),
        ),
        'sitereview_photo_extended_listtype_' . $listingtype_id => array(
            'route' => $slug_singular . '/photo/:action/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'photo',
                'action' => 'edit',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '\D+',
            )
        ),
        'sitereview_photoalbumupload_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/photo/upload/:listing_id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'photo',
                'action' => 'upload',
                'listing_id' => '0',
                'listingtype_id' => $listingtype_id,
            )
        ),
        'sitereview_albumspecific_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/album/:action/:listing_id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'album',
                'action' => 'editphotos',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '(compose-upload|delete|edit|editphotos|upload|view)',
            ),
        ),
        'sitereview_videospecific_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/videos/:action/:listing_id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'videoedit',
                'action' => 'edit',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '(compose-upload|delete|edit|editphotos|upload|view)',
            ),
        ),
        'sitereview_video_upload_listtype_' . $listingtype_id => array(
            'route' => $slug_singular . '/video/:action/:listing_id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'video',
                'action' => 'index',
                'listing_id' => '0',
                'listingtype_id' => $listingtype_id,
            ),
//             'reqs' => array(
//                 'action' => '\+D',
//             ),
        ),
        'sitereview_general_category_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/:action/:category_id/:categoryname/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'index',
                'action' => 'index',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'category_id' => '\d+',
            ),
        ),
        'sitereview_general_subcategory_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/:action/:category_id/:categoryname/:subcategory_id/:subcategoryname/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'index',
                'action' => 'index',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'category_id' => '\d+',
                'subcategory_id' => '\d+',
            ),
        ),
        'sitereview_general_subsubcategory_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/:action/:category_id/:categoryname/:subcategory_id/:subcategoryname/:subsubcategory_id/:subsubcategoryname/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'index',
                'action' => 'index',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'category_id' => '\d+',
                'subcategory_id' => '\d+',
                'subsubcategory_id' => '\d+',
            ),
        ),
//        'sitereview_browse_category_listtype_' . $listingtype_id => array(
//            'route' => $slug_plural . '/:category/:subcategory/:subsubcategory',
//            'defaults' => array(
//                'module' => 'sitereview',
//                'controller' => 'index',
//                'action' => 'index',
//                'listingtype_id' => $listingtype_id,
//            ),
//            'reqs' => array(
//                'category' => '\d+',
//                'subcategory' => '\d+',
//                'subsubcategory' => '\d+',
//            ),
//        ),
        'sitereview_review_browse_listtype_' . $listingtype_id => array(
            'route' => '/reviews/browse/' . $slug_plural . '/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'review',
                'action' => 'browse',
                'listingtype_id' => $listingtype_id,
            ),
        ),
        'sitereview_review_categories_' . $listingtype_id => array(
            'route' => 'categories/' . $slug_plural . '/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'index',
                'action' => 'categories',
                'listingtype_id' => $listingtype_id,
            ),
        ),
        'sitereview_user_general_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/review/:action/listing_id/:listing_id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'review',
                //'action' => 'create',
                'listingtype_id' => $listingtype_id
            ),
            'reqs' => array(
                'listing_id' => '\d+',
                'action' => '(create|edit|update|reply|helpful|email|delete)'
            ),
        ),
        'sitereview_view_review_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/review/:action/:review_id/:listing_id/:slug/:tab/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'review',
                'action' => 'view',
                'listingtype_id' => $listingtype_id,
                'slug' => '',
                'tab' => ''
            ),
            'reqs' => array(
                'review_id' => '\d+',
                'listing_id' => '\d+'
            ),
        ),
        'sitereview_video_general_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/video/:action/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'video',
                'action' => 'view',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '(index|create)',
            )
        ),
        'sitereview_video_view_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/video/:listing_id/:user_id/:video_id/:slug/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'video',
                'action' => 'view',
                'slug' => '',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'user_id' => '\d+'
            )
        ),
        'sitereview_video_create_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/video/create/:listing_id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'video',
                'action' => 'create',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'listing_id' => '\d+'
            )
        ),
        'sitereview_video_edit_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/video/edit/:listing_id/:video_id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'video',
                'action' => 'edit',
                'listingtype_id' => $listingtype_id,
            )
        ),
        'sitereview_video_embed_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/videos/embed/:id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'video',
                'action' => 'embed',
                'listingtype_id' => $listingtype_id,
            )
        ),
        'sitereview_video_delete_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/video/delete/:listing_id/:video_id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'video',
                'action' => 'delete',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'video_id' => '\d+',
                'listing_id' => '\d+'
            )
        ),
        'sitereview_video_tags_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/video/tagscloud/:listing/',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'index',
                'action' => 'tags-cloud',
                'listing' => 1,
                'listingtype_id' => $listingtype_id,
            )
        ),
        'sitereview_video_general' => array(
            'route' => 'review-videos/:action/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'video',
                'action' => 'browse',
            ),
            'reqs' => array(
                'action' => '(index|browse)',
            )
        ),
        'sitereview_subscription_listtype_' . $listingtype_id => array(
            'route' => $slug_singular . '/:action/:listing_id/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'subscription',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '(add|remove)',
                'listing_id' => '\d+',
            )
        ),
        'sitereview_claim_listtype_' . $listingtype_id => array(
            'route' => $slug_plural . '/claim/:action/*',
            'defaults' => array(
                'module' => 'sitereview',
                'controller' => 'claim',
                'action' => 'index',
                'listingtype_id' => $listingtype_id,
            ),
            'reqs' => array(
                'action' => '(claim-listing|get-listings|terms|my-listings|delete)',
            ),
        ),
    );
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
      $routesTypeClaim = array(
          'sitereview_package_listtype_' . $listingtype_id => array(
              'route' => $slug_plural . '/package/:action/*',
              'defaults' => array(
                  'module' => 'sitereviewpaidlisting',
                  'controller' => 'package',
                  'action' => 'index',
                  'listingtype_id' => $listingtype_id,
                  'package' => 1,
              ),
              'reqs' => array(
                  'action' => '(detail|update-package|update-confirmation|cancel)',
              ),
          ),
          'sitereview_all_package_listtype_' . $listingtype_id => array(
              'route' => $slug_plural . '/packages/*',
              'defaults' => array(
                  'module' => 'sitereviewpaidlisting',
                  'controller' => 'package',
                  'action' => 'index',
                  'listingtype_id' => $listingtype_id,
                  'package' => 2,
              ),
          ),
          'sitereview_payment_' . $listingtype_id => array(
              'route' => $slug_plural . '/payment/',
              'defaults' => array(
                  'module' => 'sitereviewpaidlisting',
                  'controller' => 'payment',
                  'action' => 'index',
                  'listingtype_id' => $listingtype_id,
              ),
          ),
          'sitereview_process_payment_' . $listingtype_id => array(
              'route' => $slug_plural . '/payment/process',
              'defaults' => array(
                  'module' => 'sitereviewpaidlisting',
                  'controller' => 'payment',
                  'action' => 'process',
                  'listingtype_id' => $listingtype_id,
              ),
          ),
          'sitereview_session_payment_' . $listingtype_id => array(
              'route' => $slug_plural . '/payment/sessionpayment/',
              'defaults' => array(
                  'module' => 'sitereviewpaidlisting',
                  'controller' => 'package',
                  'action' => 'payment',
                  'listingtype_id' => $listingtype_id,
              ),
          ),
          'sitereviewpaidlisting_extended_listtype_' . $listingtype_id => array(
              'route' => $slug_plural . '/success/:controller/:action/*',
              'defaults' => array(
                  'module' => 'sitereviewpaidlisting',
                  'controller' => 'payment',
                  'action' => 'finish',
                  'listingtype_id' => $listingtype_id,
              ),
          ),
      );
      $routesTypeBase = array_merge($routesTypeBase, $routesTypeClaim);
    }
    $routes = array_merge($routes, $routesTypeBase);
  }
    if( $cache ) {
      $cache->save($routes,'sitereview_routes', array(), 3600);
    }
  }else {
    $routes = $cacheRoutes;
  }
}
return array(
    'package' =>
    array(
        'type' => 'module',
        'name' => 'sitereview',
        'version' => '4.10.1p1',
        'path' => 'application/modules/Sitereview',
        'title' => 'Multiple Listing Types Plugin Core (Reviews & Ratings Plugin)',
        'description' => 'Multiple Listing Types Plugin Core (Reviews & Ratings Plugin)',
        'author' => '<a href="http://www.socialengineaddons.com" style="text-decoration:underline;" target="_blank">SocialEngineAddOns</a>',
        'actions' => array(
            'install',
            'upgrade',
            'refresh',
            'enable',
            'disable',
        ),
        'callback' => array(
            'path' => 'application/modules/Sitereview/settings/install.php',
            'class' => 'Sitereview_Installer',
            'priority' => 1760,
        ),
        'directories' => array(
            'application/modules/Sitereview',
        ),
        'files' => array(
            'application/languages/en/sitereview.csv',
        ),
    ),
    // Mobile / Tablet Plugin Compatible
    'sitemobile_compatible' => true,
    //Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'onStatistics',
            'resource' => 'Sitereview_Plugin_Core'
        ),
        array(
            'event' => 'onRenderLayoutDefault',
            'resource' => 'Sitereview_Plugin_Core'
        ),
        array(
            'event' => 'onUserDeleteBefore',
            'resource' => 'Sitereview_Plugin_Core',
        ),
        array(
            'event' => 'onItemDeleteBefore',
            'resource' => 'Sitereview_Plugin_Core',
        ),
        array(
            'event' => 'onRenderLayoutMobileSMDefault',
            'resource' => 'Sitereview_Plugin_Core',
        ),
    ),
    //Items ---------------------------------------------------------------------
    'items' => array(
        'sitereview_clasfvideo',
        'sitereview_listing',
        'sitereview_album',
        'sitereview_photo',
        'sitereview_review',
        //'sitereview_vieweds',
        'sitereview_topic',
        'sitereview_post',
        'sitereview_import',
        'sitereview_importfile',
        'sitereview_category',
        'sitereview_listingtype',
        'sitereview_profilemap',
        'sitereview_ratingparam',
        'sitereview_wishlist',
        'sitereview_badge',
        'sitereview_editor',
        'sitereview_priceinfo',
        'sitereview_wheretobuy',
        'sitereview_video',
        'sitereview_claim',
        'sitereview_listmemberclaims',
        'sitereview_job',
    ),
    //Route--------------------------------------------------------------------
    'routes' => $routes,
);

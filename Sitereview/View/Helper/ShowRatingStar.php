<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ShowRatingStar.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_View_Helper_ShowRatingStar extends Zend_View_Helper_Abstract {

  /**
   * Assembles action string
   * 
   * @return string
   */
  public function showRatingStar($rating, $type='user', $sizeType='small-star', $listingtype_id = 0,$html_title=null) {

    if (empty($listingtype_id)) {
      return;
    }

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);

    /*
     * $listingtypeArray->reviews = 3 (Both rating allowed)
     * $listingtypeArray->reviews = 2 (Only user rating allowed)
     * $listingtypeArray->reviews = 1 (Only editor allowed)
     * $listingtypeArray->reviews = 0 (Both rating is not allowed)
     * 
     */
    if (empty($listingtypeArray->reviews) || ($listingtypeArray->reviews == 2 && $type == 'editor') || $listingtypeArray->reviews == 1 && $type == 'user') {
      return;
    }

    $title_type = $type;
    if ($type == 'overall') {
      $type = 'user';
    }
    $rating_half_star_class = '';
    $rating_star_class = '';

    $rating = sprintf("%.1f", $rating);

    if ($sizeType == 'small-star') {
      if ($type == 'editor') {
        $rating_star_class = "rating_star_r";
        $rating_half_star_class = "rating_star_half_r";
      } else if ($type == 'user' || $type == 'visitor') {
        $rating_star_class = "rating_star_y";
        $rating_half_star_class = "rating_star_half_y";
      } else if ($type == 'overall') {
        $rating_star_class = "rating_star_b";
        $rating_half_star_class = "rating_star_half_b";
      }
    } else {
      switch ($rating) {
        case 0:
          $rating_star_class = '';
          break;
        case $rating <= .5:
          $rating_star_class = 'halfstar';
          break;
        case $rating <= 1:
          $rating_star_class = 'onestar';
          break;
        case $rating <= 1.5:
          $rating_star_class = 'onehalfstar';
          break;
        case $rating <= 2:
          $rating_star_class = 'twostar';
          break;
        case $rating <= 2.5:
          $rating_star_class = 'twohalfstar';
          break;
        case $rating <= 3:
          $rating_star_class = 'threestar';
          break;
        case $rating <= 3.5:
          $rating_star_class = 'threehalfstar';
          break;
        case $rating <= 4:
          $rating_star_class = 'fourstar';
          break;
        case $rating <= 4.5:
          $rating_star_class = 'fourhalfstar';
          break;
        case $rating <= 5:
          $rating_star_class = 'fivestar';
          break;
      }

			if ($sizeType == 'small-box') {
				$rating_star_class = 'sr-rating-box-small ' . ((!empty($rating_star_class)) ? $rating_star_class . '-small-box' : '');
				if ($type == 'editor') {
					$rating_star_class = "sr-es-box-small " . $rating_star_class;
				} else if ($type == 'user' || $type == 'visitor') {
					$rating_star_class = "sr-us-box-small " . $rating_star_class;
				} else if ($type == 'overall') {
					$rating_star_class = "sr-as-box-small " . $rating_star_class;
				}
			} else if ($sizeType == 'big-box') {
				$rating_star_class .= 'rating-box ' . ((!empty($rating_star_class)) ? $rating_star_class . '-box' : '');
				if ($type == 'editor') {
					$rating_star_class = "sr-es-box " . $rating_star_class;
				} else if ($type == 'user' || $type == 'visitor') {
					$rating_star_class = "sr-us-box " . $rating_star_class;
				} else if ($type == 'overall') {
					$rating_star_class = "sr-us-box " . $rating_star_class;
				}
			} else if ($sizeType == 'big-star') {
				if ($type == 'editor') {
					$rating_star_class = "sr_es_rating " . $rating_star_class;
				} else if ($type == 'user' || $type == 'visitor') {
					$rating_star_class = "sr_us_rating " . $rating_star_class;
				} else if ($type == 'overall') {
					$rating_star_class = "sr_as_rating " . $rating_star_class;
				}
			} 
    }

    $data = array('title_type' => $title_type, 'html_title'=>$html_title,'rating' => round($rating, 2), 'sizeType' => $sizeType, 'rating_star_class' => $rating_star_class, 'rating_half_star_class' => $rating_half_star_class);
    return $this->view->partial(
                    '_showRatingStar.tpl', 'sitereview', $data
    );
  }

}
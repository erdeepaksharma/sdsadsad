<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Widget_TagsCloudController extends Engine_Content_Widget_Abstract {

    protected $_childCount;

    public function indexAction() {

        $front = Zend_Controller_Front::getInstance();
        $module = $front->getRequest()->getModuleName();
        $action = $front->getRequest()->getActionName();
        $controller = $front->getRequest()->getControllerName();
        $siteforumTagsCloud = Zend_Registry::isRegistered('siteforumTagsCloud') ? Zend_Registry::get('siteforumTagsCloud') : null;
        if ($module == 'siteforum' && $controller == 'index' && $action == 'tags-cloud') {
            $this->view->notShowExploreTags = 1;
        }

        $params = array();
        $params['totalTags'] = $this->_getParam('totalTags');
        $params['orderingType'] = $this->_getParam('orderingType');

        $tag_array = array();
        $tag_cloud_array = Engine_Api::_()->siteforum()->getTags($params);
        if (is_array($tag_cloud_array)) {
            foreach ($tag_cloud_array as $vales) {
                $tag_array[$vales['text']] = $vales['Frequency'];
                $tag_id_array[$vales['text']] = $vales['tag_id'];
            }
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

        if(empty($siteforumTagsCloud))
            return $this->setNoRender();
        
        if (count($this->view->tag_array) <= 0) {
            return $this->setNoRender();
        }
    }

}

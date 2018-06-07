<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Content.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_Story extends Core_Model_Item_Abstract {

    public function getHref($params = array(), $type = null) {
        $url = '';
        try {


            if (isset($this->file_id) && !empty($this->file_id)) {
                $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, $type);
                if (!empty($file))
                    $url = $file->map();
                else {
                    $url = '';
                }
            } elseif (isset($this->photo_id) && !empty($this->photo_id)) {
                $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, $type);
                if (!empty($file))
                    $url = $file->map();
                else {
                    $url = '';
                }
            }
        } catch (Exception $ex) {
            
        }

        return $url;
    }

}

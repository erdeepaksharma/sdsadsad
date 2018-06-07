<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Links.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_Link extends Core_Model_Item_Abstract  {

  
    /**
     * Set category icon
     *
     */
    public function setPhoto($photo) {

        if ($photo instanceof Zend_Form_Element_File) {
            $file = $photo->getFileName();
        } else if (is_array($photo) && !empty($photo['tmp_name'])) {
            $file = $photo['tmp_name'];
        } else if (is_string($photo) && file_exists($photo)) {
            $file = $photo;
        } else {
            return;
        }

        if (empty($file))
            return;

        //GET PHOTO DETAILS
        $name = basename($file);
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
        $mainName = $path . '/' . $name;

        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $photo_params = array(
            'parent_id' => $this->link_id,
            'parent_type' => "advancedactivity_link",
        );

        //RESIZE IMAGE WORK
        $image = Engine_Image::factory();
        $image->open($file);
        $image->open($file)
                ->resize(300, 500)
                ->write($mainName)
                ->destroy();

        try {
            $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);
        } catch (Exception $e) {
            if ($e->getCode() == Storage_Api_Storage::SPACE_LIMIT_REACHED_CODE) {
                echo $e->getMessage();
                exit();
            }
        }

        return $photoFile;
    }


}
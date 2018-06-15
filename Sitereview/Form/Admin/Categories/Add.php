<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Add.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Categories_Add extends Engine_Form {

  public function init() {

    $category_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('category_id', 0);
    $perform = Zend_Controller_Front::getInstance()->getRequest()->getParam('perform', 'add');
    $cat_dependency = 0;
    $subcat_dependency = 0;
    if ($category_id) {
      $category = Engine_Api::_()->getItem('sitereview_category', $category_id);
      if ($category && empty($category->cat_dependency)) {
        $cat_dependency = $category->category_id;
      } elseif ($category && !empty($category->cat_dependency)) {
        $cat_dependency = $category->category_id;
        $subcat_dependency = $category->category_id;
      }
    }

    if ($perform == 'add') {
      if (empty($category_id)) {
        $this
                ->setTitle('Add Category');
      } elseif (!empty($category_id) && empty($category->cat_dependency)) {
        $this
                ->setTitle('Add Sub Category');
      } elseif (!empty($category_id) && !empty($category->cat_dependency)) {
        $this
                ->setTitle('Add 3rd Level Category');
      }
    } elseif ($perform == 'edit') {
      $category_name = $category->category_name;
      $this
              ->setTitle("Edit $category_name");
    }

    $this->addElement('Text', 'category_name', array(
        'label' => 'Name',
        'required' => true,
        'empty' => false,
        'value' => '',
    ));

	$link = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    $this->addElement('Text', 'category_slug', array(
        'label' => 'URL Component',
        'description' => "This will be the end of the URL of your listing type's browse page, for example: ".$link."/listingtype/categoryid/URL-COMPONENT"
    ));
    $this->category_slug->getDecorator('Description')->setOptions(array('placement' => 'APPEND', 'escape' => false));

    $this->addElement('Text', 'meta_title', array(
        'label' => 'HTML Title',
        // 'required' => true,
        'value' => '',
    ));

    $this->addElement('Textarea', 'meta_description', array(
        'label' => 'Meta Description',
        // 'required' => true,
        'value' => '',
    ));

    $this->addElement('Textarea', 'meta_keywords', array(
        'label' => 'Meta Keywords',
        // 'required' => true,
        'value' => '',
    ));

    $this->addElement('File', 'icon', array(
        'label' => 'Icon',
        'Description' => 'Upload the icon. The recommended dimension for the icon of categories is: 24 x 24 pixels and of sub-categories and 3rd level categories is: 16 x 16 pixels.',
        'allowEmpty' => true,
        'required' => false,
    ));
    $this->icon->addValidator('Extension', false, 'jpg,png,gif,jpeg,JPG,PNG,GIF,JPEG');

    if (!empty($category_id) && $perform == 'edit') {
      $category = Engine_Api::_()->getItem('sitereview_category', $category_id);
      if ($category->file_id) {
        $photoName = Engine_Api::_()->storage()->get($category->file_id, '')->getPhotoUrl();
        $description = "<img src='$photoName' class='sr_categories_icon' />";

        //VALUE FOR LOGO PREVIEW.
        $this->addElement('Dummy', 'logo_icon_preview', array(
            'label' => 'Icon Preview',
            'description' => $description,
        ));
        $this->logo_icon_preview
                ->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

        $this->addElement('Checkbox', 'removeicon', array('Description' => 'Delete Icon', 'label' => 'Yes, delete this icon.'));
      }
    }

    $this->addElement('Text', 'banner_title', array(
        'label' => 'Banner Title',
        'Description' => '',
        'allowEmpty' => true,
        'required' => false,
    ));

    $this->addElement('Text', 'banner_url', array(
        'label' => 'Banner Url',
        'Description' => '',
        'allowEmpty' => true,
        'required' => false,
    ));

    $this->addElement('Radio', 'banner_url_window', array(
        'label' => 'Banner URLs Window',
        'description' => "Open URLs of banners in new browser window / tab.",
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 0,
    ));

    $this->addElement('File', 'banner', array(
        'label' => 'Banner',
        'Description' => 'Upload the banner.',
        'allowEmpty' => true,
        'required' => false,
    ));
    $this->banner->addValidator('Extension', false, 'jpg,png,gif,jpeg,JPG,PNG,GIF,JPEG');

    if (!empty($category_id) && $perform == 'edit') {
      $category = Engine_Api::_()->getItem('sitereview_category', $category_id);
      if ($category->banner_id) {
        $photoName = Engine_Api::_()->storage()->get($category->banner_id, '')->getPhotoUrl();
        $description = "<img src='$photoName' class='sr_categories_banner_img' />";

        //VALUE FOR LOGO PREVIEW.
        $this->addElement('Dummy', 'logo_banner_preview', array(
            'label' => 'Banner Preview',
            'description' => $description,
        ));
        $this->logo_banner_preview
                ->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

        $this->addElement('Checkbox', 'removebanner', array('Description' => 'Delete Banner', 'label' => 'Yes, delete this banner.'));
      }
    }

    $this->addElement('Textarea', 'top_content', array(
      'label' => 'Top Content',
      'description' => 'Please enter the content to be shown below the category name and above the listings on the category page. (Useful for SEO. For ex: You can enter the category description here.)'
    ));

    $this->addElement('Textarea', 'bottom_content', array(
      'label' => 'Bottom Content',
      'description' => 'Please enter the content to be shown below the listings on the category page. (Useful for SEO.)'
    ));

    $this->addElement('Checkbox', 'sponsored', array(
        'label' => 'Mark this category as Sponsored. (Note: Sponsored categories will be shown in "Sponsored Categories" widget as configured by you from the Layout Editor.)',
        'Description' => 'Mark as Sponsored',
        'value' => 0,
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Add',
        'type' => 'submit',
        'ignore' => true
    ));
  }

}
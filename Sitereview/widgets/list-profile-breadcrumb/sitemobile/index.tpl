<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php 
$subcategory=array();
$subsubcategory=array();
if(!empty($this->sitereview->subcategory_id)) {
	$subcategory = array("href"=>$this->url(array('category_id' => $this->sitereview->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->category_id)->getCategorySlug(), 'subcategory_id' => $this->sitereview->subcategory_id, 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->subcategory_id)->getCategorySlug()), "sitereview_general_subcategory_listtype_" . $this->sitereview->listingtype_id),"title"=>$this->translate($this->subcategory_name),"icon"=>"arrow-r");
}
if(!empty($this->sitereview->subsubcategory_id)) {
	$subsubcategory = array("href"=>$this->url(array('category_id' => $this->sitereview->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->category_id)->getCategorySlug(), 'subcategory_id' => $this->sitereview->subcategory_id, 'subcategoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->subcategory_id)->getCategorySlug(), 'subsubcategory_id' => $this->sitereview->subsubcategory_id, 'subsubcategoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->subsubcategory_id)->getCategorySlug()), "sitereview_general_subsubcategory_listtype_" . $this->sitereview->listingtype_id),"title"=>$this->translate($this->subsubcategory_name),"icon"=>"arrow-r");
}

$breadcrumb = array(
	array("href"=>$this->listingType->getHref(),"title"=>$this->translate("$this->title_plural Home"),"icon"=>"arrow-r"),
	array("href"=>$this->url(array('category_id' => $this->sitereview->category_id, 'categoryname' => Engine_Api::_()->getItem('sitereview_category', $this->sitereview->category_id)->getCategorySlug()), "sitereview_general_category_listtype_" . $this->sitereview->listingtype_id),"title"=>$this->translate($this->category_name),"icon"=>"arrow-r"),
	$subcategory,$subsubcategory,
	array("title"=>$this->sitereview->getTitle(),"icon"=>"arrow-d"),
);
echo $this->breadcrumb($breadcrumb);
?>


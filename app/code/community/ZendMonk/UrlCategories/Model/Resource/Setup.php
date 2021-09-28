<?php
/**
 * Attribute setup
 *
 * @category   ZendMonk
 * @package    ZendMonk_UrlCategories
 * @author     Carl Monk <@ZendMonk>
 */
class ZendMonk_UrlCategories_Model_Resource_Setup extends Mage_Catalog_Model_Resource_Eav_Mysql4_Setup
{
	/**
     * Disable update mode during install
	 * Required to perform attribute updates on store level 0
     */
	public function disableUpdateMode()
	{
		Mage::app()->reinitStores();
		Mage::app()->setUpdateMode(false);
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	}
	
	/**
     * Initially add attribute values to products and categories
     */
	public function addAttributeValues()
	{
		$helper = Mage::helper('zendmonk_urlcategories/defaultpath');
		$updateCategoryData = $helper->updateProducts();
		$helper->updateCategories($updateCategoryData, true);
	}
	
	/**
     * Remove attribute values from products and categories
     */
	public function removeAttributeValues()
	{
		$helper = Mage::helper('zendmonk_urlcategories/defaultpath');
		$products = Mage::getModel('catalog/product')->getCollection();
		foreach ($products as $product) {
			$helper->removeDefaultCategoryIds($product);
		}
		$categories = Mage::getModel('catalog/category')->getCollection();
		foreach ($categories as $category) {
			$helper->removeDefaultCategoryProductIds($category);
		}
	}
}
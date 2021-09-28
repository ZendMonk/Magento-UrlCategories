<?php
/**
 * Catalog category Default Category Product IDs attribute backend
 *
 * @category   ZendMonk
 * @package    ZendMonk_UrlCategories
 * @author     Carl Monk <@ZendMonk>
 */
class ZendMonk_UrlCategories_Model_Catalog_Category_Attribute_Backend_Defaultcategoryproductids extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
	/**
	 * Attribute code for Default Category Product IDs
	 */
	const ATTRIBUTE_CODE = 'default_category_product_ids';
	
	protected $_oldProductIds = array();
	protected $_wasActive = false;
	
	/**
     * Store category's old Product IDs and Active State before saving category
     *
	 * @param Mage_Catalog_Model_Category $object
     * @return void
	 */
	public function beforeSave($object)
	{
		$oldProductIds = array();
		foreach ($object->getProductCollection() as $product) {
			$oldProductIds[] = $product->getEntityId();
		}
		$this->_oldProductIds = $oldProductIds;
		
		$this->_wasActive = Mage::getModel('catalog/category')
			->setStoreId($object->getStoreId())
			->load($object->getEntityId())
			->getIsActive();
	}
	
	/**
     * Update category's Default Category Product IDs and data of affected products
     *
	 * @param Mage_Catalog_Model_Category $object
     * @return void
	 */
    public function afterSave($object)
    {
		$helper = Mage::helper('zendmonk_urlcategories/defaultpath');
		$defaultCategoryProductIds = $helper->getDefaultCategoryProductIds($object);
		
		$targetProductIds = array();
		$isActive = Mage::getModel('catalog/category')
			->setStoreId($object->getStoreId())
			->load($object->getEntityId())
			->getIsActive();
		$wasActive = false;
		$nowActive = false;
		if ($isActive != $this->_wasActive) {
			if (!$isActive) {
				$targetProductIds = $defaultCategoryProductIds;
				$wasActive = true;
			} else {
				$nowActive = true;
			}
		}
		
		$newProductIds = array();
		$category = Mage::getModel('catalog/category')
			->load($object->getEntityId());
		foreach ($category->getProductCollection() as $product) {
			$productId = $product->getEntityId();
			$newProductIds[] = $productId;
			if ($nowActive) {
				$defaultCategoryIds = $helper->getDefaultCategoryIds($product);
				if (in_array(0, array_keys($defaultCategoryIds))) {
					$targetProductIds[] = $productId;
				}
			}
		}
		
		if (!$wasActive && $removedProductIds = array_diff($this->_oldProductIds, $newProductIds)) {
			foreach ($removedProductIds as $productId) {
				if (in_array($productId, $defaultCategoryProductIds) && !in_array($productId, $targetProductIds)) {
					$targetProductIds[] = $productId;
				}
			}
		}
		
		if ($targetProductIds) {
			$updateCategoryData = $helper->updateProducts($targetProductIds);
			$helper->updateCategories($updateCategoryData);
		}
    }
	
	protected $_defaultCategoryProductIds = array();
	
	/**
     * Store category's Default Category Product IDs before removing category
     *
	 * @param Mage_Catalog_Model_Category $object
     * @return void
	 */
	public function beforeDelete($object)
	{
		$this->_defaultCategoryProductIds = Mage::helper('zendmonk_urlcategories/defaultpath')->getDefaultCategoryProductIds($object);
	}
	
	/**
     * Update product data affected by category removal
     *
	 * @param Mage_Catalog_Model_Category $object
     * @return void
	 */
	public function afterDelete($object)
	{
		if ($this->_defaultCategoryProductIds) {
			$helper = Mage::helper('zendmonk_urlcategories/defaultpath');
			$updateCategoryData = $helper->updateProducts($this->_defaultCategoryProductIds);
			$helper->updateCategories($updateCategoryData);
		}
	}
}
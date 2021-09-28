<?php
/**
 * Catalog product Default Category ID attribute backend
 *
 * @category   ZendMonk
 * @package    ZendMonk_UrlCategories
 * @author     Carl Monk <@ZendMonk>
 */
class ZendMonk_UrlCategories_Model_Catalog_Product_Attribute_Backend_Defaultcategoryid extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
	/**
	 * Attribute code for Default Category ID
	 */
	const ATTRIBUTE_CODE = 'default_category_id';
	
	/**
     * Lock Default Category ID for user's shouldn't be able to change it
     *
	 * @param Mage_Catalog_Model_Product $object
     * @return void
	 */
	public function afterLoad($object)
	{
		$object->lockAttribute(self::ATTRIBUTE_CODE);
	}
	
	/**
     * Update product's Default Category IDs and data of affected categories
     *
	 * @param Mage_Catalog_Model_Product $object
     * @return void
	 */
    public function afterSave($object)
    {
		$helper = Mage::helper('zendmonk_urlcategories/defaultpath');
		$updateProductData = $helper->setDefaultCategoryIds($object, $object->getCategoryIds());
		$updateCategoryData = $helper->getUpdateCategoryData($object->getEntityId(), $updateProductData);
		$helper->updateCategories($updateCategoryData);
    }
	
	protected $_defaultCategoryIds = array();
	
	/**
     * Store product's Default Category IDs before removing product
     *
	 * @param Mage_Catalog_Model_Product $object
     * @return void
	 */
	public function beforeDelete($object)
	{
		$this->_defaultCategoryIds = Mage::helper('zendmonk_urlcategories/defaultpath')->getDefaultCategoryIds($object);
	}
	
	/**
     * Update category data affected by product removal
     *
	 * @param Mage_Catalog_Model_Product $object
     * @return void
	 */
	public function afterDelete($object)
	{
		if ($this->_defaultCategoryIds) {
			$helper = Mage::helper('zendmonk_urlcategories/defaultpath');
			$updateProductData = array();
			foreach ($this->_defaultCategoryIds as $categoryId) {
				if ($categoryId) {
					$updateProductData[$categoryId] = array('removed');
				}
			}
			$updateCategoryData = $helper->getUpdateCategoryData($object->getEntityId(), $updateProductData);
			$helper->updateCategories($updateCategoryData);
		}
	}
}
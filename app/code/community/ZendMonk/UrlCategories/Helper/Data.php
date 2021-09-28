<?php
/**
 * Data helper
 *
 * @category   ZendMonk
 * @package    ZendMonk_UrlCategories
 * @author     Carl Monk <@ZendMonk>
 */
class ZendMonk_UrlCategories_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Config Path
	 */
	const XML_PATH_USE_DEFAULT_PATH = 'catalog/seo/product_use_default_path';
	
	protected $_useDefaultCategoryPath;
	
	/**
	 * Retrieve store config Product Use Default Category Path
	 *
	 * @return int
	 */
	public function getUseDefaultCategoryPath()
	{
		if (!isset($this->_useDefaultCategoryPath)) {
			if ($this->isModuleOutputEnabled() && $storeConfig = (int) Mage::getStoreConfig(self::XML_PATH_USE_DEFAULT_PATH)) {
				$this->_useDefaultCategoryPath = Mage::getStoreConfig(Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_USE_CATEGORY) ? $storeConfig : 0;
			} else {
				$this->_useDefaultCategoryPath = 0;
			}
		}
		return $this->_useDefaultCategoryPath;
	}
	
	protected $_defaultCategoryIds = array();
	
	/**
	 * Retrieve Default Category ID
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return int
	 */
	public function getDefaultCategoryId($product)
	{
		$productId = $product->getEntityId();
		if (isset($this->_defaultCategoryIds[$productId])) {
			return $this->_defaultCategoryIds[$productId];
		}
		
		$attributeCode = ZendMonk_UrlCategories_Model_Catalog_Product_Attribute_Backend_Defaultcategoryid::ATTRIBUTE_CODE;
		$attributeValue = $product->getData($attributeCode);
		if (is_null($attributeValue)) {
			$attributeValue = Mage::getModel('catalog/product')
				->setStoreId($product->getStoreId())
				->load($productId)
				->getData($attributeCode);
		}
		
		$this->_defaultCategoryIds[$productId] = $attributeValue;
		return $attributeValue;
	}
}
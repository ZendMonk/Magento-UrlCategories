<?php
/**
 * Defaultpath helper
 *
 * @category   ZendMonk
 * @package    ZendMonk_UrlCategories
 * @author     Carl Monk <@ZendMonk>
 */
class ZendMonk_UrlCategories_Helper_Defaultpath extends Mage_Core_Helper_Abstract
{
	/**
     * Save category's current attribute and assign new value
	 * Resembles function addAttributeUpdate from Mage_Catalog_Model_Product
     *
	 * @param Mage_Catalog_Model_Category $category
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @param int $storeId
     * @return void
     */
	protected function _addCategoryAttributeUpdate($category, $attributeCode, $attributeValue, $storeId)
	{
		$oldValue = $category->getData($attributeCode);
        $oldStore = $category->getStoreId();

        $category->setData($attributeCode, $attributeValue);
        $category->setStoreId(0);
        $category->getResource()->saveAttribute($category, $attributeCode);

        $category->setData($attributeCode, $oldValue);
        $category->setStoreId($oldStore);
	}
	
	protected $_categoriesActive;
	
	/**
     * Retrieve category's Active State by Store ID
	 *
     * @param Mage_Catalog_Model_Category $category
	 * @param int $storeId
	 * @return bool
     */
	protected function _getCategoryIsActive($category, $storeId) {
		$categoryId = $category->getEntityId();
		if (isset($this->_categoriesActive[$categoryId])) {
            return $this->_categoriesActive[$categoryId][$storeId];
        }
		
		$categoriesActive = array();
		if ($attributeValues = $this->_getAttributeValuesAssocByStore($category, 'is_active')) {
			foreach (Mage::app()->getStores(true) as $tmpStoreId => $store) {
				$attributeValue = isset($attributeValues[$tmpStoreId]) ? $attributeValues[$tmpStoreId] : $attributeValues[0];
				$categoriesActive[$tmpStoreId] = $attributeValue;
			}
		}
		
		$this->_categoriesActive[$categoryId] = $categoriesActive;
		return $categoriesActive[$storeId];
	}
	
	protected $_attributeValuesAssocByStore = array();
	
	/**
     * Retrieve array of attribute values associated by Store IDs
	 *
     * @param Mage_Catalog_Model_Abstract $object
	 * @param string $attributeCode
	 * @return array
     */
	protected function _getAttributeValuesAssocByStore($object, $attributeCode)
	{
		$entityId = $object->getEntityId();
		$cacheKey = sprintf('%d-%d-%s', $object->getEntityTypeId(), $entityId, $attributeCode);
		if (isset($this->_attributeValuesAssocByStore[$cacheKey])) {
			return $this->_attributeValuesAssocByStore[$cacheKey];
        }
		
		if ($object instanceof Mage_Catalog_Model_Product) {
			$entityType = 'catalog/product';
		} else if ($object instanceof Mage_Catalog_Model_Category) {
			$entityType = 'catalog/category';
		}
		if (!$entityType) {
			return false;
		}
		
		$attributeValues = array();
		foreach (Mage::app()->getStores(true) as $storeId => $store) {
			$attributeValue = Mage::getModel($entityType)
				->setStoreId($storeId)
				->load($entityId)
				->getData($attributeCode);
			if (is_null($attributeValue)) {
				$attributeValues = array();
				break;
			}
			if (!isset($attributeValues[0]) || $attributeValues[0] != $attributeValue) {
				$attributeValues[$storeId] = $attributeValue;
			}
		}
		$this->_attributeValuesAssocByStore[$cacheKey] = $attributeValues;
		return $attributeValues;
	}
	
	/**
     * Retrieve Store IDs for associated attribute values, excluding store level 0
	 *
     * @param array $attributeValues
	 * @return array
     */
	protected function _getStoreIds($attributeValues)
	{
		return array_keys(array_slice($attributeValues, 1, NULL, true));
	}
	
	/**
     * Save category's Default Category Product IDs
	 *
     * @param Mage_Catalog_Model_Category $category
	 * @param array $defaultCategoryProductIds
	 * @param bool $writeAnyways
	 * @return void
     */
	protected function _setDefaultCategoryProductIds($category, $defaultCategoryProductIds, $writeAnyways = false)
	{
		$newDefaultCategoryProductIds = $defaultCategoryProductIds;
		sort($newDefaultCategoryProductIds);
		if ($newDefaultCategoryProductIds != $this->getDefaultCategoryProductIds($category) || $writeAnyways) {
			$newDefaultCategoryProductIds = $newDefaultCategoryProductIds ? implode(',', $newDefaultCategoryProductIds) : '';
			$this->_addCategoryAttributeUpdate($category, ZendMonk_UrlCategories_Model_Catalog_Category_Attribute_Backend_Defaultcategoryproductids::ATTRIBUTE_CODE, $newDefaultCategoryProductIds, 0);
		}
	}
	
	/**
     * Retrieve category's Default Category Product IDs
	 *
     * @param Mage_Catalog_Model_Category $category
	 * @return array
     */
	public function getDefaultCategoryProductIds($category)
	{
		if ($defaultCategoryProductIds = $category->getData(ZendMonk_UrlCategories_Model_Catalog_Category_Attribute_Backend_Defaultcategoryproductids::ATTRIBUTE_CODE)) {
			return explode(',', $defaultCategoryProductIds);
		}
		return array();
	}
	
	/**
     * Remove category's Default Category Product IDs attribute value
	 *
     * @param Mage_Catalog_Model_Category $category
	 * @return void
     */
	public function removeDefaultCategoryProductIds($category)
	{
		$this->_addCategoryAttributeUpdate($category, ZendMonk_UrlCategories_Model_Catalog_Category_Attribute_Backend_Defaultcategoryproductids::ATTRIBUTE_CODE, NULL, 0);
	}
	
	/**
     * Save product's Default Category IDs
	 *
     * @param Mage_Catalog_Model_Product $product
	 * @param array $categoryIds
	 * @return array
     */
	public function setDefaultCategoryIds($product, $categoryIds)
	{
		$attributeCode = ZendMonk_UrlCategories_Model_Catalog_Product_Attribute_Backend_Defaultcategoryid::ATTRIBUTE_CODE;
		$attributeValues = $this->getDefaultCategoryIds($product);
		
		$defaultCategoryIds = array();
		foreach (array_values($attributeValues) as $defaultCategoryId) {
			if ($defaultCategoryId) {
				$defaultCategoryIds[] = $defaultCategoryId;
			}
		}
		
		$newDefaultCategoryIds = array();
		$newAttributeValues = array();
		if (!$categoryIds) {
			$newAttributeValues[0] = 0;
		} else {
			$categoryDataAssoc = array();
			$entityIds = array();
			$tmpCategoryIds = $categoryIds;
			
			while ($tmpCategoryIds) {
				$entityIds = array_merge($entityIds, $tmpCategoryIds);
				$categories = Mage::getModel('catalog/category')
					->getCollection()
					->addFieldToFilter('entity_id', array('in' => $tmpCategoryIds))
					->load();
				$parentCategoryIds = array();
				foreach ($categories as $category) {
					foreach (Mage::app()->getStores(true) as $storeId => $store) {
						if (!isset($categoryDataAssoc[$storeId])) {
							$categoryDataAssoc[$storeId] = array();
						}
						$parentId = $category->getParentId();
						if ($parentId == 0 || $this->_getCategoryIsActive($category, $storeId)) {
							$categoryDataAssoc[$storeId][$category->getEntityId()] = $parentId;
							if ($parentId && !in_array($parentId, $entityIds) && !in_array($parentId, $parentCategoryIds)) {
								$parentCategoryIds[] = $parentId;
							}
						}
					}
				}
				$tmpCategoryIds = $parentCategoryIds;
			}
			
			foreach ($categoryDataAssoc as $storeId => $categoryData) {
				$newDefaultCategoryId = 0;
				if ($categoryData) {
					$categoryIdsAllowed = array();
					foreach ($categoryData as $categoryId => $parentId) {
						if (in_array($categoryId, $categoryIds)) {
							$isActive = false;
							if ($parentId == 0) {
								$isActive = true;
							}
							if (!$isActive) {
								$currentParentId = $parentId;
								while ($currentParentId && isset($categoryData[$currentParentId])) {
									$nextParentId = $categoryData[$currentParentId];
									if ($nextParentId == 0) {
										$isActive = true;
									}
									$currentParentId = $nextParentId;
								}
							}
							if ($isActive) {
								$categoryIdsAllowed[] = $categoryId;
							}
						} else {
							break;
						}
					}
					if ($categoryIdsAllowed) {
						$newDefaultCategoryId = $categoryIdsAllowed[0];
						$oldDefaultCategoryId = isset($attributeValues[$storeId]) ? $attributeValues[$storeId] : 0;
						if (in_array($oldDefaultCategoryId, $categoryIdsAllowed)) {
							$newDefaultCategoryId = $oldDefaultCategoryId;
						} else if (isset($newAttributeValues[0]) && in_array($newAttributeValues[0], $categoryIdsAllowed)) {
							$newDefaultCategoryId = $newAttributeValues[0];
						}
					}
				}
				if ($storeId == 0 || $newDefaultCategoryId != $newAttributeValues[0]) {
					$newAttributeValues[$storeId] = $newDefaultCategoryId;
					if ($newDefaultCategoryId && !in_array($newDefaultCategoryId, $newDefaultCategoryIds)) {
						$newDefaultCategoryIds[] = $newDefaultCategoryId;
					}
				}
			}
		}
		
		$reset = false;
		if (count($attributeValues) > 1) {
			if ($newStoreIds = $this->_getStoreIds($newAttributeValues)) {
				foreach ($this->_getStoreIds($attributeValues) as $oldStoreId) {
					if (!in_array($oldStoreId, $newStoreIds)) {
						$reset = true;
						break;
					}
				}
			} else {
				$reset = true;
			}
			if ($reset) {
				$this->removeDefaultCategoryIds($product);
			}
		}
		
		foreach ($newAttributeValues as $storeId => $defaultCategoryId) {
			if (!isset($attributeValues[$storeId]) || $attributeValues[$storeId] != $defaultCategoryId || ($reset && $storeId == 0)) {
				$product->addAttributeUpdate($attributeCode, $defaultCategoryId, $storeId);
			}
		}
		
		$updateProductData = array();
		if ($targetCategoryIds = array_merge($defaultCategoryIds, $newDefaultCategoryIds)) {
			$addedCategoryIds = array_diff($newDefaultCategoryIds, $defaultCategoryIds);
			$removedCategoryIds = array_diff($defaultCategoryIds, $newDefaultCategoryIds);
			foreach ($targetCategoryIds as $categoryId) {
				$updateProductData[$categoryId] = array();
				if (in_array($categoryId, $addedCategoryIds)) {
					$updateProductData[$categoryId][] = 'added';
				}
				if (in_array($categoryId, $removedCategoryIds)) {
					$updateProductData[$categoryId][] = 'removed';
				}
			}
		}
		return $updateProductData;
	}
	
	/**
     * Retrieve product's Default Category IDs
	 *
     * @param Mage_Catalog_Model_Product $product
	 * @return array
     */
	public function getDefaultCategoryIds($product)
	{
		return $this->_getAttributeValuesAssocByStore($product, ZendMonk_UrlCategories_Model_Catalog_Product_Attribute_Backend_Defaultcategoryid::ATTRIBUTE_CODE);
	}
	
	/**
     * Remove product's Default Category ID attribute values
	 *
     * @param Mage_Catalog_Model_Product $product
	 * @return void
     */
	public function removeDefaultCategoryIds($product)
	{
		$product->addAttributeUpdate(ZendMonk_UrlCategories_Model_Catalog_Product_Attribute_Backend_Defaultcategoryid::ATTRIBUTE_CODE, NULL, 0);
	}
	
	/**
     * Update Product Data by Product Ids
	 *
     * @param mixed $productIds
	 * @return array
     */
	public function updateProducts($productIds = false)
	{
		$updateCategoryData = array();
		$productCollection = Mage::getModel('catalog/product')
			->getCollection();
		if ($productIds !== false) {
			$filterProductIds = is_array($productIds) ? $productIds : array($productIds);
			$productCollection->addAttributeToFilter('entity_id', array('in' => $filterProductIds));
		}
		$products = $productCollection->load();
		foreach ($products as $product) {
			$productId = $product->getEntityId();
			$updateProductData = $this->setDefaultCategoryIds($product, $product->getCategoryIds());
			$updateCategoryData = $this->getUpdateCategoryData($productId, $updateProductData, $updateCategoryData);
		}
		return $updateCategoryData;
	}
	
	/**
     * Retrieve data required for category update by Product ID and Product Data
	 *
     * @param int $productId
	 * @param array $productData
	 * @param mixed $oldCategoryData
	 * @return void
     */
	public function getUpdateCategoryData($productId, $productData, $oldCategoryData = false)
	{
		$updateCategoryData = $oldCategoryData !== false ? $oldCategoryData : array();
		foreach ($productData as $categoryId => $categoryData) {
			if (!isset($updateCategoryData[$categoryId])) {
				$updateCategoryData[$categoryId] = array();
			}
			foreach (array('added', 'removed') as $action) {
				if (in_array($action, $categoryData)) {
					if (!isset($updateCategoryData[$categoryId][$action])) {
						$updateCategoryData[$categoryId][$action] = array();
					}
					$updateCategoryData[$categoryId][$action][] = $productId;
				}
			}
		}
		return $updateCategoryData;
	}
	
	/**
     * Update Category Data
	 *
     * @param array $categoryData
	 * @param mixed $updateAll
	 * @return void
     */
	public function updateCategories($categoryData, $updateAll = false)
	{
		if ($categoryData) {
			$categoryCollection = Mage::getModel('catalog/category')
				->getCollection()
				->addAttributeToSelect(ZendMonk_UrlCategories_Model_Catalog_Category_Attribute_Backend_Defaultcategoryproductids::ATTRIBUTE_CODE);
			if (!$updateAll) {
				$categoryCollection->addAttributeToFilter('entity_id', array('in' => array_keys($categoryData)));
			}
			$defaultCategories = $categoryCollection->load();
			foreach ($defaultCategories as $category) {
				$categoryId = $category->getEntityId();
				$defaultCategoryProductIds = $this->getDefaultCategoryProductIds($category);
				if (isset($categoryData[$categoryId])) {
					if (isset($categoryData[$categoryId]['added'])) {
						$defaultCategoryProductIds = array_merge($defaultCategoryProductIds, $categoryData[$categoryId]['added']);
					}
					if (isset($categoryData[$categoryId]['removed'])) {
						$defaultCategoryProductIds = array_diff($defaultCategoryProductIds, $categoryData[$categoryId]['removed']);
					}
					$this->_setDefaultCategoryProductIds($category, $defaultCategoryProductIds);
				} else if ($updateAll) {
					$this->_setDefaultCategoryProductIds($category, $defaultCategoryProductIds, true);
				}
			}
		}
	}
}
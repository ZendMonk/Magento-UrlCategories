<?php
/**
 * Catalog product Default Category ID attribute source
 *
 * @category   ZendMonk
 * @package    ZendMonk_UrlCategories
 * @author     Carl Monk <@ZendMonk>
 */
class ZendMonk_UrlCategories_Model_Catalog_Product_Attribute_Source_Defaultcategoryid extends ZendMonk_UrlCategories_Model_Catalog_Attribute_Source_Abstract
{
	/**
     * Retrieve selection of currently available Default Category IDs by associated product
     *
     * @return array
     */
	public function getAllOptions()
    {
		$helper = Mage::helper('zendmonk_urlcategories/defaultpath');
		$options = array(array('value' => 0, 'label' => $helper->__('No Default Category Ids')));
		if ($object = $this->_getObject()) {
			$defaultCategoryIds = array_values($helper->getDefaultCategoryIds($object));
			
			$categories = Mage::getModel('catalog/category')
				->getCollection()
				->setStoreId(Mage::app()->getRequest()->getParam('store'))
				->addIdFilter($defaultCategoryIds)
				->addAttributeToSelect('name');
				
			foreach ($categories as $category) {
				$categoryId = $category->getEntityId();
				$options[] = array('value' => $categoryId, 'label' => $category->getName().' ('.$helper->__('ID: '.$categoryId).')');
			}
		}
		return $options;
    }
}
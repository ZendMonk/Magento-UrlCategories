<?php
/**
 * Catalog enhanced abstract attribute source
 *
 * @category   ZendMonk
 * @package    ZendMonk_UrlCategories
 * @author     Carl Monk <@ZendMonk>
 */
abstract class ZendMonk_UrlCategories_Model_Catalog_Attribute_Source_Abstract extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	protected $_objectData;
	
	/**
     * Retrieve associated object's Entity ID and Type by requested URL
     *
     * @return array
     */
	protected function _getObjectData()
	{
		if (isset($this->_objectData)) {
			return $this->_objectData;
		}
		
		$request = Mage::app()->getRequest();
		$controllerName = $request->getControllerName();
		if (in_array($controllerName, array('catalog_category', 'catalog_product')) && $objectId = $request->getParam('id')) {
			$objectData = array();
			$objectData['id'] = $objectId;
			$objectData['type'] = implode('/', explode('_', $controllerName));
			$this->_objectData = $objectData;
			return $objectData;
		}
		return false;
	}
	
	protected $_object;
	
	/**
     * Retrieve instance of associated object
     *
     * @return Mage_Catalog_Model_Abstract
     */
	protected function _getObject()
	{
		if (isset($this->_object)) {
			return $this->_object;
		}
		
		if ($objectData = $this->_getObjectData()) {
			$object = Mage::getModel($objectData['type'])
				->load($objectData['id']);
			$this->_object = $object;
			return $object;
		}
		return false;
	}
}
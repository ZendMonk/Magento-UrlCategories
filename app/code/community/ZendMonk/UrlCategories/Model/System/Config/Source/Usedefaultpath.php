<?php
/**
 * Use Default Path config source
 *
 * @category   ZendMonk
 * @package    ZendMonk_UrlCategories
 * @author     Carl Monk <@ZendMonk>
 */
class ZendMonk_UrlCategories_Model_System_Config_Source_Usedefaultpath
{
	/**
     * Retrieve options
     *
     * @return array
     */
	public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('zendmonk_urlcategories')->__('No')),
            array('value' => 1, 'label' => Mage::helper('zendmonk_urlcategories')->__('If Category Path isn\'t already set')),
            array('value' => 2, 'label' => Mage::helper('zendmonk_urlcategories')->__('Always'))
        );
    }
}
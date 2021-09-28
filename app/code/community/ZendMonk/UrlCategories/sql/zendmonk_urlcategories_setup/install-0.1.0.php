<?php
/**
 * @var $installer ZendMonk_UrlCategories_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_product', ZendMonk_UrlCategories_Model_Catalog_Product_Attribute_Backend_Defaultcategoryid::ATTRIBUTE_CODE, array(
	'backend'    => 'zendmonk_urlcategories/catalog_product_attribute_backend_defaultcategoryid',
	'type'       => 'int',
	'input'      => 'select',
	'label'      => 'Default Category',
	'source'     => 'zendmonk_urlcategories/catalog_product_attribute_source_defaultcategoryid',
	'required'   => 0,
	'default'    => 0,
	'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'sort_order' => 30,
	'group'	     => 'General'
));
$installer->addAttribute('catalog_category', ZendMonk_UrlCategories_Model_Catalog_Category_Attribute_Backend_Defaultcategoryproductids::ATTRIBUTE_CODE, array(
	'backend'    => 'zendmonk_urlcategories/catalog_category_attribute_backend_defaultcategoryproductids',
	'type'       => 'varchar',
	'required'   => 0,
	'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'visible'	 => 0,
	'sort_order' => 60
));
$installer->disableUpdateMode();
$installer->addAttributeValues();
$installer->endSetup();

/**
 * To uninstall, dequotate the following lines and delete the row whose 'code'-column reads 'zendmonk_urlcategories_setup' from
 * database table 'core_resource'. Then reload any page of your webstore to remove all attributes and values from your database.
 *
 * Mind that store config values won't be removed.
 *
$installer = $this;
$installer->startSetup();
$installer->disableUpdateMode();
$installer->removeAttributeValues();
$installer->removeAttribute('catalog_product', ZendMonk_UrlCategories_Model_Catalog_Product_Attribute_Backend_Defaultcategoryid::ATTRIBUTE_CODE);
$installer->removeAttribute('catalog_product', ZendMonk_UrlCategories_Model_Catalog_Category_Attribute_Backend_Defaultcategoryproductids::ATTRIBUTE_CODE);
$installer->endSetup();
 */
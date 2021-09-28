<?php
/**
 * Rewrite of Product Url model
 *
 * @category   ZendMonk
 * @package    ZendMonk_UrlCategories
 * @author     Carl Monk <@ZendMonk>
 */
class ZendMonk_UrlCategories_Model_Catalog_Product_Url extends Mage_Catalog_Model_Product_Url
{
    /**
     * Rewrite core function to evt. include Default Category Path
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $params
     * @return string
     */
    public function getUrl(Mage_Catalog_Model_Product $product, $params = array())
    {
        $url = $product->getData('url');
        if (!empty($url)) {
            return $url;
        }

        $requestPath = $product->getData('request_path');
        $categoryId = $this->_getCategoryIdForUrl($product, $params);

		$helper = Mage::helper('zendmonk_urlcategories');
		$useDefaultCategoryPath = $helper->getUseDefaultCategoryPath();
		if ($useDefaultCategoryPath && $defaultCategoryId = $helper->getDefaultCategoryId($product)) {
			if (is_null($categoryId) || ($useDefaultCategoryPath == 2 && $categoryId != $defaultCategoryId)) {
				$categoryId = $defaultCategoryId;
				$requestPath = '';
			}
		}

        if (empty($requestPath)) {
            $requestPath = $this->_getRequestPath($product, $categoryId);
            $product->setRequestPath($requestPath);
        }

        if (isset($params['_store'])) {
            $storeId = $this->_getStoreId($params['_store']);
        } else {
            $storeId = $product->getStoreId();
        }

        if ($storeId != $this->_getStoreId()) {
            $params['_store_to_url'] = true;
        }

        // reset cached URL instance GET query params
        if (!isset($params['_query'])) {
            $params['_query'] = array();
        }

        $this->getUrlInstance()->setStore($storeId);
        $productUrl = $this->_getProductUrl($product, $requestPath, $params);
        $product->setData('url', $productUrl);
        return $product->getData('url');
    }
}

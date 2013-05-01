<?
class Mage_Catalog_Helper_Map extends Mage_Core_Helper_Abstract 
{
	public function getCategoryUrl()
	{
		return $this->_getUrl('catalog/seo_sitemap/category');
	}

	public function getProductUrl()
	{
		return $this->_getUrl('catalog/seo_sitemap/product');
	}	
}
<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Sitemap
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Directory module observer
 *
 */
class Mage_Sitemap_Model_Observer
{
    const CRON_STRING_PATH = 'crontab/jobs/generate_sitemaps/schedule/cron_expr';

    const XML_PATH_ERROR_TEMPLATE  = 'sitemap/generate/error_email_template';
    const XML_PATH_ERROR_IDENTITY  = 'sitemap/generate/error_email_identity';
    const XML_PATH_ERROR_RECIPIENT = 'sitemap/generate/error_email';

    public function scheduledGenerateSitemaps($schedule)
    {
         $generateWarnings = array();

        if( !Mage::getStoreConfig(self::CRON_STRING_PATH ) ) {
            return;
        }

        $collection = Mage::getResourceModel('sitemap/sitemap_collection')
            ->load();

        foreach ($collection as $sitemap){
            try {
                if ($sitemap->getId()) {
                    $xml = $sitemap->generateSitemap();
                    $file = Mage::getBaseDir('base') . '/' . $sitemap->getSitemapPath() . '/' . $sitemap->getSitemapFilename();

                    $file = str_replace('//', '/', $file);

                    $fp = fopen($file, 'w');
                    fputs($fp, $xml);
                    fclose($fp);
                }
            } catch (Exception $e) {
                $generateWarnings[] = Mage::helper('sitemap')->__('FATAL ERROR:') . ' ' . $e->getMessage();
            }
        }

        if( sizeof($importWarnings) != 0 ) {
            /* @var $mailTamplate Mage_Core_Model_Email_Template */
            $mailTamplate = Mage::getModel('core/email_template');
            $mailTamplate->setDesignConfig(
                    array(
                        'area'  => 'backend',
                    )
                )
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_ERROR_TEMPLATE),
                    Mage::getStoreConfig(self::XML_PATH_ERROR_IDENTITY),
                    Mage::getStoreConfig(self::XML_PATH_ERROR_RECIPIENT),
                    null,
                    array(
                      'warnings'    => join("\n", $generateWarnings),
                    )
                );
        }

    }
}
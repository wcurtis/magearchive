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
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `core_config_data`;
CREATE TABLE `core_config_data` (
  `config_id` int(10) unsigned NOT NULL auto_increment,
  `scope` enum('default','websites','stores','config') NOT NULL default 'default',
  `scope_id` int(11) NOT NULL default '0',
  `path` varchar(255) NOT NULL default 'general',
  `value` text NOT NULL,
  PRIMARY KEY  (`config_id`),
  UNIQUE KEY `config_scope` (`scope`,`scope_id`,`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `core_email_template`;
CREATE TABLE `core_email_template` (
  `template_id` int(7) unsigned NOT NULL auto_increment,
  `template_code` varchar(150) default NULL,
  `template_text` text,
  `template_type` int(3) unsigned default NULL,
  `template_subject` varchar(200) default NULL,
  `template_sender_name` varchar(200) default NULL,
  `template_sender_email` varchar(200) character set latin1 collate latin1_general_ci default NULL,
  `added_at` datetime default NULL,
  `modified_at` datetime default NULL,
  PRIMARY KEY  (`template_id`),
  UNIQUE KEY `template_code` (`template_code`),
  KEY `added_at` (`added_at`),
  KEY `modified_at` (`modified_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Email templates';

insert  into `core_email_template`(`template_id`,`template_code`,`template_text`,`template_type`,`template_subject`,`template_sender_name`,`template_sender_email`,`added_at`,`modified_at`) values
(1,'New account (HTML)','               <style type=\"text/css\">\r\n           body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }\r\n      </style>\r\n\r\n<div style=\"font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;\">\r\n         <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"98%\" style=\"margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;\"\">\r\n             <tr>\r\n                    <td align=\"center\" valign=\"top\">\r\n                    <!-- [ header starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                                 <a href=\"{{store url=\"\"}}\"><img src=\"{{skin url=\"images/logo_email.gif\"}}\" alt=\"Magento\"  style=\"margin-bottom:10px;\" border=\"0\"/></a></td>\r\n                           </tr>\r\n                       </table>\r\n\r\n                    <!-- [ middle starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                             <p><strong>Dear {{var customer.name}}</strong>,<br/>\r\n                                Welcome to Magento Demo Store. To log in when visiting our site just click <a href=\"{{store url=\"customer/account/\"}}\" style=\"color:#1E7EC8;\">Login</a> or <a href=\"{{store url=\"customer/account/\"}}\" style=\"color:#1E7EC8;\">My Account</a> at the top of every page, and then enter your e-mail address and password.</p>\r\n\r\n         <p style=\"border:1px solid #BEBCB7; padding:13px 18px; background:#F8F7F5; \">\r\nUse the following values when prompted to log in:<br/>\r\nE-mail: {{var customer.email}}<br/>\r\nPassword: {{var customer.password}}<p>\r\n\r\n<p>When you log in to your account, you will be able to do the following:</p>\r\n\r\n<ul>\r\n<li>Proceed through checkout faster when making a purchase</li>\r\n<li> Check the status of orders</li>\r\n<li>View past orders</li>\r\n<li> Make changes to your account information</li>\r\n<li>Change your password</li>\r\n<li>Store alternative addresses (for shipping to multiple family members and friends!)</li>\r\n</ul>\r\n\r\n<p>If you have any questions about your account or any other matter, please feel free to contact us at \r\n<a href=\"mailto:magento@varien.com\" style=\"color:#1E7EC8;\">dummyemail@magentocommerce.com</a> or by phone at (800) DEMO-STORE.</p>\r\n<p>Thanks again!</p>\r\n\r\n\r\n                             </td>\r\n                           </tr>\r\n                       </table>\r\n                    \r\n                    </td>\r\n               </tr>\r\n           </table>\r\n            </div>\r\n',2,'Welcome, {{var customer.name}}!',NULL,NULL,NOW(),NOW()),
(2,'New order (HTML)','<style type=\"text/css\">\r\nbody,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }\r\n</style>\r\n\r\n<div style=\"font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;\">\r\n            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"98%\" style=\"margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;\"\">\r\n             <tr>\r\n                    <td align=\"center\" valign=\"top\">\r\n                    <!-- [ header starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                                 <a href=\"{{store url=\"\"}}\"><img src=\"{{skin url=\"images/logo_email.gif\"}}\" alt=\"Magento\"  style=\"margin-bottom:10px;\" border=\"0\"/></a></td>\r\n                           </tr>\r\n                       </table>\r\n\r\n                    <!-- [ middle starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                             <p><strong>Hello {{var billing.name}}</strong>,<br/>\r\n                                Thank you for your order from Magento Demo Store. Once your package ships we will send an email with a link to track your order. You can check the status of your order by <a href=\"{{store url=\"customer/account/\"}}\" style=\"color:#1E7EC8;\">logging into your account</a>. If you have any questions about your order please contact us at <a href=\"mailto:dummyemail@magentocommerce.com\" style=\"color:#1E7EC8;\">dummyemail@magentocommerce.com</a> or call us at <span class=\"nobr\">(800) DEMO-NUMBER</span> Monday - Friday, 8am - 5pm PST.</p>\r\n <p>Your order confirmation is below. Thank you again for your business.</p>\r\n\r\n                                <h3 style=\"border-bottom:2px solid #eee; font-size:1.05em; padding-bottom:1px; \">Your Order #{{var order.increment_id}} <small>(placed on {{var order.getCreatedAtFormated(\'long\')}})</small></h3>\r\n                              <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\r\n                                 <thead>\r\n                                 <tr>\r\n                                        <th align=\"left\" width=\"48.5%\" bgcolor=\"#d9e5ee\" style=\"padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;\">Billing\r\n                                       Information:</th>\r\n                                       <th width=\"3%\"></th>\r\n                                      <th align=\"left\" width=\"48.5%\" bgcolor=\"#d9e5ee\" style=\"padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;\">Payment\r\n                                       Method:</th>\r\n                                    </tr>\r\n                                   </thead>\r\n                                    <tbody>\r\n                                 <tr>\r\n                                        <td valign=\"top\" style=\"padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;\">{{var order.billing_address.getFormated(\'html\')}}</td>\r\n                                      <td>&nbsp;</td>\r\n                                     <td valign=\"top\" style=\"padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;\"> {{var order.payment.getHtmlFormated(\'private\'))}}</td>\r\n                                 </tr>\r\n                                   </tbody>\r\n                                </table><br/>\r\n                                               <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\r\n                                 <thead>\r\n                                 <tr>\r\n                                        <th align=\"left\" width=\"48.5%\" bgcolor=\"#d9e5ee\" style=\"padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;\">Shipping\r\n                                      Information:</th>\r\n                                       <th width=\"3%\"></th>\r\n                                      <th align=\"left\" width=\"48.5%\" bgcolor=\"#d9e5ee\" style=\"padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;\">Shipping\r\n                                      Method:</th>\r\n                                    </tr>\r\n                                   </thead>\r\n                                    <tbody>\r\n                                 <tr>\r\n                                        <td valign=\"top\" style=\"padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;\">{{var order.shipping_address.getFormated(\'html\')}}</td>\r\n                                     <td>&nbsp;</td>\r\n                                     <td valign=\"top\" style=\"padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;\">{{var order.shipping_description}}</td>\r\n                                   </tr>\r\n                                   </tbody>\r\n                                </table><br/>\r\n\r\n{{var items_html}}<br/>\r\n      {{var order.getEmailCustomerNote()}}\r\n                                <p>Thank you again,<br/><strong>Magento Demo Store</strong></p>\r\n\r\n\r\n                             </td>\r\n                           </tr>\r\n                       </table>\r\n\r\n                    </td>\r\n               </tr>\r\n           </table>\r\n            </div>',2,'New Order # {{var order.increment_id}}',NULL,NULL,NOW(),NOW()),
(3,'New password (HTML)','\r\n        <style type=\"text/css\">\r\n           body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }\r\n      </style>\r\n\r\n        <div style=\"font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;\">\r\n         <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"98%\" style=\"margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;\"\">\r\n             <tr>\r\n                    <td align=\"center\" valign=\"top\">\r\n                    <!-- [ header starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                                 <p><a href=\"{{store url=\"\"}}\" style=\"color:#1E7EC8;\"><img src=\"{{skin url=\"images/media/logo_email.gif\"}}\" alt=\"Magento\" border=\"0\"/></a></p></td>\r\n                            </tr>\r\n                       </table>\r\n\r\n                    <!-- [ middle starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                             <p><strong>Dear {{var customer.name}},</strong>,<br/>\r\n                               Your new password is: {{var customer.password}}</p>\r\n                                                               <p>You can change your password at any time by logging into <a href=\"{{store url=\"customer/account/\"}}\" style=\"color:#1E7EC8;\">your account</a>.<p>\r\n                             \r\n                                <p>Thank you again,<br/><strong>Magento Demo Store</strong></p>\r\n\r\n\r\n                             </td>\r\n                           </tr>\r\n                       </table>\r\n                    \r\n                    </td>\r\n               </tr>\r\n           </table>\r\n            </div>\r\n',2,'New password for {{var customer.name}}',NULL,NULL,NOW(),NOW()),
(4,'Order update (HTML)','                <style type=\"text/css\">\r\n           body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }\r\n      </style>\r\n<div style=\"font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;\">\r\n         <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"98%\" style=\"margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;\"\">\r\n             <tr>\r\n                    <td align=\"center\" valign=\"top\">\r\n                    <!-- [ header starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                                 <a href=\"{{store url=\"\"}}\"><img src=\"{{skin url=\"images/logo_email.gif\"}}\" alt=\"Magento\"  style=\"margin-bottom:10px;\" border=\"0\"/></a></td>\r\n                           </tr>\r\n                       </table>\r\n\r\n                    <!-- [ middle starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                             <p><strong>Dear {{var billing.name}}</strong>,<br/>\r\n                             Your order # {{var order.increment_id}} has been <strong>{{var order.status.frontend_label}}</strong>.</p>\r\n                              <p>Your order was shipped to:<br/><address>{{var order.shipping_address.getFormated(\'html\')}}</address>\r\n<p>If you have any questions, please feel free to contact us at \r\n<a href=\"mailto:magento@varien.com\" style=\"color:#1E7EC8;\">dummyemail@magentocommerce.com</a> or by phone at (800) DEMO-STORE.</p>\r\n\r\n\r\n <p>Thank you again,<br/><strong>Magento Demo Store</strong></p>\r\n\r\n\r\n                             </td>\r\n                           </tr>\r\n                       </table>\r\n                    \r\n                    </td>\r\n               </tr>\r\n           </table>\r\n            </div>\r\n',2,'Order # {{var order.increment_id}} update',NULL,NULL,NOW(),NOW()),
(5,'New account (Plain)','Welcome {{var customer.name}}!\r\n\r\nThank you very much for creating an account.\r\n\r\nTo officially log in when you\'re visiting our site, simply click on \"Login\" or \"My Account\" located at the top of every page, and then enter your e-mail address and the password you have chosen.\r\n\r\n==========================================\r\n\r\nUse the following values when prompted to log in:\r\n\r\nE-mail: {{var customer.email}}\r\n\r\nPassword: {{var customer.password}}\r\n\r\n==========================================\r\n\r\nWhen you log in to your account, you will be able to do the following:\r\n\r\n* Proceed through checkout faster when making a purchase\r\n\r\n* Check the status of orders\r\n\r\n* View past orders\r\n\r\n* Make changes to your account information\r\n\r\n* Change your password\r\n\r\n* Store alternative addresses (for shipping to multiple family members and friends!)\r\n\r\nIf you have any questions about your account or any other matter, please feel free to contact us at \r\nmagento@varien.com or by phone at 1-111-111-1111.\r\n\r\n\r\nThanks again!',2,'Welcome {{var customer.name}}',NULL,NULL,NOW(),NOW()),
(6,'Newsletter subscription confirmation (HTML)','       <style type=\"text/css\">\r\n           body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }\r\n      </style>\r\n\r\n        <div style=\"font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;\">\r\n         <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"98%\" style=\"margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;\"\">\r\n             <tr>\r\n                    <td align=\"center\" valign=\"top\">\r\n                    <!-- [ header starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                                 <p><a href=\"{{store url=\"\"}}\" style=\"color:#1E7EC8;\"><img src=\"{{skin url=\"images/media/logo_email.gif\"}}\" alt=\"Magento\" border=\"0\"/></a></p></td>\r\n                            </tr>\r\n                       </table>\r\n\r\n                    <!-- [ middle starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                             <p><strong>Dear {{var customer.name}},</strong>,<br/>\r\n                               Thank you for subscribing to our newsletter.</p>\r\n                                                              <p style=\"border:1px solid #BEBCB7; padding:13px 18px; background:#F8F7F5; \">To begin receiving the newsletter, you must first confirm your subscription by clicking on the following link:<br />\r\n<a href=\"{{var subscriber.getConfirmationLink()}}\" style=\"color:#1E7EC8;\">{{var subscriber.getConfirmationLink()}}</a><p>\r\n                              \r\n                                <p>Thank you again,<br/><strong>Magento Demo Store</strong></p>\r\n\r\n\r\n                             </td>\r\n                           </tr>\r\n                       </table>\r\n                    \r\n                    </td>\r\n               </tr>\r\n           </table>\r\n            </div>\r\n',2,'Newsletter subscription confirmation',NULL,NULL,NOW(),NOW()),
(7,'Share Wishlist','<style type=\"text/css\">\r\n    body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }\r\n</style>\r\n<div style=\"font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;\">\r\n    <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"98%\" style=\"margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;\">\r\n        <tr>\r\n            <td align=\"center\" valign=\"top\">\r\n            <!-- [ header starts here] -->\r\n                <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                    <tr>\r\n                        <td valign=\"top\">\r\n                            <p><a href=\"{{store url=\"\"}}\" style=\"color:#1E7EC8;\"><img src=\"{{skin url=\"images/logo_email.gif\"}}\" alt=\"Magento\" border=\"0\"/></a></p></td>\r\n                    </tr>\r\n                </table>\r\n\r\n            <!-- [ middle starts here] -->\r\n                <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                    <tr>\r\n                        <td valign=\"top\">\r\n                        <p>Hey,<br/>\r\n                        Take a look at my wishlist from Magento Demo Store.</p> \r\n\r\n<p>{{var message}}</p>\r\n\r\n                        {{var items}}\r\n\r\n                        <br/>\r\n\r\n<p><strong><a href=\"{{var addAllLink}}\" style=\"color:#DC6809;\">Add all items to shopping cart</a></strong> | <strong><a href=\"{{var viewOnSiteLink}}\" style=\"color:#1E7EC8;\">View all items in the store</a></strong></p>\r\n                        \r\n                        <p>Thank you,<br/><strong>{{var customer.name}}</strong></p>\r\n\r\n\r\n                        </td>\r\n                    </tr>\r\n                </table>\r\n            \r\n            </td>\r\n        </tr>\r\n    </table>\r\n    </div>',2,'Take a look at {{var customer.name}}\'s wishlist',NULL,NULL,NOW(),NOW()),
(8,'Newsletter Subscription Success','Newsletter Subscription Success',2,'Newsletter Subscription Success',NULL,NULL,NOW(),NOW()),
(9,'Newsletter Unsubscription Success','Newsletter Unsubscription Success',2,'Newsletter Unsubscription Success',NULL,NULL,NOW(),NOW());

DROP TABLE IF EXISTS `core_language`;
CREATE TABLE `core_language` (
  `language_code` varchar(2) NOT NULL default '',
  `language_title` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Languages';

insert  into `core_language`(`language_code`,`language_title`) values ('aa','Afar'),('ab','Abkhazian'),('af','Afrikaans'),('am','Amharic'),('ar','Arabic'),('as','Assamese'),('ay','Aymara'),('az','Azerbaijani'),('ba','Bashkir'),('be','Byelorussian'),('bg','Bulgarian'),('bh','Bihari'),('bi','Bislama'),('bn','Bengali'),('bo','Tibetan'),('br','Breton'),('ca','Catalan'),('co','Corsican'),('cs','Czech'),('cy','Welsh'),('da','Danish'),('de','German'),('dz','Bhutani'),('el','Greek'),('en','English'),('eo','Esperanto'),('es','Spanish'),('et','Estonian'),('eu','Basque'),('fa','Persian'),('fi','Finnish'),('fj','Fiji'),('fo','Faeroese'),('fr','French'),('fy','Frisian'),('ga','Irish'),('gd','Gaelic'),('gl','Galician'),('gn','Guarani'),('gu','Gujarati'),('ha','Hausa'),('hi','Hindi'),('hr','Croatian'),('hu','Hungarian'),('hy','Armenian'),('ia','Interlingua'),('ie','Interlingue'),('ik','Inupiak'),('in','Indonesian'),('is','Icelandic'),('it','Italian'),('iw','Hebrew'),('ja','Japanese'),('ji','Yiddish'),('jw','Javanese'),('ka','Georgian'),('kk','Kazakh'),('kl','Greenlandic'),('km','Cambodian'),('kn','Kannada'),('ko','Korean'),('ks','Kashmiri'),('ku','Kurdish'),('ky','Kirghiz'),('la','Latin'),('ln','Lingala'),('lo','Laothian'),('lt','Lithuanian'),('lv','Latvian'),('mg','Malagasy'),('mi','Maori'),('mk','Macedonian'),('ml','Malayalam'),('mn','Mongolian'),('mo','Moldavian'),('mr','Marathi'),('ms','Malay'),('mt','Maltese'),('my','Burmese'),('na','Nauru'),('ne','Nepali'),('nl','Dutch'),('no','Norwegian'),('oc','Occitan'),('om','Oromo'),('or','Oriya'),('pa','Punjabi'),('pl','Polish'),('ps','Pashto'),('pt','Portuguese'),('qu','Quechua'),('rm','Rhaeto-Romance'),('rn','Kirundi'),('ro','Romanian'),('ru','Russian'),('rw','Kinyarwanda'),('sa','Sanskrit'),('sd','Sindhi'),('sg','Sangro'),('sh','Serbo-Croatian'),('si','Singhalese'),('sk','Slovak'),('sl','Slovenian'),('sm','Samoan'),('sn','Shona'),('so','Somali'),('sq','Albanian'),('sr','Serbian'),('ss','Siswati'),('st','Sesotho'),('su','Sudanese'),('sv','Swahili'),('sw','Swedish'),('ta','Tamil'),('te','Tegulu'),('tg','Tajik'),('th','Thai'),('ti','Tigrinya'),('tk','Turkmen'),('tl','Tagalog'),('tn','Setswana'),('to','Tonga'),('tr','Turkish'),('ts','Tsonga'),('tt','Tatar'),('tw','Twi'),('uk','Ukrainian'),('ur','Urdu'),('uz','Uzbek'),('vi','Vietnamese'),('vo','Volapuk'),('wo','Wolof'),('xh','Xhosa'),('yo','Yoruba'),('zh','Chinese'),('zu','Zulu');

DROP TABLE IF EXISTS `core_resource`;
CREATE TABLE `core_resource` (
  `code` varchar(50) NOT NULL default '',
  `version` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Resource version registry';

DROP TABLE IF EXISTS `core_session`;
CREATE TABLE `core_session` (
  `session_id` varchar(255) NOT NULL default '',
  `website_id` smallint(5) unsigned default NULL,
  `session_expires` int(10) unsigned NOT NULL default '0',
  `session_data` text NOT NULL,
  PRIMARY KEY  (`session_id`),
  KEY `FK_SESSION_WEBSITE` (`website_id`),
  CONSTRAINT `FK_SESSION_WEBSITE` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Session data store';

DROP TABLE IF EXISTS `core_store`;
CREATE TABLE `core_store` (
  `store_id` smallint(5) unsigned NOT NULL auto_increment,
  `code` varchar(32) NOT NULL default '',
  `language_code` varchar(2) default NULL,
  `website_id` smallint(5) unsigned default '0',
  `name` varchar(32) NOT NULL default '',
  `sort_order` smallint(5) unsigned NOT NULL default '0',
  `is_active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`store_id`),
  UNIQUE KEY `code` (`code`),
  KEY `FK_STORE_LANGUAGE` (`language_code`),
  KEY `FK_STORE_WEBSITE` (`website_id`),
  KEY `is_active` (`is_active`,`sort_order`),
  CONSTRAINT `FK_STORE_LANGUAGE` FOREIGN KEY (`language_code`) REFERENCES `core_language` (`language_code`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_STORE_WEBSITE` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores';

insert  into `core_store`(`store_id`,`code`,`language_code`,`website_id`,`name`,`sort_order`,`is_active`) values (0,'default','en',0,'Default',0,1),(1,'base','en',1,'English Store',0,1);

DROP TABLE IF EXISTS `core_translate`;
CREATE TABLE `core_translate` (
  `key_id` int(10) unsigned NOT NULL auto_increment,
  `string` varchar(255) NOT NULL default '',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `translate` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`key_id`),
  UNIQUE KEY `IDX_CODE` (`string`,`store_id`),
  KEY `FK_CORE_TRANSLATE_STORE` (`store_id`),
  CONSTRAINT `FK_CORE_TRANSLATE_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Translation data';


DROP TABLE IF EXISTS `core_website`;
CREATE TABLE `core_website` (
  `website_id` smallint(5) unsigned NOT NULL auto_increment,
  `code` varchar(32) NOT NULL default '',
  `name` varchar(64) NOT NULL default '',
  `sort_order` smallint(5) unsigned NOT NULL default '0',
  `is_active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`website_id`),
  UNIQUE KEY `code` (`code`),
  KEY `is_active` (`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Websites';

insert  into `core_website`(`website_id`,`code`,`name`,`sort_order`,`is_active`) values (0,'default','Default',0,1),(1,'base','Main Website',0,1);

DROP TABLE IF EXISTS `core_layout_update`;
CREATE TABLE `core_layout_update` (
  `layout_update_id` int(10) unsigned NOT NULL auto_increment,
  `handle` varchar(255) default NULL,
  `xml` text,
  PRIMARY KEY  (`layout_update_id`),
  KEY `handle` (`handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `core_layout_link`;
CREATE TABLE `core_layout_link` (
  `layout_link_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `package` varchar(64) NOT NULL default '',
  `theme` varchar(64) NOT NULL default '',
  `layout_update_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`layout_link_id`),
  UNIQUE KEY `store_id` (`store_id`,`package`,`theme`,`layout_update_id`),
  KEY `FK_core_layout_link_update` (`layout_update_id`),
  CONSTRAINT `FK_core_layout_link_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_core_layout_link_update` FOREIGN KEY (`layout_update_id`) REFERENCES `core_layout_update` (`layout_update_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

drop table if exists `core_url_rewrite`;
create table `core_url_rewrite` (
    `url_rewrite_id` int unsigned not null auto_increment primary key,
    `store_id` smallint unsigned not null,
    `id_path` varchar(255) not null,
    `request_path` varchar(255) not null,
    `target_path` varchar(255) not null,
    `options` varchar(255) not null,
    `type` int(1) NOT NULL  DEFAULT '0',
    `description` varchar(255) NULL,
    unique (`id_path`, `store_id`),
    unique (`request_path`, `store_id`),
    key (`target_path`, `store_id`),
    foreign key (`store_id`) references `core_store` (`store_id`) on delete cascade on update cascade
) engine=InnoDB default charset=utf8;

drop table if exists `core_url_rewrite_tag`;
create table `core_url_rewrite_tag` (
    `url_rewrite_tag_id` int unsigned not null auto_increment primary key,
    `url_rewrite_id` int unsigned not null,
    `tag` varchar(255),
    unique (`tag`, `url_rewrite_id`),
    key (`url_rewrite_id`),
    foreign key (`url_rewrite_id`) references `core_url_rewrite` (`url_rewrite_id`) on delete cascade on update cascade
) engine=InnoDB default charset=utf8;

drop table if exists `core_convert_profile`;
CREATE TABLE `core_convert_profile` (
  `profile_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `actions_xml` text,
  `gui_data` text,
  `direction` enum('import','export') default NULL,
  `entity_type` varchar(64) NOT NULL default '',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `data_transfer` enum('file', 'interactive'),
  PRIMARY KEY  (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

drop table if exists `core_convert_history`;
CREATE TABLE `core_convert_history` (
  `history_id` int(10) unsigned NOT NULL auto_increment,
  `profile_id` int(10) unsigned NOT NULL default '0',
  `action_code` varchar(64) default NULL,
  `user_id` int(10) unsigned NOT NULL default '0',
  `performed_at` datetime default NULL,
  PRIMARY KEY  (`history_id`),
  KEY `FK_core_convert_history` (`profile_id`),
  CONSTRAINT `FK_core_convert_history` FOREIGN KEY (`profile_id`) REFERENCES `core_convert_profile` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


drop table if exists `design_change`;
CREATE TABLE `design_change` (
`design_change_id` INT NOT NULL AUTO_INCREMENT,
`store_id` smallint(5) unsigned NOT NULL ,
`package` VARCHAR( 255 ) NOT NULL ,
`theme` VARCHAR( 255 ) NOT NULL ,
`date_from` DATE NOT NULL ,
`date_to` DATE NOT NULL,
KEY `FK_DESIGN_CHANGE_STORE` (`store_id`),
PRIMARY KEY  (`design_change_id`)
) ENGINE = innodb;

ALTER TABLE `design_change`
  ADD
  CONSTRAINT `FK_DESIGN_CHANGE_STORE`
   FOREIGN KEY (`store_id`)
   REFERENCES `core_store` (`store_id`);
");

$installer->endSetup();

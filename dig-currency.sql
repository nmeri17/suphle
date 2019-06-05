-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 05, 2019 at 07:44 AM
-- Server version: 5.7.17
-- PHP Version: 7.3.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dig-currency`
--

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `mode` enum('Naira','percentage','crypto') NOT NULL,
  `amount` float NOT NULL,
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `name`, `mode`, `amount`, `creationDate`) VALUES
(2, '75a4b37', 'Naira', 22, '2019-04-17 11:48:08'),
(3, '7263923', 'crypto', 0.33, '2019-04-17 11:50:14');

-- --------------------------------------------------------

--
-- Table structure for table `ecurrencies`
--

DROP TABLE IF EXISTS `ecurrencies`;
CREATE TABLE IF NOT EXISTS `ecurrencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `currencyFrom` varchar(50) NOT NULL,
  `currencyTo` varchar(20) NOT NULL,
  `minimumTransactionCost` float NOT NULL,
  `maximumTransactionCost` int(11) NOT NULL,
  `approvalMode` enum('manual','automatic') NOT NULL DEFAULT 'automatic',
  `priceToday` int(11) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`currencyFrom`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ecurrencies`
--

INSERT INTO `ecurrencies` (`id`, `currencyFrom`, `currencyTo`, `minimumTransactionCost`, `maximumTransactionCost`, `approvalMode`, `priceToday`, `visible`) VALUES
(1, 'BTC', 'PDP', 0.002, 5, 'manual', 450, 1),
(2, 'ETH', 'APC', 0.0125, 8, 'automatic', 355, 1);

-- --------------------------------------------------------

--
-- Table structure for table `page`
--

DROP TABLE IF EXISTS `page`;
CREATE TABLE IF NOT EXISTS `page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `view-name` varchar(100) NOT NULL DEFAULT 'static',
  `title` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `custom_scripts` varchar(1000) NOT NULL,
  `content` mediumtext,
  `nav_indicator` varchar(50) NOT NULL,
  `keywords` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `page`
--

INSERT INTO `page` (`id`, `name`, `view-name`, `title`, `description`, `custom_scripts`, `content`, `nav_indicator`, `keywords`) VALUES
(1, 'index', 'index', 'Tilwa', '', '', '<div><ul><li><b>Test Run:</b></li></ul><div><br></div><ol><li>you\'re signed in as admin. continue to <a href=\"/profile\" target=\"_self\">profile</a> or</li><li>change user token on line 26 of /model/Model.php</li><li>post testimony from your dashboard</li><li>perform a trade transaction from the nav bar</li><li>return to admin account and confirm all actions as user works</li><li>test other endpoints appearing at admin\'s panel. Almost every button/cell there is connected to a behavior</li><li>test <i>utilities</i> and <i>buy data</i> from the nav bar or <a href=\"/utilities\" target=\"_self\">click here</a></li></ol><div><u><br></u></div><div><u>Notes</u><div><u><br></u></div><ul><li>Admin cannot trade</li><li>Testimonies are irreversible i.e once admin approves, that user can\'t testify again. You may notice similar actions already baked in. To test their functionality, you can create another user account from phpmyadmin and test those features from your new account. Just remember to place the new account\'s id on line 26 of model.php.</li><li>You may find some tables without save buttons. Alter its rows as desired, their values will be updated in real time</li></ul></div></div><div><u><br></u></div><div><u><br></u></div><div><u>New Content</u></div><div><br></div><div>To edit this page, from the control panel &gt; edit pages, type in <i>index. </i>That\'s the homepage. Every other page can be accessed from its link i.e. site-name/folder/utilities will be edited with <i>utilities</i></div><div><br></div><div>User registration/login come last, so fear not about manually copying IDs</div><div><u><br></u></div><div><u><br></u></div><div><u>Contributing</u></div><div><br></div><div>To alter template structure and fine-tune to taste, see examples of the front end syntax in the <i>views</i> folder. To mess around with the models and supply desired data, please see the <a href=\"/lib/Templating/readme.md\" target=\"_blank\">docs</a></div><div><br></div><div><br></div><div><u>To-do</u><ul><li>Pagination was implemented but untested because we don\'t have enough data for that yet. Control panel pages with numerous rows will have its pagination visible much later</li></ul></div>', 'active_index', ''),
(11, 'utilities', 'utilities', 'Pay a merchant', '', '', NULL, 'active_utilities', NULL),
(12, 'buy-data', 'buy-data', 'Purchase airtime and data', '', '', NULL, 'active_buy_data', NULL),
(13, 'trade', 'trade', 'Buy and sell currency', '', '', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod\ntempor incididunt ut labore et dolore magna aliqua.</p>', 'active_trade', NULL),
(14, 'profile', 'dashboard', 'profile', '', '<link rel=\"stylesheet\" type=\"text/css\" href=\"/assets/profile.css\">', NULL, 'active_profile', NULL),
(5, 'faq', 'static', 'Frequently Asked Questions', '', '', NULL, 'active_faq', NULL),
(6, '404', 'static', 'The Boardman', '', '', NULL, 'active_404', NULL),
(7, 'privacy', 'static', 'The Boardman', '', '', NULL, 'active_privacy', NULL),
(8, 'tos', 'static', 'The Boardman', '', '', NULL, 'active_tos', NULL),
(9, 'producthunt', 'static', '', '', '', NULL, '0', NULL),
(10, 'betalist', 'static', '', '', '', NULL, '0', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buy` varchar(100) NOT NULL DEFAULT 'none',
  `sell` varchar(100) NOT NULL DEFAULT 'none',
  `data` varchar(100) NOT NULL DEFAULT 'none',
  `airtime` varchar(100) NOT NULL DEFAULT 'none',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `buy`, `sell`, `data`, `airtime`) VALUES
(1, 'photoIds,utilityBills', 'none', 'none', 'none');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fromId` varchar(100) NOT NULL,
  `content` varchar(400) NOT NULL,
  `date` datetime NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `from` (`fromId`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `fromId`, `content`, `date`, `approved`) VALUES
(1, 'user5c8fa2581b4f9', 'do you know what da load hath done for me?', '2019-04-08 22:31:18', 1);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `initiator` varchar(50) NOT NULL,
  `currencyFrom` varchar(30) NOT NULL,
  `currencyTo` varchar(30) NOT NULL,
  `mode` enum('buy','sell','airtime','data') NOT NULL,
  `amount` int(11) NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `approvedBy` varchar(100) DEFAULT NULL,
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  `vendorRes` varchar(1500) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `initiator`, `currencyFrom`, `currencyTo`, `mode`, `amount`, `approved`, `approvedBy`, `expired`, `vendorRes`, `date`) VALUES
(3, 'user5c8fa2581b4f9', 'BTC', 'PDP', 'sell', 45, 1, 'user5c8fa27da2340', 0, NULL, '2019-04-06 13:57:04'),
(4, 'user5c8fa27da2340', 'Naira', 'MTN', 'airtime', 4, 1, NULL, 0, NULL, '2019-04-06 13:58:11'),
(5, 'user5c8fa27da2340', 'ETH', 'APC', 'buy', 0, 1, NULL, 0, NULL, '2019-04-06 18:52:58'),
(6, 'user5c8fa2581b4f9', 'BTC', 'PDP', 'buy', 0, 1, 'user5c8fa27da2340', 0, NULL, '2019-04-06 19:03:49'),
(7, 'user5c8fa27da2340', 'ETH', 'PDP', 'sell', 2, 1, NULL, 0, NULL, '2019-04-08 04:27:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(20) NOT NULL,
  `password` varchar(200) NOT NULL,
  `firstName` varchar(20) NOT NULL,
  `lastName` varchar(20) NOT NULL,
  `referredBy` varchar(50) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `phone` bigint(20) NOT NULL,
  `bankName` varchar(150) NOT NULL,
  `accountNumber` bigint(20) NOT NULL,
  `regDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` enum('user','juniorAdmin','admin') NOT NULL DEFAULT 'user',
  `balance` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `accountNumber` (`accountNumber`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `userId`, `password`, `firstName`, `lastName`, `referredBy`, `email`, `phone`, `bankName`, `accountNumber`, `regDate`, `role`, `balance`) VALUES
(1, 'user5c8fa2581b4f9', '', 'nmeri', 'chukwu', 'user5c8fa26469049', 'vainglories17@gmail.com', 7086994594, 'UBN', 43714443, '2019-02-11 08:00:00', 'user', 42),
(2, 'user5c8fa26469049', '', 'user', 'two', NULL, 'nmeri17@gmail.com', 7039841657, 'GTB', 43724435, '2019-01-28 15:06:23', 'user', 0),
(3, 'user5c8fa27da2340', '', 'user', 'three', NULL, 'admin@tilwa.com', 8035674774, 'UBA', 78938980, '2019-01-20 02:14:16', 'admin', 99916);

-- --------------------------------------------------------

--
-- Table structure for table `vtu-data-catalog`
--

DROP TABLE IF EXISTS `vtu-data-catalog`;
CREATE TABLE IF NOT EXISTS `vtu-data-catalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `packageValue` varchar(70) NOT NULL,
  `network` enum('mtn','airtel','glo','9mobile') NOT NULL,
  `price` int(5) NOT NULL,
  `validity` float NOT NULL COMMENT 'in days',
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `vtu-data-catalog`
--

INSERT INTO `vtu-data-catalog` (`id`, `packageValue`, `network`, `price`, `validity`, `visible`) VALUES
(1, '200mb', '9mobile', 300, 1, 1),
(2, '100mb', 'glo', 200, 3, 0),
(3, '15mb', 'mtn', 50, 1, 1),
(4, '150mb', 'airtel', 250, 5, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

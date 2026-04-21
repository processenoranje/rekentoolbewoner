-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2026 at 10:44 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bewoner`
--
CREATE DATABASE IF NOT EXISTS `bewoner` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bewoner`;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','editor') NOT NULL DEFAULT 'admin',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `household_data`
--

CREATE TABLE `household_data` (
  `id` int(11) NOT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `huisnummer` varchar(10) DEFAULT NULL,
  `toevoeging` varchar(10) DEFAULT NULL,
  `zonnepanelen` tinyint(1) DEFAULT 0,
  `preset` varchar(10) DEFAULT NULL,
  `verbruik` int(11) DEFAULT NULL,
  `opwek` int(11) DEFAULT NULL,
  `data_source` enum('preset','custom') DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `household_data`
--

INSERT INTO `household_data` (`id`, `postcode`, `huisnummer`, `toevoeging`, `zonnepanelen`, `preset`, `verbruik`, `opwek`, `data_source`, `submitted_at`) VALUES
(1, '5959mm', '23', '', 1, '2', 3500, 3000, 'preset', '2026-04-14 07:37:17'),
(2, '', '', '', 1, '4', 3500, 3000, 'preset', '2026-04-14 12:29:15'),
(3, '', '', '', 1, '4', 3500, 3000, 'preset', '2026-04-14 12:29:26'),
(4, '', '', '', 1, '4', 3500, 3000, 'preset', '2026-04-14 12:29:37'),
(5, '', '', '', 1, '3', 3500, 3000, 'preset', '2026-04-21 06:58:08'),
(6, '', '', '', 1, '3', 3500, 3000, 'preset', '2026-04-21 06:58:14'),
(7, '', '', '', 1, '3', 3500, 3000, 'preset', '2026-04-21 06:58:19');

-- --------------------------------------------------------

--
-- Table structure for table `page_content`
--

CREATE TABLE `page_content` (
  `id` int(11) NOT NULL,
  `section_key` varchar(100) NOT NULL,
  `content` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_content`
--

INSERT INTO `page_content` (`id`, `section_key`, `content`, `updated_at`, `active`) VALUES
(15, 'omschrijving', '<h1>Rekentool Bewoner</h1>\r\n        <div>\r\n          <p>In 2027 stopt het salderen. Dit betekent dat je moet betalen voor de stroom die je opwekt, maar niet zelf gebruikt. Daardoor kunnen je energiekosten met honderden euro’s per jaar stijgen. </p>\r\n \r\n          <p>Wij bieden hiervoor een oplossing: energie delen. De stroom die je zelf niet gebruikt, kun je doneren aan het Energieschap. In ruil daarvoor krijg je belastingvoordeel op je inkomstenbelasting en help je tegelijkertijd je buurman. Zo voorkom je dat je rekening hoog oploopt.</p>\r\n \r\n          <p>Wil je weten wat het stoppen van het salderen voor jou betekent? Gebruik dan de tool hiernaast.</p>\r\n\r\n          <p>* Let op, deze tool geeft een indicatie, aan de uitkomsten kunnen geen rechten worden ontleend.</p>', '2026-04-14 12:20:27', 1),
(25, 'pakket0', 'Totale pakketscore', '2026-04-20 09:46:07', 1),
(26, 'pakket1', 'Totale pakketscore', '2026-04-20 09:45:57', 1),
(27, 'pakket2', 'Klantwaardering', '2026-04-20 09:45:13', 1),
(28, 'pakket3', 'Leverancierswaardering', '2026-04-20 09:44:33', 1),
(29, 'pakket4', 'Pakketwaardering', '2026-04-20 09:43:54', 1),
(30, 'overzicht0', 'Overzicht', '2026-04-20 09:42:59', 1),
(31, 'overzicht1', 'Keuzes voor 2027', '2026-04-20 09:42:58', 1),
(32, 'overzicht2', 'Totale jaarkosten<br>\r\n(incl. btw)', '2026-04-20 09:42:23', 1),
(33, 'overzicht3', 'Uitwerking kosten', '2026-04-20 09:41:47', 1),
(34, 'overzicht4', 'Toelichting keuze', '2026-04-20 09:40:58', 1),
(35, 'overzicht5', 'Investering', '2026-04-20 09:40:20', 1),
(36, 'overzicht6', 'Gebruik van zonne-energie', '2026-04-20 09:39:42', 1),
(37, 'overzicht7', 'Omgang met stroomoverschot', '2026-04-20 09:39:04', 1),
(38, 'overzicht8', 'Techniek & beheer', '2026-04-20 09:38:25', 1),
(39, 'overzicht9', 'Individueel vs collectief', '2026-04-20 09:37:48', 1),
(40, 'overzicht10', 'Duurzaamheid & netbelasting', '2026-04-20 09:37:13', 1),
(41, 'overzicht11', 'Voor wie geschikt', '2026-04-20 09:36:27', 1),
(42, 'overzicht12', 'Contract beëindigen', '2026-04-20 09:35:45', 1),
(45, 'overzicht13', 'Opzegtermijn', '2026-04-20 09:35:03', 1),
(46, 'pakket1a', '<div class=\"pv--\">\r\n<span class=\"c-rating-total c-spec c-spec--rating u-display--inline-block\">\r\n\r\n8<span class=\"c-spec__decimals\">,6</span>\r\n<span class=\"c-rating-total__svg\"><svg viewBox=\"0 0 61 61\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M28.195 58.036l-1.345.559a5.354 5.354 0 01-6.175-1.522l-.93-1.12a6 6 0 00-4.083-2.143l-1.45-.13a5.354 5.354 0 01-4.76-4.217l-.304-1.424a6 6 0 00-2.619-3.794l-1.224-.789A5.354 5.354 0 013.05 37.51l.393-1.401a6 6 0 00-.556-4.577l-.717-1.267a5.354 5.354 0 01.767-6.314l1-1.059a6 6 0 001.634-4.31l-.046-1.456a5.354 5.354 0 013.613-5.234l1.377-.473a6 6 0 003.451-3.057l.635-1.31a5.354 5.354 0 015.632-2.956l1.439.221a6 6 0 004.477-1.103l1.171-.865a5.354 5.354 0 016.36 0l1.171.865a6 6 0 004.477 1.103l1.44-.221a5.354 5.354 0 015.63 2.956l.636 1.31a6 6 0 003.45 3.057l1.378.473a5.354 5.354 0 013.613 5.234l-.046 1.455a6 6 0 001.635 4.311l1 1.059a5.354 5.354 0 01.766 6.314l-.717 1.267a6 6 0 00-.556 4.577l.393 1.401a5.354 5.354 0 01-2.255 5.947l-1.224.789a6 6 0 00-2.62 3.794l-.303 1.424a5.354 5.354 0 01-4.76 4.218l-1.45.13a6 6 0 00-4.083 2.142l-.93 1.12a5.354 5.354 0 01-6.175 1.522l-1.345-.56a6 6 0 00-4.61 0z\" fill=\"#61A38D\" class=\"u-svg-fill\"></path><circle stroke=\"#F7F7F7\" cx=\"30.5\" cy=\"30.5\" r=\"22.961\"></circle></svg></span>\r\n</span>\r\n</div>', '2026-04-20 09:45:49', 1),
(47, 'pakket1b', '<div class=\"pv-- c-comparison__highlight\">\r\n<span class=\"c-rating-total c-spec c-spec--rating u-display--inline-block\">\r\n\r\n8<span class=\"c-spec__decimals\">,7</span>\r\n<span class=\"c-rating-total__svg\"><svg viewBox=\"0 0 61 61\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M28.195 58.036l-1.345.559a5.354 5.354 0 01-6.175-1.522l-.93-1.12a6 6 0 00-4.083-2.143l-1.45-.13a5.354 5.354 0 01-4.76-4.217l-.304-1.424a6 6 0 00-2.619-3.794l-1.224-.789A5.354 5.354 0 013.05 37.51l.393-1.401a6 6 0 00-.556-4.577l-.717-1.267a5.354 5.354 0 01.767-6.314l1-1.059a6 6 0 001.634-4.31l-.046-1.456a5.354 5.354 0 013.613-5.234l1.377-.473a6 6 0 003.451-3.057l.635-1.31a5.354 5.354 0 015.632-2.956l1.439.221a6 6 0 004.477-1.103l1.171-.865a5.354 5.354 0 016.36 0l1.171.865a6 6 0 004.477 1.103l1.44-.221a5.354 5.354 0 015.63 2.956l.636 1.31a6 6 0 003.45 3.057l1.378.473a5.354 5.354 0 013.613 5.234l-.046 1.455a6 6 0 001.635 4.311l1 1.059a5.354 5.354 0 01.766 6.314l-.717 1.267a6 6 0 00-.556 4.577l.393 1.401a5.354 5.354 0 01-2.255 5.947l-1.224.789a6 6 0 00-2.62 3.794l-.303 1.424a5.354 5.354 0 01-4.76 4.218l-1.45.13a6 6 0 00-4.083 2.142l-.93 1.12a5.354 5.354 0 01-6.175 1.522l-1.345-.56a6 6 0 00-4.61 0z\" fill=\"#61A38D\" class=\"u-svg-fill\"></path><circle stroke=\"#F7F7F7\" cx=\"30.5\" cy=\"30.5\" r=\"22.961\"></circle></svg></span>\r\n</span>\r\n</div>', '2026-04-20 09:45:39', 1),
(48, 'pakket1c', '<div class=\"pv--\">\r\n<span class=\"c-rating-total c-spec c-spec--rating u-display--inline-block\">\r\n\r\n8<span class=\"c-spec__decimals\">,6</span>\r\n<span class=\"c-rating-total__svg\"><svg viewBox=\"0 0 61 61\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M28.195 58.036l-1.345.559a5.354 5.354 0 01-6.175-1.522l-.93-1.12a6 6 0 00-4.083-2.143l-1.45-.13a5.354 5.354 0 01-4.76-4.217l-.304-1.424a6 6 0 00-2.619-3.794l-1.224-.789A5.354 5.354 0 013.05 37.51l.393-1.401a6 6 0 00-.556-4.577l-.717-1.267a5.354 5.354 0 01.767-6.314l1-1.059a6 6 0 001.634-4.31l-.046-1.456a5.354 5.354 0 013.613-5.234l1.377-.473a6 6 0 003.451-3.057l.635-1.31a5.354 5.354 0 015.632-2.956l1.439.221a6 6 0 004.477-1.103l1.171-.865a5.354 5.354 0 016.36 0l1.171.865a6 6 0 004.477 1.103l1.44-.221a5.354 5.354 0 015.63 2.956l.636 1.31a6 6 0 003.45 3.057l1.378.473a5.354 5.354 0 013.613 5.234l-.046 1.455a6 6 0 001.635 4.311l1 1.059a5.354 5.354 0 01.766 6.314l-.717 1.267a6 6 0 00-.556 4.577l.393 1.401a5.354 5.354 0 01-2.255 5.947l-1.224.789a6 6 0 00-2.62 3.794l-.303 1.424a5.354 5.354 0 01-4.76 4.218l-1.45.13a6 6 0 00-4.083 2.142l-.93 1.12a5.354 5.354 0 01-6.175 1.522l-1.345-.56a6 6 0 00-4.61 0z\" fill=\"#61A38D\" class=\"u-svg-fill\"></path><circle stroke=\"#F7F7F7\" cx=\"30.5\" cy=\"30.5\" r=\"22.961\"></circle></svg></span>\r\n</span>\r\n</div>', '2026-04-20 09:45:26', 1),
(49, 'pakket2a', '<div class=\"pv-- c-comparison__highlight\">\r\n<span class=\"c-rating-circle c-spec\">\r\n\r\n7<span class=\"c-spec__decimals\">,7</span>\r\n</span>\r\n</div>', '2026-04-20 09:45:05', 1),
(50, 'pakket2b', '<div class=\"pv-- c-comparison__highlight\">\r\n<span class=\"c-rating-circle c-spec\">\r\n\r\n7<span class=\"c-spec__decimals\">,9</span>\r\n</span>\r\n</div>', '2026-04-20 09:44:53', 1),
(51, 'pakket2c', '<div class=\"pv-- c-comparison__highlight\">\r\n<span class=\"c-rating-circle c-spec\">\r\n\r\n7<span class=\"c-spec__decimals\">,4</span>\r\n</span>\r\n</div>', '2026-04-20 09:44:45', 1),
(52, 'pakket3a', '<div class=\"pv-- c-comparison__highlight\">\r\n<span class=\"c-rating-circle c-spec\">\r\n\r\n9<span class=\"c-spec__decimals\">,4</span>\r\n</span>\r\n</div>', '2026-04-20 09:44:26', 1),
(53, 'pakket3b', '<div class=\"pv--\">\r\n<span class=\"c-rating-circle c-spec\">\r\n\r\n9<span class=\"c-spec__decimals\">,3</span>\r\n</span>\r\n</div>', '2026-04-20 09:44:16', 1),
(54, 'pakket3c', '<div class=\"pv--\">\r\n<span class=\"c-rating-circle c-spec\">\r\n\r\n9<span class=\"c-spec__decimals\">,3</span>\r\n</span>\r\n</div>', '2026-04-20 09:44:08', 1),
(55, 'pakket4a', '<div class=\"pv-- c-comparison__highlight\">\r\n<span class=\"c-rating-circle c-spec\">\r\n\r\n8<span class=\"c-spec__decimals\">,7</span>\r\n</span>\r\n</div>', '2026-04-20 09:43:46', 1),
(56, 'pakket4b', '<div class=\"pv--\">\r\n<span class=\"c-rating-circle c-spec\">\r\n\r\n9<span class=\"c-spec__decimals\">,0</span>\r\n</span>\r\n</div>', '2026-04-20 09:43:36', 1),
(57, 'pakket4c', '<div class=\"pv--\">\r\n<span class=\"c-rating-circle c-spec\">\r\n\r\n9<span class=\"c-spec__decimals\">,0</span>\r\n</span>\r\n</div>', '2026-04-20 09:43:27', 1),
(58, 'overzicht2a', '<label for=\"\" class=\"totaal\">Totaal per jaar:\r\n                    <input type=\"text\" id=\"totaalz\" name=\"\" class=\"inputresult\" value=\"695.50\" readonly>\r\n                    </label>', '2026-04-20 09:42:09', 1),
(59, 'overzicht2b', '<label for=\"\" class=\"totaal\">Totaal per jaar:\r\n                    <input type=\"text\" id=\"totaalm\" name=\"\" class=\"inputresult\" value=\"401.20\" readonly>\r\n                    </label>', '2026-04-20 09:42:08', 1),
(60, 'overzicht2c', '<label for=\"\" class=\"totaal\">Totaal per jaar:\r\n                    <input type=\"text\" id=\"totaalthuis\" name=\"\" class=\"inputresult\" value=\"0.00\" readonly>\r\n                    </label>', '2026-04-20 09:42:00', 1),
(61, 'overzicht3a', '<h3>Tarieven</h3>\r\n                    <label for=\"\">Energie kosten:\r\n                    <input type=\"text\" id=\"jaarverbrz\" name=\"\" class=\"inputresult\" value=\"676.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\">Terugleverkosten:\r\n                    <input type=\"text\" id=\"terugleverkostz\" name=\"\" class=\"inputresult\" value=\"94.50\" readonly>\r\n                    </label>\r\n                    <label for=\"\">Terugleververgoeding:\r\n                    <input type=\"text\" id=\"terugleververgz\" name=\"\" class=\"inputresult\" value=\"-105.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\" class=\"inactive\">Buurtstroom kosten:\r\n                    <input type=\"text\" id=\"\" name=\"\" class=\"inputresult inactive\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\" class=\"inactive\">Terugleverkosten buurtstroom:\r\n                    <input type=\"text\" id=\"\" name=\"\" class=\"inputresult inactive\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\" class=\"inactive\">Korting inkomstenbelasting:\r\n                    <input type=\"text\" id=\"\" name=\"\" class=\"inputresult inactive\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <h3>Lidmaatschap</h3>\r\n                    <label for=\"\" class=\"inactive\">Lidmaatschapskosten:\r\n                    <input type=\"text\" id=\"participkost\" name=\"\" class=\"inputresult inactive\" value=\"0.00\" readonly>\r\n                    </label>', '2026-04-20 09:41:38', 1),
(62, 'overzicht3b', '<h3 style=\"display: none;\">Opwek en verbruik</h3>\r\n                    <label for=\"\" style=\"display: none;\">Gemiddeld gelijktijdig verbruik:\r\n                    <input type=\"text\" id=\"gelijkt\" name=\"\" class=\"inputresult\" value=\"40 %\" readonly>\r\n                    </label>\r\n\r\n                    <h3>Tarieven</h3>\r\n                    <label for=\"\" class=\"inactive\">Energie kosten:\r\n                    <input type=\"text\" id=\"\" name=\"\" class=\"inputresult inactive\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\" class=\"inactive\">Terugleverkosten:\r\n                    <input type=\"text\" id=\"\" name=\"\" class=\"inputresult inactive\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\" class=\"inactive\">Terugleververgoeding:\r\n                    <input type=\"text\" id=\"\" name=\"\" class=\"inputresult inactive\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\">Buurtstroom kosten:\r\n                    <input type=\"text\" id=\"tariefbuurtstr\" name=\"\" class=\"inputresult\" value=\"483.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\">Terugleverkosten buurtstroom:\r\n                    <input type=\"text\" id=\"terugleverbuurtm\" name=\"\" class=\"inputresult\" value=\"16.20\" readonly>\r\n                    </label>\r\n                    <label for=\"\">Korting inkomstenbelasting:\r\n                    <input type=\"text\" id=\"inkomstbelast\" name=\"\" class=\"inputresult\" value=\"-162.00\" readonly>\r\n                    </label>\r\n                    <h3>Lidmaatschap</h3>\r\n                    <label for=\"\">Lidmaatschapskosten:\r\n                    <input type=\"text\" id=\"participkostm\" name=\"\" class=\"inputresult\" value=\"24.00\" readonly>\r\n                    </label>', '2026-04-20 09:41:29', 1),
(63, 'overzicht3c', '<h3 style=\"display: none;\">Opwek en verbruik</h3>\r\n\r\n                    <h3>Tarieven</h3>\r\n                    <label for=\"\">Energie kosten:\r\n                    <input type=\"text\" id=\"jaarverbth\" name=\"\" class=\"inputresult\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\">Terugleverkosten:\r\n                    <input type=\"text\" id=\"terugleverkostth\" name=\"\" class=\"inputresult\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\">Terugleververgoeding:\r\n                    <input type=\"text\" id=\"terugleververgth\" name=\"\" class=\"inputresult\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\" class=\"inactive\">Buurtstroom kosten:\r\n                    <input type=\"text\" id=\"\" name=\"\" class=\"inputresult inactive\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\" class=\"inactive\">Terugleverkosten buurtstroom:\r\n                    <input type=\"text\" id=\"\" name=\"\" class=\"inputresult inactive\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <label for=\"\" class=\"inactive\">Korting inkomstenbelasting:\r\n                    <input type=\"text\" id=\"\" name=\"\" class=\"inputresult inactive\" value=\"0.00\" readonly>\r\n                    </label>\r\n                    <h3>Lidmaatschap</h3>\r\n                    <label for=\"\" class=\"inactive\">Lidmaatschapskosten:\r\n                    <input type=\"text\" id=\"\" name=\"\" class=\"inputresult inactive\" value=\"0.00\" readonly>\r\n                    </label>', '2026-04-20 09:41:18', 1),
(64, 'overzicht4a', '<b>Wat betekent dit?</b>\r\nJe verandert niets aan je huidige situatie. De stroom die je niet direct zelf gebruikt, gaat terug het net op.\r\n<p>\r\n<b>Gevolgen vanaf 2027</b>\r\n<ul>\r\n  <li>Het salderen stopt: je betaalt voor afname en krijgt minder terug voor teruglevering.</li>\r\n  <li>Terugleverkosten kunnen flink oplopen.</li>\r\n  <li>Je hebt weinig grip op je energierekening.</li>\r\n</ul>\r\n</p>\r\n<b>Voor wie passend?</b>\r\nVoor huishoudens die (nog) niets willen of kunnen aanpassen, maar wel de hogere kosten accepteren.', '2026-04-20 09:40:51', 1),
(65, 'overzicht4b', '<b>Wat betekent dit?</b>\r\nJe deelt lokaal opgewekte stroom met je buurt, met hulp van een buurtbatterij en slimme meterkasten. Overschotten worden opgeslagen of direct in de wijk gebruikt.\r\n<p>\r\n<b>Voordelen</b>\r\n<ul>\r\n  <li>Je benut lokaal opgewekte energie optimaal.</li>\r\n  <li>Minder teruglevering aan het net = lagere kosten.</li>\r\n  <li>Je krijgt belastingvoordeel via energie delen.</li>\r\n  <li>Iedereen in de buurt profiteert mee.</li>\r\n</ul>\r\n</p>\r\n<b>Waarom dit past bij de stichting</b>\r\nStichting Oranje Advies 2051 ziet energie als een ecosysteemdienst: lokaal, eerlijk en sociaal georganiseerd, waarbij bewoners samen sterker staan.\r\n\r\n<p><b>Voor wie passend?</b>\r\nVoor bewoners die samen willen investeren in een eerlijke energietransitie, zonder zelf alles individueel te hoeven regelen.</p>', '2026-04-20 09:40:41', 1),
(66, 'overzicht4c', '<b>Wat betekent dit?</b>\r\nJe slaat opgewekte stroom op in een batterij in je eigen woning en gebruikt deze later zelf.\r\n<p><b>Voordelen</b>\r\n<ul>\r\n<li>Meer eigen verbruik van zonne-energie.</li>\r\n<li>Minder teruglevering aan het net.</li>\r\n</ul>\r\n<b>Aandachtspunten</b>\r\n<ul>\r\n  <li>Hoge aanschafkosten.</li>\r\n  <li>Beperkte capaciteit: niet alle overschotten zijn op te slaan.</li>\r\n  <li>Het voordeel is volledig individueel; de buurt profiteert niet mee.</li>\r\n</ul>\r\n</p>\r\n<b>Voor wie passend?</b>\r\nVoor huishoudens die individueel willen optimaliseren en bereid zijn daarin zelf te investeren.', '2026-04-20 09:40:31', 1),
(67, 'overzicht5a', '<b>Investering</b>\r\nGeen investering.\r\n<p><b>Afbetaaltermijn</b>\r\nNiet van toepassing.</p>\r\n<b>Toelichting</b>\r\nJe maakt geen kosten vooraf, maar blijft volledig afhankelijk van energietarieven en terugleverkosten na het stoppen van salderen.', '2026-04-20 09:40:13', 1),
(68, 'overzicht5b', '<b>Investering</b>\r\nIndicatief €3.000 per woning via deelname aan het energieschap.\r\n<p><b>Afbetaaltermijn</b>\r\n\r\n20% eigen inleg bij start\r\nResterend bedrag via maandelijkse afbetaling\r\nAflossing loopt via het lidmaatschap van het energieschap</p>\r\n\r\n<b>Toelichting</b>\r\nDe investering is collectief georganiseerd. Bewoners profiteren direct van lagere energiekosten en belastingvoordeel terwijl de kosten gespreid worden over de looptijd.', '2026-04-20 09:40:04', 1),
(69, 'overzicht5c', '<b>Investering</b>\r\nHoge eenmalige investering, volledig voor eigen rekening.\r\n\r\n<p><b>Afbetaaltermijn</b>\r\nGeen standaard afbetaalregeling; kosten worden vooraf of via externe financiering betaald.</p>\r\n<b>Toelichting</b>\r\nDe investering en het financiële voordeel zijn volledig individueel. Onderhoud, vervanging en risico’s liggen bij de bewoner zelf.', '2026-04-20 09:39:54', 1),
(70, 'overzicht6a', 'Zonne‑energie wordt eerst in huis gebruikt; het overschot gaat direct terug naar het elektriciteitsnet.<p>\r\n<b>Gevolg</b>\r\n<ul>\r\n  <li>Een groot deel van de opgewekte stroom wordt niet direct benut.</li>\r\n  <li>Teruglevering aan het net wordt steeds minder aantrekkelijk.</li>\r\n  <li>Je bent afhankelijk van terugleververgoedingen en -kosten.</li>\r\n</ul></p>', '2026-04-20 09:39:35', 1),
(71, 'overzicht6b', 'Zonne‑energie wordt eerst in huis gebruikt. Het overschot blijft lokaal in de buurt en wordt opgeslagen in een buurtbatterij of direct gedeeld met andere bewoners.<p>\r\n<b>Gevolg</b>\r\n<ul>\r\n  <li>Meer lokaal en gelijktijdig gebruik van opgewekte stroom.</li>\r\n  <li>Minder stroom terug naar het net.</li>\r\n  <li>Ook bewoners zonder zonnepanelen profiteren van lokaal opgewekte energie.</li>\r\n</ul></p>', '2026-04-20 09:39:25', 1),
(72, 'overzicht6c', 'Zonne‑energie wordt eerst in huis gebruikt en daarna opgeslagen in een eigen thuisbatterij voor later gebruik.<p>\r\n<b>Gevolg</b>\r\n<ul>\r\n  <li>Meer eigen verbruik dan bij niets doen.</li>\r\n  <li>Overschotten die niet in de batterij passen, gaan alsnog terug naar het net.</li>\r\n  <li>Het voordeel blijft volledig individueel.</li>\r\n</ul></p>', '2026-04-20 09:39:16', 1),
(73, 'overzicht7a', '<strong>Wat gebeurt er met overtollige zonne‑energie?</strong>\r\n<ul>\r\n  <li>Stroom die je niet direct gebruikt, gaat terug naar het elektriciteitsnet.</li>\r\n  <li>Je ontvangt een terugleververgoeding of betaalt terugleverkosten.</li>\r\n  <li>Het overschot verlaat de buurt en wordt niet lokaal benut.</li>\r\n</ul>', '2026-04-20 09:38:56', 1),
(74, 'overzicht7b', '<strong>Wat gebeurt er met overtollige zonne‑energie?</strong>\r\n<ul>\r\n  <li>Stroom die je niet direct gebruikt, blijft in de buurt.</li>\r\n  <li>Het overschot wordt opgeslagen in een buurtbatterij of direct gedeeld met andere bewoners.</li>\r\n  <li>De energie wordt later gebruikt als buurtstroom.</li>\r\n  <li>Minder teruglevering aan het net en lagere terugleverkosten.</li>\r\n</ul>', '2026-04-20 09:38:46', 1),
(75, 'overzicht7c', '<strong>Wat gebeurt er met overtollige zonne‑energie?</strong>\r\n<ul>\r\n  <li>Stroom die je niet direct gebruikt, wordt opgeslagen in je eigen thuisbatterij.</li>\r\n  <li>Is de batterij vol, dan gaat het resterende overschot alsnog terug naar het net.</li>\r\n  <li>Het gebruik en voordeel zijn volledig individueel.</li>\r\n</ul>', '2026-04-20 09:38:34', 1),
(76, 'overzicht8a', '<strong>Techniek</strong>\r\n<ul>\r\n  <li>Geen aanvullende techniek naast de bestaande meterkast.</li>\r\n  <li>Zonnepanelen leveren stroom terug aan het net.</li>\r\n</ul>\r\n\r\n<strong>Beheer</strong>\r\n<ul>\r\n  <li>Geen extra beheer of onderhoud.</li>\r\n  <li>Je bent volledig afhankelijk van energieleverancier en netbeheerder.</li>\r\n</ul>', '2026-04-20 09:38:17', 1),
(77, 'overzicht8b', '<strong>Techniek</strong>\r\n<ul>\r\n  <li>Buurtbatterij in de wijk voor opslag van lokale zonne‑energie.</li>\r\n  <li>Slimme meterkast of meetmodule in huis.</li>\r\n  <li>Software stuurt verdeling van buurtstroom automatisch aan.</li>\r\n</ul>\r\n\r\n<strong>Beheer</strong>\r\n<ul>\r\n  <li>Beheer en onderhoud zijn collectief georganiseerd.</li>\r\n  <li>Bewoners hoeven zelf niets technisch te bedienen.</li>\r\n  <li>Monitoring en ondersteuning gebeuren op afstand.</li>\r\n</ul>', '2026-04-20 09:38:10', 1),
(78, 'overzicht8c', '<strong>Techniek</strong>\r\n<ul>\r\n  <li>Een batterij in of bij de woning voor eigen opslag.</li>\r\n  <li>Aansturing via omvormer en bijbehorende software.</li>\r\n</ul>\r\n\r\n<strong>Beheer</strong>\r\n<ul>\r\n  <li>Onderhoud en beheer zijn de verantwoordelijkheid van de bewoner.</li>\r\n  <li>Vervanging en storingen zijn individueel geregeld.</li>\r\n</ul>', '2026-04-20 09:38:00', 1),
(79, 'overzicht9a', '<strong>Individueel</strong>\r\n<ul>\r\n  <li>Iedereen regelt zijn eigen energiecontract en kosten.</li>\r\n  <li>Voordelen en nadelen zijn volledig persoonlijk.</li>\r\n</ul>\r\n\r\n<strong>Collectief</strong>\r\n<ul>\r\n  <li>Er is geen samenwerking met de buurt.</li>\r\n  <li>Opgewekte energie wordt niet lokaal gedeeld.</li>\r\n</ul>', '2026-04-20 09:37:40', 1),
(80, 'overzicht9b', '<strong>Individueel</strong>\r\n<ul>\r\n  <li>Je gebruikt stroom zoals je gewend bent.</li>\r\n  <li>Je profiteert persoonlijk van lagere kosten en stabielere tarieven.</li>\r\n</ul>\r\n\r\n<strong>Collectief</strong>\r\n<ul>\r\n  <li>Opgewekte energie wordt samen met de buurt benut.</li>\r\n  <li>Bewoners met en zonder zonnepanelen doen mee.</li>\r\n  <li>Kosten, voordelen en techniek zijn collectief georganiseerd.</li>\r\n  <li>De buurt wordt minder afhankelijk van het elektriciteitsnet.</li>\r\n</ul>', '2026-04-20 09:37:31', 1),
(81, 'overzicht9c', '<strong>Individueel</strong>\r\n<ul>\r\n  <li>De batterij en het voordeel zijn volledig voor eigen gebruik.</li>\r\n  <li>Je regelt investering, onderhoud en vervanging zelf.</li>\r\n</ul>\r\n\r\n<strong>Collectief</strong>\r\n<ul>\r\n  <li>Er is geen directe samenwerking met de buurt.</li>\r\n  <li>Andere bewoners profiteren niet mee van het systeem.</li>\r\n</ul>', '2026-04-20 09:37:23', 1),
(82, 'overzicht10a', '<strong>Duurzaamheid</strong>\r\n<ul>\r\n  <li>Een groot deel van de zonne‑energie verlaat de buurt via het elektriciteitsnet.</li>\r\n  <li>Lokaal opgewekte stroom wordt niet optimaal benut.</li>\r\n</ul>\r\n\r\n<strong>Netbelasting</strong>\r\n<ul>\r\n  <li>Teruglevering zorgt voor extra druk op het elektriciteitsnet.</li>\r\n  <li>Bij piekproductie kan dit leiden tot hogere kosten en beperkingen.</li>\r\n</ul>', '2026-04-20 09:37:04', 1),
(83, 'overzicht10b', '<strong>Duurzaamheid</strong>\r\n<ul>\r\n  <li>Lokaal opgewekte zonne‑energie blijft in de buurt.</li>\r\n  <li>Meer directe en nuttige inzet van duurzame stroom.</li>\r\n  <li>Ook bewoners zonder zonnepanelen profiteren van groene energie.</li>\r\n</ul>\r\n\r\n<strong>Netbelasting</strong>\r\n<ul>\r\n  <li>Minder teruglevering aan het elektriciteitsnet.</li>\r\n  <li>Pieken worden afgevlakt door lokale opslag en verdeling.</li>\r\n  <li>Het net wordt ontlast, wat bijdraagt aan toekomstbestendigheid.</li>\r\n</ul>', '2026-04-20 09:36:54', 1),
(84, 'overzicht10c', '<strong>Duurzaamheid</strong>\r\n<ul>\r\n  <li>Meer eigen gebruik van zonne‑energie dan bij niets doen.</li>\r\n  <li>Overschotten die niet opgeslagen kunnen worden, gaan alsnog terug naar het net.</li>\r\n</ul>\r\n\r\n<strong>Netbelasting</strong>\r\n<ul>\r\n  <li>De belasting van het net neemt beperkt af.</li>\r\n  <li>Effect blijft afhankelijk van de grootte van de batterij.</li>\r\n</ul>', '2026-04-20 09:36:44', 1),
(85, 'overzicht11a', '<strong>Past vooral bij</strong>\r\n<ul>\r\n  <li>Huishoudens die geen veranderingen willen of kunnen doorvoeren.</li>\r\n  <li>Bewoners die geen investering willen doen.</li>\r\n</ul>\r\n\r\n<strong>Minder geschikt als</strong>\r\n<ul>\r\n  <li>Je grip wilt krijgen op je energiekosten na 2027.</li>\r\n  <li>Je lokaal opgewekte zonne‑energie beter wilt benutten.</li>\r\n</ul>', '2026-04-20 09:36:18', 1),
(86, 'overzicht11b', '<strong>Past vooral bij</strong>\r\n<ul>\r\n  <li>Bewoners die samen met de buurt energie willen delen.</li>\r\n  <li>Huishoudens met én zonder zonnepanelen.</li>\r\n  <li>Mensen die willen profiteren zonder zelf alles te moeten regelen.</li>\r\n</ul>\r\n\r\n<strong>Minder geschikt als</strong>\r\n<ul>\r\n  <li>Je alleen individueel voordeel zoekt zonder samenwerking.</li>\r\n  <li>Je geen collectieve oplossing wilt.</li>\r\n</ul>', '2026-04-20 09:36:09', 1),
(87, 'overzicht11c', '<strong>Past vooral bij</strong>\r\n<ul>\r\n  <li>Huishoudens die individueel hun zonne‑energie willen opslaan.</li>\r\n  <li>Bewoners die zelf willen investeren en beheren.</li>\r\n</ul>\r\n\r\n<strong>Minder geschikt als</strong>\r\n<ul>\r\n  <li>Je geen grote eenmalige investering wilt doen.</li>\r\n  <li>Je zoekt naar een oplossing die ook de buurt helpt.</li>\r\n</ul>', '2026-04-20 09:35:59', 1),
(88, 'overzicht12a', '<strong>Beëindigen</strong>\r\n<ul>\r\n  <li>Je kunt je energiecontract opzeggen volgens de voorwaarden van je energieleverancier.</li>\r\n  <li>Er zijn geen aanvullende afspraken of verplichtingen.</li>\r\n</ul>\r\n\r\n<strong>Gevolg</strong>\r\n<ul>\r\n  <li>Bij een nieuw contract blijven de effecten van stoppen met salderen bestaan.</li>\r\n</ul>', '2026-04-20 09:35:34', 1),
(89, 'overzicht12b', '<strong>Beëindigen</strong>\r\n<ul>\r\n  <li>Deelname aan energie delen is vrijwillig.</li>\r\n  <li>Opzegging verloopt via het energieschap.</li>\r\n  <li>De collectieve installatie blijft in beheer van de buurt.</li>\r\n</ul>\r\n\r\n<strong>Gevolg</strong>\r\n<ul>\r\n  <li>Na beëindiging maak je geen gebruik meer van buurtstroom.</li>\r\n  <li>Je valt terug op je reguliere energiecontract.</li>\r\n</ul>', '2026-04-20 09:35:24', 1),
(90, 'overzicht12c', '<strong>Beëindigen</strong>\r\n<ul>\r\n  <li>Er is geen contract dat je kunt beëindigen.</li>\r\n  <li>De batterij blijft eigendom van de bewoner.</li>\r\n</ul>\r\n\r\n<strong>Gevolg</strong>\r\n<ul>\r\n  <li>Bij stoppen met gebruik blijft de investering bestaan.</li>\r\n  <li>Eventuele verkoop of verwijdering regel je zelf.</li>\r\n</ul>', '2026-04-20 09:35:14', 1),
(91, 'overzicht13a', '<strong>Opzegtermijn</strong>\r\n<ul>\r\n  <li>De opzegtermijn volgt uit de voorwaarden van je energiecontract.</li>\r\n  <li>Deze verschilt per energieleverancier en type contract.</li>\r\n</ul>', '2026-04-20 09:34:46', 1),
(92, 'overzicht13b', '<strong>Opzegtermijn</strong>\r\n<ul>\r\n  <li>Deelname aan energie delen kent een opzegtermijn via het energieschap.</li>\r\n  <li>De opzegtermijn is vastgelegd in de lidmaatschapsvoorwaarden.</li>\r\n  <li>De collectieve installatie blijft bestaan, ook als je stopt.</li>\r\n</ul>', '2026-04-20 09:34:36', 1),
(93, 'overzicht13c', '<strong>Opzegtermijn</strong>\r\n<ul>\r\n  <li>Er is geen opzegtermijn van toepassing.</li>\r\n  <li>De batterij is eigendom van de bewoner.</li>\r\n</ul>', '2026-04-20 09:34:26', 1),
(99, 'overzicht1a', 'Niets doen', '2026-04-20 09:42:51', 1),
(100, 'overzicht1b', 'Buurtbatterij', '2026-04-20 09:42:43', 1),
(101, 'overzicht1c', 'Thuisbatterij', '2026-04-20 09:42:36', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `active` (`active`);

--
-- Indexes for table `household_data`
--
ALTER TABLE `household_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_content`
--
ALTER TABLE `page_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_key` (`section_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `household_data`
--
ALTER TABLE `household_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `page_content`
--
ALTER TABLE `page_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 08. Mai 2020 um 12:54
-- Server Version: 5.6.43
-- PHP-Version: 5.6.40-0+deb8u2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `vendo`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Countries`
--

CREATE TABLE IF NOT EXISTS `Countries` (
  `id` int(11) NOT NULL DEFAULT '0',
  `iso` varchar(16) DEFAULT NULL,
  `phonecode` varchar(16) DEFAULT NULL,
  `nicename` varchar(1024) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `Countries`
--

INSERT INTO `Countries` (`id`, `iso`, `phonecode`, `nicename`) VALUES
(1, 'AF', '93', 'Afghanistan'),
(2, 'AL', '355', 'Albania'),
(3, 'DZ', '213', 'Algeria'),
(4, 'AS', '1684', 'American Samoa'),
(5, 'AD', '376', 'Andorra'),
(6, 'AO', '244', 'Angola'),
(7, 'AI', '1264', 'Anguilla'),
(8, 'AQ', '0', 'Antarctica'),
(9, 'AG', '1268', 'Antigua and Barbuda'),
(10, 'AR', '54', 'Argentina'),
(11, 'AM', '374', 'Armenia'),
(12, 'AW', '297', 'Aruba'),
(13, 'AU', '61', 'Australia'),
(14, 'AT', '43', 'Austria'),
(15, 'AZ', '994', 'Azerbaijan'),
(16, 'BS', '1242', 'Bahamas'),
(17, 'BH', '973', 'Bahrain'),
(18, 'BD', '880', 'Bangladesh'),
(19, 'BB', '1246', 'Barbados'),
(20, 'BY', '375', 'Belarus'),
(21, 'BE', '32', 'Belgium'),
(22, 'BZ', '501', 'Belize'),
(23, 'BJ', '229', 'Benin'),
(24, 'BM', '1441', 'Bermuda'),
(25, 'BT', '975', 'Bhutan'),
(26, 'BO', '591', 'Bolivia'),
(27, 'BA', '387', 'Bosnia and Herzegovina'),
(28, 'BW', '267', 'Botswana'),
(29, 'BV', '0', 'Bouvet Island'),
(30, 'BR', '55', 'Brazil'),
(31, 'IO', '246', 'British Indian Ocean Territory'),
(32, 'BN', '673', 'Brunei Darussalam'),
(33, 'BG', '359', 'Bulgaria'),
(34, 'BF', '226', 'Burkina Faso'),
(35, 'BI', '257', 'Burundi'),
(36, 'KH', '855', 'Cambodia'),
(37, 'CM', '237', 'Cameroon'),
(38, 'CA', '1', 'Canada'),
(39, 'CV', '238', 'Cape Verde'),
(40, 'KY', '1345', 'Cayman Islands'),
(41, 'CF', '236', 'Central African Republic'),
(42, 'TD', '235', 'Chad'),
(43, 'CL', '56', 'Chile'),
(44, 'CN', '86', 'China'),
(45, 'CX', '61', 'Christmas Island'),
(46, 'CC', '672', 'Cocos (Keeling) Islands'),
(47, 'CO', '57', 'Colombia'),
(48, 'KM', '269', 'Comoros'),
(49, 'CG', '242', 'Congo'),
(50, 'CD', '242', 'Congo, the Democratic Republic of the'),
(51, 'CK', '682', 'Cook Islands'),
(52, 'CR', '506', 'Costa Rica'),
(53, 'CI', '225', 'Cote D''Ivoire'),
(54, 'HR', '385', 'Croatia'),
(55, 'CU', '53', 'Cuba'),
(56, 'CY', '357', 'Cyprus'),
(57, 'CZ', '420', 'Czech Republic'),
(58, 'DK', '45', 'Denmark'),
(59, 'DJ', '253', 'Djibouti'),
(60, 'DM', '1767', 'Dominica'),
(61, 'DO', '1809', 'Dominican Republic'),
(62, 'EC', '593', 'Ecuador'),
(63, 'EG', '20', 'Egypt'),
(64, 'SV', '503', 'El Salvador'),
(65, 'GQ', '240', 'Equatorial Guinea'),
(66, 'ER', '291', 'Eritrea'),
(67, 'EE', '372', 'Estonia'),
(68, 'ET', '251', 'Ethiopia'),
(69, 'FK', '500', 'Falkland Islands (Malvinas)'),
(70, 'FO', '298', 'Faroe Islands'),
(71, 'FJ', '679', 'Fiji'),
(72, 'FI', '358', 'Finland'),
(73, 'FR', '33', 'France'),
(74, 'GF', '594', 'French Guiana'),
(75, 'PF', '689', 'French Polynesia'),
(76, 'TF', '0', 'French Southern Territories'),
(77, 'GA', '241', 'Gabon'),
(78, 'GM', '220', 'Gambia'),
(79, 'GE', '995', 'Georgia'),
(80, 'DE', '49', 'Germany'),
(81, 'GH', '233', 'Ghana'),
(82, 'GI', '350', 'Gibraltar'),
(83, 'GR', '30', 'Greece'),
(84, 'GL', '299', 'Greenland'),
(85, 'GD', '1473', 'Grenada'),
(86, 'GP', '590', 'Guadeloupe'),
(87, 'GU', '1671', 'Guam'),
(88, 'GT', '502', 'Guatemala'),
(89, 'GN', '224', 'Guinea'),
(90, 'GW', '245', 'Guinea-Bissau'),
(91, 'GY', '592', 'Guyana'),
(92, 'HT', '509', 'Haiti'),
(93, 'HM', '0', 'Heard Island and Mcdonald Islands'),
(94, 'VA', '39', 'Holy See (Vatican City State)'),
(95, 'HN', '504', 'Honduras'),
(96, 'HK', '852', 'Hong Kong'),
(97, 'HU', '36', 'Hungary'),
(98, 'IS', '354', 'Iceland'),
(99, 'IN', '91', 'India'),
(100, 'ID', '62', 'Indonesia'),
(101, 'IR', '98', 'Iran, Islamic Republic of'),
(102, 'IQ', '964', 'Iraq'),
(103, 'IE', '353', 'Ireland'),
(104, 'IL', '972', 'Israel'),
(105, 'IT', '39', 'Italy'),
(106, 'JM', '1876', 'Jamaica'),
(107, 'JP', '81', 'Japan'),
(108, 'JO', '962', 'Jordan'),
(109, 'KZ', '7', 'Kazakhstan'),
(110, 'KE', '254', 'Kenya'),
(111, 'KI', '686', 'Kiribati'),
(112, 'KP', '850', 'Korea, Democratic People''s Republic of'),
(113, 'KR', '82', 'Korea, Republic of'),
(114, 'KW', '965', 'Kuwait'),
(115, 'KG', '996', 'Kyrgyzstan'),
(116, 'LA', '856', 'Lao People''s Democratic Republic'),
(117, 'LV', '371', 'Latvia'),
(118, 'LB', '961', 'Lebanon'),
(119, 'LS', '266', 'Lesotho'),
(120, 'LR', '231', 'Liberia'),
(121, 'LY', '218', 'Libyan Arab Jamahiriya'),
(122, 'LI', '423', 'Liechtenstein'),
(123, 'LT', '370', 'Lithuania'),
(124, 'LU', '352', 'Luxembourg'),
(125, 'MO', '853', 'Macao'),
(126, 'MK', '389', 'Macedonia, the Former Yugoslav Republic of'),
(127, 'MG', '261', 'Madagascar'),
(128, 'MW', '265', 'Malawi'),
(129, 'MY', '60', 'Malaysia'),
(130, 'MV', '960', 'Maldives'),
(131, 'ML', '223', 'Mali'),
(132, 'MT', '356', 'Malta'),
(133, 'MH', '692', 'Marshall Islands'),
(134, 'MQ', '596', 'Martinique'),
(135, 'MR', '222', 'Mauritania'),
(136, 'MU', '230', 'Mauritius'),
(137, 'YT', '269', 'Mayotte'),
(138, 'MX', '52', 'Mexico'),
(139, 'FM', '691', 'Micronesia, Federated States of'),
(140, 'MD', '373', 'Moldova, Republic of'),
(141, 'MC', '377', 'Monaco'),
(142, 'MN', '976', 'Mongolia'),
(143, 'MS', '1664', 'Montserrat'),
(144, 'MA', '212', 'Morocco'),
(145, 'MZ', '258', 'Mozambique'),
(146, 'MM', '95', 'Myanmar'),
(147, 'NA', '264', 'Namibia'),
(148, 'NR', '674', 'Nauru'),
(149, 'NP', '977', 'Nepal'),
(150, 'NL', '31', 'Netherlands'),
(151, 'AN', '599', 'Netherlands Antilles'),
(152, 'NC', '687', 'New Caledonia'),
(153, 'NZ', '64', 'New Zealand'),
(154, 'NI', '505', 'Nicaragua'),
(155, 'NE', '227', 'Niger'),
(156, 'NG', '234', 'Nigeria'),
(157, 'NU', '683', 'Niue'),
(158, 'NF', '672', 'Norfolk Island'),
(159, 'MP', '1670', 'Northern Mariana Islands'),
(160, 'NO', '47', 'Norway'),
(161, 'OM', '968', 'Oman'),
(162, 'PK', '92', 'Pakistan'),
(163, 'PW', '680', 'Palau'),
(164, 'PS', '970', 'Palestinian Territory, Occupied'),
(165, 'PA', '507', 'Panama'),
(166, 'PG', '675', 'Papua New Guinea'),
(167, 'PY', '595', 'Paraguay'),
(168, 'PE', '51', 'Peru'),
(169, 'PH', '63', 'Philippines'),
(170, 'PN', '0', 'Pitcairn'),
(171, 'PL', '48', 'Poland'),
(172, 'PT', '351', 'Portugal'),
(173, 'PR', '1787', 'Puerto Rico'),
(174, 'QA', '974', 'Qatar'),
(175, 'RE', '262', 'Reunion'),
(176, 'RO', '40', 'Romania'),
(177, 'RU', '7', 'Russian'),
(178, 'RW', '250', 'Rwanda'),
(179, 'SH', '290', 'Saint Helena'),
(180, 'KN', '1869', 'Saint Kitts and Nevis'),
(181, 'LC', '1758', 'Saint Lucia'),
(182, 'PM', '508', 'Saint Pierre and Miquelon'),
(183, 'VC', '1784', 'Saint Vincent and the Grenadines'),
(184, 'WS', '684', 'Samoa'),
(185, 'SM', '378', 'San Marino'),
(186, 'ST', '239', 'Sao Tome and Principe'),
(187, 'SA', '966', 'Saudi Arabia'),
(188, 'SN', '221', 'Senegal'),
(189, 'CS', '381', 'Serbia and Montenegro'),
(190, 'SC', '248', 'Seychelles'),
(191, 'SL', '232', 'Sierra Leone'),
(192, 'SG', '65', 'Singapore'),
(193, 'SK', '421', 'Slovakia'),
(194, 'SI', '386', 'Slovenia'),
(195, 'SB', '677', 'Solomon Islands'),
(196, 'SO', '252', 'Somalia'),
(197, 'ZA', '27', 'South Africa'),
(198, 'GS', '0', 'South Georgia and the South Sandwich Islands'),
(199, 'ES', '34', 'Spain'),
(200, 'LK', '94', 'Sri Lanka'),
(201, 'SD', '249', 'Sudan'),
(202, 'SR', '597', 'Suriname'),
(203, 'SJ', '47', 'Svalbard and Jan Mayen'),
(204, 'SZ', '268', 'Swaziland'),
(205, 'SE', '46', 'Sweden'),
(206, 'CH', '41', 'Switzerland'),
(207, 'SY', '963', 'Syrian Arab Republic'),
(208, 'TW', '886', 'Taiwan, Province of China'),
(209, 'TJ', '992', 'Tajikistan'),
(210, 'TZ', '255', 'Tanzania, United Republic of'),
(211, 'TH', '66', 'Thailand'),
(212, 'TL', '670', 'Timor-Leste'),
(213, 'TG', '228', 'Togo'),
(214, 'TK', '690', 'Tokelau'),
(215, 'TO', '676', 'Tonga'),
(216, 'TT', '1868', 'Trinidad and Tobago'),
(217, 'TN', '216', 'Tunisia'),
(218, 'TR', '90', 'Turkey'),
(219, 'TM', '7370', 'Turkmenistan'),
(220, 'TC', '1649', 'Turks and Caicos Islands'),
(221, 'TV', '688', 'Tuvalu'),
(222, 'UG', '256', 'Uganda'),
(223, 'UA', '380', 'Ukraine'),
(224, 'AE', '971', 'United Arab Emirates'),
(225, 'GB', '44', 'United Kingdom'),
(226, 'US', '1', 'United States'),
(227, 'UM', '1', 'United States Minor Outlying Islands'),
(228, 'UY', '598', 'Uruguay'),
(229, 'UZ', '998', 'Uzbekistan'),
(230, 'VU', '678', 'Vanuatu'),
(231, 'VE', '58', 'Venezuela'),
(232, 'VN', '84', 'Viet Nam'),
(233, 'VG', '1284', 'Virgin Islands, British'),
(234, 'VI', '1340', 'Virgin Islands, U.s.'),
(235, 'WF', '681', 'Wallis and Futuna'),
(236, 'EH', '212', 'Western Sahara'),
(237, 'YE', '967', 'Yemen'),
(238, 'ZM', '260', 'Zambia'),
(239, 'ZW', '263', 'Zimbabwe');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `Countries`
--
ALTER TABLE `Countries`
 ADD PRIMARY KEY (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

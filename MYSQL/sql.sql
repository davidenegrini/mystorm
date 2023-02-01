CREATE TABLE `places_auth` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `places_id` int(20) NOT NULL,
  `users_id` int(20) NOT NULL,
  `plevel` int(10) NOT NULL DEFAULT '1' COMMENT '1=visualizzatoreNONUSATA, 2=utilizzatore, 3=amministratore',
  `regdate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `places_list` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `identifier` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pname` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pdesc` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pwhere` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `regdate` datetime NOT NULL,
  `active` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `places_log` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `places_id` int(20) NOT NULL,
  `users_id` int(20) NOT NULL,
  `log` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `regdate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `things_list` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `places_id` int(20) NOT NULL,
  `identifier` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `tname` text COLLATE utf8mb4_unicode_520_ci,
  `tdesc` text COLLATE utf8mb4_unicode_520_ci,
  `timg` text COLLATE utf8mb4_unicode_520_ci,
  `expi` date DEFAULT NULL,
  `quant` float NOT NULL DEFAULT '0',
  `regdate` datetime NOT NULL,
  `active` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `things_log` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `things_id` int(20) NOT NULL,
  `users_id` int(20) NOT NULL,
  `log` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `regdate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `users_list` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `mail` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pass` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `name` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `regdate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

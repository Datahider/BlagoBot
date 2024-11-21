-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               11.3.2-MariaDB - mariadb.org binary distribution
-- Операционная система:         Win64
-- HeidiSQL Версия:              12.7.0.6850
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Дамп структуры для таблица test.blago_department
DROP TABLE IF EXISTS `blago_department`;
CREATE TABLE IF NOT EXISTS `blago_department` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NAME` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_log_report
DROP TABLE IF EXISTS `blago_log_report`;
CREATE TABLE IF NOT EXISTS `blago_log_report` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `report_class` varchar(1024) NOT NULL,
  `time_start` datetime NOT NULL,
  `processing_time` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=417 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_menu
DROP TABLE IF EXISTS `blago_menu`;
CREATE TABLE IF NOT EXISTS `blago_menu` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent` bigint(20) DEFAULT NULL,
  `sort` bigint(20) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `description` varchar(1024) DEFAULT NULL,
  `handler_class` varchar(256) DEFAULT NULL,
  `handler_param` varchar(256) DEFAULT NULL,
  `title` varchar(64) DEFAULT NULL,
  `type` enum('submenu','report') NOT NULL DEFAULT 'submenu',
  `subtype_id` bigint(20) DEFAULT NULL,
  `accessed_by` varchar(4) NOT NULL DEFAULT 'a',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_report
DROP TABLE IF EXISTS `blago_report`;
CREATE TABLE IF NOT EXISTS `blago_report` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sort` bigint(20) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `title` varchar(64) DEFAULT NULL,
  `handler_class` varchar(256) DEFAULT NULL,
  `handler_param` varchar(256) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `accessed_by` varchar(4) NOT NULL DEFAULT 'a',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_report_param
DROP TABLE IF EXISTS `blago_report_param`;
CREATE TABLE IF NOT EXISTS `blago_report_param` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `report` bigint(20) DEFAULT NULL,
  `sort` bigint(20) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `name` varchar(32) DEFAULT NULL,
  `title` varchar(64) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `is_mandatory` tinyint(1) NOT NULL,
  `is_multiple_choise` tinyint(1) NOT NULL,
  `choise_type` enum('buttons','aphabet') NOT NULL,
  `max_buttons` tinyint(4) NOT NULL,
  `value_set` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_report_param_value
DROP TABLE IF EXISTS `blago_report_param_value`;
CREATE TABLE IF NOT EXISTS `blago_report_param_value` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `value_set` varchar(32) DEFAULT NULL,
  `sort` bigint(20) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `title` varchar(64) DEFAULT NULL,
  `value` varchar(256) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_telle_bot_params
DROP TABLE IF EXISTS `blago_telle_bot_params`;
CREATE TABLE IF NOT EXISTS `blago_telle_bot_params` (
  `name` varchar(100) NOT NULL,
  `value` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_telle_chats
DROP TABLE IF EXISTS `blago_telle_chats`;
CREATE TABLE IF NOT EXISTS `blago_telle_chats` (
  `id` bigint(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `title` varchar(256) DEFAULT NULL,
  `username` varchar(256) DEFAULT NULL,
  `first_name` varchar(256) DEFAULT NULL,
  `last_name` varchar(256) DEFAULT NULL,
  `is_forum` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_telle_cron_entries
DROP TABLE IF EXISTS `blago_telle_cron_entries`;
CREATE TABLE IF NOT EXISTS `blago_telle_cron_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cron_expression` varchar(100) DEFAULT NULL,
  `next_start_time` datetime DEFAULT NULL,
  `start_in_background` tinyint(1) NOT NULL DEFAULT 0,
  `last_started` datetime DEFAULT NULL,
  `last_result` varchar(10) DEFAULT NULL,
  `last_error_description` varchar(500) DEFAULT NULL,
  `job_class` varchar(300) DEFAULT NULL,
  `job_args` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_telle_pending_jobs
DROP TABLE IF EXISTS `blago_telle_pending_jobs`;
CREATE TABLE IF NOT EXISTS `blago_telle_pending_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `start_time` datetime DEFAULT NULL,
  `start_in_background` tinyint(1) NOT NULL DEFAULT 0,
  `was_started` datetime DEFAULT NULL,
  `result` varchar(10) DEFAULT NULL,
  `error_description` varchar(500) DEFAULT NULL,
  `job_class` varchar(300) DEFAULT NULL,
  `job_args` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_telle_pending_updates
DROP TABLE IF EXISTS `blago_telle_pending_updates`;
CREATE TABLE IF NOT EXISTS `blago_telle_pending_updates` (
  `id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `locked_till` int(10) unsigned NOT NULL,
  `worker` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_telle_sessions
DROP TABLE IF EXISTS `blago_telle_sessions`;
CREATE TABLE IF NOT EXISTS `blago_telle_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` bigint(20) NOT NULL,
  `chat` bigint(20) NOT NULL,
  `message_thread_id` bigint(20) DEFAULT NULL,
  `mode` varchar(128) DEFAULT NULL,
  `command` varchar(128) DEFAULT NULL,
  `state` varchar(128) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `priority_handler` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_telle_users
DROP TABLE IF EXISTS `blago_telle_users`;
CREATE TABLE IF NOT EXISTS `blago_telle_users` (
  `id` bigint(20) unsigned NOT NULL,
  `is_bot` tinyint(3) unsigned NOT NULL,
  `first_name` varchar(256) NOT NULL,
  `last_name` varchar(256) DEFAULT NULL,
  `username` varchar(256) DEFAULT NULL,
  `language_code` varchar(10) DEFAULT NULL,
  `is_premium` tinyint(1) DEFAULT NULL,
  `added_to_attachment_menu` tinyint(1) DEFAULT NULL,
  `can_join_groups` tinyint(1) DEFAULT NULL,
  `can_read_all_group_messages` tinyint(1) DEFAULT NULL,
  `support_inline_queries` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_user
DROP TABLE IF EXISTS `blago_user`;
CREATE TABLE IF NOT EXISTS `blago_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tg_user` bigint(20) NOT NULL,
  `surname` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `fathers_name` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `phone` varchar(14) DEFAULT NULL,
  `phone2` varchar(14) DEFAULT NULL,
  `notes` varchar(1024) DEFAULT NULL,
  `access_level` enum('admin','operator','user','restricted','unknown') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TG_USER` (`tg_user`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_user_dept_binding
DROP TABLE IF EXISTS `blago_user_dept_binding`;
CREATE TABLE IF NOT EXISTS `blago_user_dept_binding` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `dept_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `USER_DEPT` (`user_id`,`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_activity
DROP TABLE IF EXISTS `blago_x_activity`;
CREATE TABLE IF NOT EXISTS `blago_x_activity` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `gasu_code` varchar(10) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `GASU_CODE` (`gasu_code`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_category
DROP TABLE IF EXISTS `blago_x_category`;
CREATE TABLE IF NOT EXISTS `blago_x_category` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NAME` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_contract
DROP TABLE IF EXISTS `blago_x_contract`;
CREATE TABLE IF NOT EXISTS `blago_x_contract` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `x_contragent_id` bigint(20) DEFAULT NULL,
  `x_object_id` bigint(20) NOT NULL,
  `status` enum('ГП','РГ','Закупки','Контракт','Прочее') NOT NULL,
  `status2` varchar(32) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `has_pir` tinyint(1) NOT NULL DEFAULT 0,
  `has_smr` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=481 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_contract2
DROP TABLE IF EXISTS `blago_x_contract2`;
CREATE TABLE IF NOT EXISTS `blago_x_contract2` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `x_contragent_id` bigint(20) DEFAULT NULL,
  `x_object_id` bigint(20) NOT NULL,
  `status` enum('ГП','РГ','Закупки','Контракт','Прочее') NOT NULL,
  `status2` varchar(32) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `has_pir` tinyint(1) NOT NULL DEFAULT 0,
  `has_smr` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=475 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_contract_data
DROP TABLE IF EXISTS `blago_x_contract_data`;
CREATE TABLE IF NOT EXISTS `blago_x_contract_data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `x_contract_id` bigint(20) NOT NULL,
  `type` enum('РГ ФБ','РГ БМ','РГ БМО','РГ ОМСУ','РГ ОМСУ2','Нмцк ФБ','Нмцк БМ','Нмцк БМО','Нмцк ОМСУ','Нмцк ОМСУ2','Контракт ФБ','Контракт БМ','Контракт БМО','Контракт ОМСУ','Контракт ОМСУ2','Заявка ФБ','Заявка БМ','Заявка БМО','Заявка ОМСУ','Заявка ОМСУ2','Оплата ФБ','Оплата БМ','Оплата БМО','Оплата ОМСУ','Оплата ОМСУ2','Критерий','Снятие') NOT NULL,
  `value` decimal(10,0) DEFAULT NULL,
  `note` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `YOT` (`year`,`x_contract_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=2390 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_contract_data2
DROP TABLE IF EXISTS `blago_x_contract_data2`;
CREATE TABLE IF NOT EXISTS `blago_x_contract_data2` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `x_contract_id` bigint(20) NOT NULL,
  `type` enum('РГ ФБ','РГ БМ','РГ БМО','РГ ОМСУ','РГ ОМСУ2','Нмцк ФБ','Нмцк БМ','Нмцк БМО','Нмцк ОМСУ','Нмцк ОМСУ2','Контракт ФБ','Контракт БМ','Контракт БМО','Контракт ОМСУ','Контракт ОМСУ2','Заявка ФБ','Заявка БМ','Заявка БМО','Заявка ОМСУ','Заявка ОМСУ2','Оплата ФБ','Оплата БМ','Оплата БМО','Оплата ОМСУ','Оплата ОМСУ2','Критерий','Снятие') NOT NULL,
  `value` decimal(10,0) DEFAULT NULL,
  `note` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `YOT` (`year`,`x_contract_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=2288 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_contragent
DROP TABLE IF EXISTS `blago_x_contragent`;
CREATE TABLE IF NOT EXISTS `blago_x_contragent` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `inn` varchar(12) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `INN` (`inn`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_object
DROP TABLE IF EXISTS `blago_x_object`;
CREATE TABLE IF NOT EXISTS `blago_x_object` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uin` varchar(25) NOT NULL,
  `omsu_id` bigint(20) NOT NULL,
  `full_name` varchar(2048) NOT NULL,
  `short_name` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `x_responsible_id` bigint(20) DEFAULT NULL,
  `x_category_id` bigint(20) NOT NULL,
  `category2_name` varchar(64) NOT NULL DEFAULT '',
  `x_activity_id` bigint(20) NOT NULL,
  `gasu_date` datetime DEFAULT NULL,
  `ready_percent` decimal(10,0) DEFAULT NULL,
  `object_char` varchar(64) DEFAULT NULL,
  `type` varchar(64) DEFAULT NULL,
  `period` varchar(64) DEFAULT NULL,
  `open_date_planned` datetime DEFAULT NULL,
  `open_date_fact` datetime DEFAULT NULL,
  `purchase_level` enum('1','2') DEFAULT NULL,
  `moge_in_plan` datetime DEFAULT NULL,
  `moge_out_plan` datetime DEFAULT NULL,
  `moge_in_fact` datetime DEFAULT NULL,
  `moge_out_fact` datetime DEFAULT NULL,
  `rgmin_in_plan` datetime DEFAULT NULL,
  `rgmin_in_fact` datetime DEFAULT NULL,
  `psmr_plan` datetime DEFAULT NULL,
  `psmr_fact` datetime DEFAULT NULL,
  `ksmr_plan` datetime DEFAULT NULL,
  `ksmr_fact` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=306 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_object2
DROP TABLE IF EXISTS `blago_x_object2`;
CREATE TABLE IF NOT EXISTS `blago_x_object2` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uin` varchar(25) NOT NULL,
  `omsu_id` bigint(20) NOT NULL,
  `full_name` varchar(2048) NOT NULL,
  `short_name` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `x_responsible_id` bigint(20) DEFAULT NULL,
  `x_category_id` bigint(20) NOT NULL,
  `category2_name` varchar(64) NOT NULL DEFAULT '',
  `x_activity_id` bigint(20) NOT NULL,
  `gasu_date` datetime DEFAULT NULL,
  `ready_percent` decimal(10,0) DEFAULT NULL,
  `object_char` varchar(64) DEFAULT NULL,
  `type` varchar(64) DEFAULT NULL,
  `period` varchar(64) DEFAULT NULL,
  `open_date_planned` datetime DEFAULT NULL,
  `open_date_fact` datetime DEFAULT NULL,
  `purchase_level` enum('1','2') DEFAULT NULL,
  `moge_in_plan` datetime DEFAULT NULL,
  `moge_out_plan` datetime DEFAULT NULL,
  `moge_in_fact` datetime DEFAULT NULL,
  `moge_out_fact` datetime DEFAULT NULL,
  `rgmin_in_plan` datetime DEFAULT NULL,
  `rgmin_in_fact` datetime DEFAULT NULL,
  `psmr_plan` datetime DEFAULT NULL,
  `psmr_fact` datetime DEFAULT NULL,
  `ksmr_plan` datetime DEFAULT NULL,
  `ksmr_fact` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=306 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_omsu
DROP TABLE IF EXISTS `blago_x_omsu`;
CREATE TABLE IF NOT EXISTS `blago_x_omsu` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `head_id` bigint(20) DEFAULT NULL,
  `vicehead_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_prev
DROP TABLE IF EXISTS `blago_x_prev`;
CREATE TABLE IF NOT EXISTS `blago_x_prev` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `omsu_name` varchar(64) NOT NULL,
  `object_name` varchar(256) NOT NULL,
  `category2_name` varchar(64) NOT NULL,
  `object_count` int(11) NOT NULL,
  `payment_total` decimal(10,0) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7184 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_prev2
DROP TABLE IF EXISTS `blago_x_prev2`;
CREATE TABLE IF NOT EXISTS `blago_x_prev2` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `omsu_name` varchar(64) NOT NULL,
  `object_name` varchar(256) NOT NULL,
  `category2_name` varchar(64) NOT NULL,
  `object_count` int(11) NOT NULL,
  `payment_total` decimal(10,0) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7184 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_responsible
DROP TABLE IF EXISTS `blago_x_responsible`;
CREATE TABLE IF NOT EXISTS `blago_x_responsible` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `surname` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `fathers_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_year_data
DROP TABLE IF EXISTS `blago_x_year_data`;
CREATE TABLE IF NOT EXISTS `blago_x_year_data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `x_object_id` bigint(20) NOT NULL,
  `type` enum('СМР','ПИР','Лимит ФБ','Лимит БМ','Лимит БМО','Лимит ОМСУ') NOT NULL,
  `value` decimal(10,0) DEFAULT NULL,
  `note` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `YOT` (`year`,`x_object_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1189 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

-- Дамп структуры для таблица test.blago_x_year_data2
DROP TABLE IF EXISTS `blago_x_year_data2`;
CREATE TABLE IF NOT EXISTS `blago_x_year_data2` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `x_object_id` bigint(20) NOT NULL,
  `type` enum('СМР','ПИР','Лимит ФБ','Лимит БМ','Лимит БМО','Лимит ОМСУ') NOT NULL,
  `value` decimal(10,0) DEFAULT NULL,
  `note` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `YOT` (`year`,`x_object_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1186 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Экспортируемые данные не выделены.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

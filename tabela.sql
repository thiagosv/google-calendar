-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.1.29-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win32
-- HeidiSQL Versão:              9.4.0.5135
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Copiando estrutura para tabela google-calendar.appointment
CREATE TABLE IF NOT EXISTS `appointment` (
  `appointment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `appointment_title` varchar(255) DEFAULT NULL,
  `appointment_description` text,
  `appointment_location` varchar(255) DEFAULT NULL,
  `appointment_event_id` varchar(255) NOT NULL,
  `appointment_start` timestamp NULL DEFAULT NULL,
  `appointment_end` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`appointment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela google-calendar.appointment: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `appointment` DISABLE KEYS */;
/*!40000 ALTER TABLE `appointment` ENABLE KEYS */;

-- Copiando estrutura para tabela google-calendar.attendees
CREATE TABLE IF NOT EXISTS `attendees` (
  `attendees_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attendees_appointment_id` int(11) unsigned DEFAULT NULL,
  `attendees_name` varchar(255) DEFAULT NULL,
  `attendees_email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`attendees_id`),
  KEY `FK_attendees_appointment` (`attendees_appointment_id`),
  CONSTRAINT `FK_attendees_appointment` FOREIGN KEY (`attendees_appointment_id`) REFERENCES `appointment` (`appointment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Copiando dados para a tabela google-calendar.attendees: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `attendees` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendees` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

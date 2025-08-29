-- Create the database if it doesn't exist and set it as the current database
CREATE DATABASE IF NOT EXISTS `foila_group` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `foila_group`;

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `division` enum('Group','Ipswich','London','Teesside') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `customers`
--
CREATE TABLE `customers` (
  `customer_code` varchar(6) NOT NULL,
  `customer_name` varchar(50) NOT NULL,
  `customer_address_1` varchar(50) NOT NULL,
  `customer_address_2` varchar(50) DEFAULT NULL,
  `customer_address_3` varchar(50) DEFAULT NULL,
  `customer_address_4` varchar(50) DEFAULT NULL,
  `customer_postcode` varchar(15) DEFAULT NULL,
  `customer_division` enum('Group','Ipswich','London','Teesside') NOT NULL,
  `customer_contact_name` varchar(50) DEFAULT NULL,
  `customer_telephone` varchar(50) DEFAULT NULL,
  `customer_mobile` varchar(50) DEFAULT NULL,
  `customer_email` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`customer_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `jobs`
--
CREATE TABLE `jobs` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(6) NOT NULL,
  `order_number` varchar(50) DEFAULT NULL,
  `second_reference` varchar(50) DEFAULT NULL,
  `third_reference` varchar(50) DEFAULT NULL,
  `collection_address_1` varchar(50) NOT NULL,
  `collection_address_2` varchar(50) DEFAULT NULL,
  `collection_address_3` varchar(50) DEFAULT NULL,
  `collection_address_4` varchar(50) DEFAULT NULL,
  `collection_postcode` varchar(15) DEFAULT NULL,
  `collection_date` date NOT NULL DEFAULT current_timestamp(),
  `collection_time_type` enum('Fixed','None','Booked In','Time Slot','AM','PM') NOT NULL DEFAULT 'Fixed',
  `collection_time_1` time NOT NULL DEFAULT '00:00:00',
  `collection_time_2` time DEFAULT NULL,
  `delivery_address_1` varchar(50) NOT NULL,
  `delivery_address_2` varchar(50) DEFAULT NULL,
  `delivery_address_3` varchar(50) DEFAULT NULL,
  `delivery_address_4` varchar(50) DEFAULT NULL,
  `delivery_postcode` varchar(15) DEFAULT NULL,
  `delivery_date` date NOT NULL DEFAULT current_timestamp(),
  `delivery_time_type` enum('Fixed','None','Booked In','Time Slot','AM','PM') NOT NULL DEFAULT 'Fixed',
  `delivery_time_1` time NOT NULL DEFAULT '00:00:00',
  `delivery_time_2` time DEFAULT NULL,
  `goods_description` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Booked',
  `quantity` decimal(10,3) DEFAULT 0.000,
  `weight` decimal(10,3) DEFAULT 0.000,
  `volume` decimal(10,3) DEFAULT 0.000,
  `job_value` decimal(10,3) DEFAULT 0.000,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_code` (`customer_code`),
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`customer_code`) REFERENCES `customers` (`customer_code`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User out of hours login attempts

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `attempt_time` datetime NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
-- The password for the user 'FionaSuleman' is: FionaS$10
--
INSERT INTO `users` (`username`, `password_hash`, `division`) VALUES
('FionaSuleman', '$2y$10$wTfH.N65vSgTzVn7.kH4gO.B5g3jT9X/L.cO1sF2aE7hG4kI8d.sO', 'Group');
COMMIT;
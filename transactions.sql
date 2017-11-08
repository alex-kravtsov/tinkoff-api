DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
    `OrderId` VARCHAR(50) NOT NULL,
    `Amount` VARCHAR(20) NOT NULL,
    `CustomerKey` VARCHAR(36) NULL,
    `Description` VARCHAR(255) NULL,
    `Status` VARCHAR(30) NULL,
    `PaymentId` VARCHAR(30) NULL,
    `PaymentURL` VARCHAR(255) NULL,
    `RebillId` VARCHAR(50) NULL COMMENT 'Recurrent payment ID.',
    `CardId` VARCHAR(50) NULL,
    `Pan` VARCHAR(255) NULL COMMENT 'Masked card number.',
    `ExpDate` VARCHAR(255) NULL COMMENT 'Card expiration date.',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `initialized_at` TIMESTAMP NULL,
    `completed_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;

DROP TABLE IF EXISTS `test_orders`;
CREATE TABLE `test_orders` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NULL,
    `price` DECIMAL(19,2) NOT NULL,
    `description` VARCHAR(255),
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `payed_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;

DROP TABLE IF EXISTS `test_customers`;
CREATE TABLE `test_customers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;

INSERT INTO `test_customers` (`email`) VALUES ('mail@example.com');

CREATE TABLE IF NOT EXISTS `mc_stripe_config` (
    `id_stripe` SMALLINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
    `public_key` VARCHAR(150) NOT NULL,
    `secret_key` VARCHAR(150) NOT NULL,
    `webhook_secret` VARCHAR(150) NOT NULL,
    PRIMARY KEY (`id_stripe`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_stripe_history` (
    `id_stripe_h` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_key_cart` VARCHAR(64) NULL,
    `order_ref` VARCHAR(64) NOT NULL,
    `stripe_session_id` VARCHAR(150) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('pending','paid','canceled','failed') DEFAULT 'pending',
    `date_register` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_stripe_h`),
    KEY `idx_order` (`order_ref`),
    KEY `idx_stripe_sess` (`stripe_session_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
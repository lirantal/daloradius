-- UNIT-040: durable IPN deduplication. No callback bodies or credentials.
-- Apply before deploying the migrated Chilli PayPal callbacks.
CREATE TABLE IF NOT EXISTS `chilli_paypal_events` (
  `event_key` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `payment_key` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `subscription_key` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `event_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `order_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `event_type` VARCHAR(40) NOT NULL,
  `event_status` VARCHAR(32) NOT NULL,
  `event_date` DATETIME NOT NULL,
  `processed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_key`),
  UNIQUE KEY `completed_payment` (`payment_key`),
  KEY `order_events` (`order_hash`, `event_date`),
  KEY `subscription_events` (`subscription_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

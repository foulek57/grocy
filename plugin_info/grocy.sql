CREATE TABLE `grocy_extend` (
  `product_id` varchar(255) NOT NULL,
  `barcode` varchar(255) NULL,
  `eqlogic_id` int(11) NOT NULL,
  UNIQUE KEY `grocy_key` (`product_id`,`barcode`,`eqlogic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
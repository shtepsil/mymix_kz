CREATE TABLE `our_stores`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `delivery_price_id` int(11) NOT NULL COMMENT 'Город',
  `name` varchar(255) NOT NULL COMMENT 'Название',
  `y` varchar(255) COMMENT 'Широта',
  `x` varchar(255) COMMENT 'Долгота',
  `isVisible` tinyint(1) DEFAULT 1 COMMENT 'Видимость',
  PRIMARY KEY (`id`),
  CONSTRAINT `defk` FOREIGN KEY (`delivery_price_id`) REFERENCES `delivery_price` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);


ALTER TABLE `user_invited`
	ADD COLUMN `order_id` int(11) NOT NULL AFTER `status`;

CREATE TABLE `article_categories`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Название',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);

ALTER TABLE `articles`
ADD COLUMN `category_id` int(10) NULL COMMENT 'Категория' AFTER `id`,
ADD FOREIGN KEY (`category_id`) REFERENCES `article_categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
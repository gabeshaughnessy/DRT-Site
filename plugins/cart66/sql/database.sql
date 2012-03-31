create table if not exists `[prefix]products` (
  `id` int(10) unsigned not null auto_increment,
  `name` varchar(255) not null,
  `item_number` varchar(50) not null,
  `price` decimal(8,2) not null,
  `options_1` text not null,
  `options_2` text not null,
  `custom` varchar(50) not null default 'none',
  `custom_desc` text not null,
  `taxable` tinyint(1) unsigned not null,
  `shipped` tinyint(1) unsigned not null,
  `weight` decimal(8,2) unsigned not null default 0,
  `download_path` text,
  `download_limit` tinyint default 0,
  `spreedly_subscription_id` varchar(250) not null default '',
  `allow_cancel` tinyint default 1,
  `is_paypal_subscription` tinyint default 0,
  `max_quantity` int(10) unsigned not null default 0,
  `gravity_form_id` int(10) unsigned not null default 0,
  `gravity_form_qty_id` int(10) unsigned not null default 0,
  `feature_level` varchar(255) not null,
  `setup_fee` decimal(8,2) not null,
  `billing_interval` int(10) unsigned not null,
  `billing_interval_unit` varchar(50) not null,
  `billing_cycles` int(10) unsigned not null,
  `offer_trial` tinyint(1) unsigned not null default 0,
  `trial_period` int(10) unsigned not null,
  `trial_period_unit` varchar(50) not null,
  `trial_price` decimal(8,2) not null,
  `trial_cycles` int(10) unsigned not null default 0,
  `start_recurring_number` int(10) unsigned not null default 1,
  `start_recurring_unit` varchar(50) not null,
  `price_description` varchar(255) not null,
  primary key(`id`)
);

create table if not exists `[prefix]downloads` (
  `id` int(10) unsigned not null auto_increment,
  `duid` varchar(100),
  `downloaded_on` datetime null,
  `ip` varchar(50) not null,
  primary key(`id`)
);

create table if not exists `[prefix]promotions` (
  `id` int(10) unsigned not null auto_increment,
  `code` varchar(50) not null,
  `type` enum('dollar','percentage') not null default 'dollar',
  `amount` decimal(8,2),
  `min_order` decimal(8,2),
  primary key(`id`)
);

create table if not exists `[prefix]shipping_methods` (
  `id` int(10) unsigned not null auto_increment,
  `name` varchar(100) not null,
  `default_rate` decimal(8,2) not null,
  `default_bundle_rate` decimal(8,2) not null,
  `carrier` varchar(100) not null,
  `code` varchar(50) not null,
  primary key(`id`)
);

create table if not exists `[prefix]shipping_rates` (
  `id` int(10) unsigned not null auto_increment,
  `product_id` int(10) unsigned not null,
  `shipping_method_id` int(10) unsigned not null,
  `shipping_rate` decimal(8,2) not null,
  `shipping_bundle_rate` decimal(8,2) not null,
  primary key(`id`)
);

create table if not exists `[prefix]shipping_rules` (
  `id` int(10) unsigned not null auto_increment,
  `min_amount` decimal(8,2),
  `shipping_method_id` int(10) unsigned not null,
  `shipping_cost` decimal(8,2),
  primary key(`id`)
);

create table if not exists `[prefix]tax_rates` (
  `id` int(10) unsigned not null auto_increment,
  `state` varchar(20) not null,
  `zip_low` mediumint unsigned not null default 0,
  `zip_high` mediumint unsigned not null default 0,
  `rate` decimal(8,2) not null,
  `tax_shipping` tinyint(1) not null default 0,
  primary key(`id`)
);

create table if not exists `[prefix]cart_settings` (
  `key` varchar(50) not null,
  `value` text not null,
  primary key(`key`)
);

create table if not exists `[prefix]orders` (
  `id` int(10) unsigned not null auto_increment,
  `bill_first_name` varchar(50) not null,
  `bill_last_name` varchar(50) not null,
  `bill_address` varchar(150) not null,
  `bill_address2` varchar(150) not null,
  `bill_city` varchar(150) not null,
  `bill_state` varchar(50) not null,
  `bill_country` varchar(50) not null default '',
  `bill_zip` varchar(150) not null,
  `ship_first_name` varchar(50) not null,
  `ship_last_name` varchar(50) not null,
  `ship_address` varchar(150) not null,
  `ship_address2` varchar(150) not null,
  `ship_city` varchar(150) not null,
  `ship_state` varchar(50) not null,
  `ship_country` varchar(50) not null default '',
  `ship_zip` varchar(150) not null,
  `phone` varchar(15) not null,
  `email` varchar(100) not null,
  `coupon` varchar(50) null,
  `discount_amount` decimal(8,2) not null,
  `trans_id` varchar(25) not null,
  `shipping` decimal(8,2) not null,
  `subtotal` decimal(8,2) not null,
  `tax` decimal(8,2) not null,
  `total` decimal(8,2) not null,
  `non_subscription_total` decimal(8,2) not null,
  `ordered_on` datetime,
  `status` varchar(50) not null,
  `ip` varchar(50) not null,
  `ouid` varchar(100) not null,
  `shipping_method` varchar(50),
  `account_id` int(10) unsigned not null default 0,
  primary key(`id`)
);

create table if not exists `[prefix]order_items` (
  `id` int(10) unsigned not null auto_increment,
  `order_id` int(10) unsigned not null,
  `product_id` int(10) unsigned not null,
  `item_number` varchar(50) not null,
  `product_price` decimal(8,2) not null,
  `description` text not null,
  `quantity` int(10) unsigned not null,
  `duid` varchar(100) null,
  `form_entry_ids` varchar(100) not null,
  primary key(`id`)
);

create table if not exists `[prefix]inventory` (
  `ikey` varchar(250) not null,
  `product_id` int(10) unsigned not null,
  `track` tinyint(1) unsigned not null default 0,
  `quantity` int(10) unsigned not null,
  primary key(`ikey`)
);

create table if not exists `[prefix]accounts` (
  `id` int(10) unsigned not null auto_increment,
  `first_name` varchar(100) not null,
  `last_name` varchar(100) not null,
  `email` varchar(100) not null,
  `username` varchar(50) not null,
  `password` varchar(50) not null,
  `notes` text not null,
  `created_at` datetime not null,
  `updated_at` datetime not null,
  primary key(`id`)
);

create table if not exists `[prefix]account_subscriptions` (
  `id` int(10) unsigned not null auto_increment,
  `account_id` int(10) unsigned not null,
  `billing_first_name` varchar(100),
  `billing_last_name` varchar(100),
  `feature_level` varchar(200) not null,
  `subscription_plan_name` varchar(255) not null,
  `paypal_billing_profile_id` varchar(50) not null,
  `status` varchar(20) not null default '',
  `active_until` datetime not null,
  `subscriber_token` varchar(50) not null,
  `created_at` datetime not null,
  `updated_at` datetime not null,
  `grace_until` datetime not null,
  `ready_to_renew_since` datetime not null,
  `ready_to_renew` tinyint(1) not null default 0,
  `card_expires_before_next_auto_renew` tinyint(1) not null default 0,
  `recurring` tinyint(1) not null default 0,
  `active` tinyint(1) not null default 0,
  `billing_interval` varchar(50) not null,
  primary key(`id`)
);

create table if not exists `[prefix]pp_recurring_payments` (
  `id` int(10) unsigned not null auto_increment,
  `account_id` int(10) unsigned not null,
  `recurring_payment_id` varchar(50) not null,
  `mc_gross` decimal(8,2) not null default 0,
  `txn_id` varchar(50) not null,
  `product_name` varchar(255) not null,
  `first_name` varchar(100) not null,
  `last_name` varchar(100) not null,
  `payer_email` varchar(255) not null,
  `ipn` text not null,
  `next_payment_date` varchar(100) not null,
  `time_created` datetime not null,
  `created_at` datetime not null, 
  primary key(`id`)
);

--  Upgrading to Cart66 1.0.1

alter table `[prefix]accounts` add column `notes` text not null;

--  Upgrading to Cart66 1.0.3

alter table `[prefix]products` add column `start_recurring_number` int(10) unsigned not null default 1;
alter table `[prefix]products` add column `start_recurring_unit` varchar(50) not null;
alter table `[prefix]products` add column `price_description` varchar(255) not null;

-- Upgrading to Cart66 1.0.6
alter table `[prefix]order_items` modify `description` text;
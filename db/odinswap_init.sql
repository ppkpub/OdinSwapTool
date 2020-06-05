-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2020-06-05 11:29:32
-- 服务器版本： 10.4.11-MariaDB
-- PHP 版本： 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `odinswap`
--

-- --------------------------------------------------------

--
-- 表的结构 `bids`
--

CREATE TABLE `bids` (
  `bid_rec_id` bigint(20) UNSIGNED NOT NULL,
  `bidder_uri` varchar(80) NOT NULL,
  `sell_rec_id` bigint(20) NOT NULL,
  `full_odin_uri` varchar(120) NOT NULL,
  `asset_id` varchar(64) NOT NULL,
  `remark` text NOT NULL,
  `coin_type` varchar(32) NOT NULL DEFAULT 'BTC',
  `bid_amount` decimal(20,8) NOT NULL,
  `status_code` tinyint(1) NOT NULL DEFAULT 0,
  `bid_utc` bigint(20) NOT NULL,
  `accepted_txid` varchar(80) DEFAULT NULL,
  `payment_txid` varchar(80) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `more_address_list`
--

CREATE TABLE `more_address_list` (
  `address_id` bigint(20) NOT NULL,
  `owner_uri` varchar(80) NOT NULL,
  `coin_type` varchar(32) NOT NULL,
  `address` varchar(80) NOT NULL,
  `original` varchar(255) DEFAULT NULL,
  `sign` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `private_message`
--

CREATE TABLE `private_message` (
  `msg_id` bigint(20) NOT NULL COMMENT '主键Id',
  `user_uri` varchar(80) NOT NULL COMMENT '发送者Id',
  `friend_uri` varchar(80) NOT NULL COMMENT '接受者Id',
  `sender_uri` varchar(80) NOT NULL COMMENT '发送者id',
  `receiver_uri` varchar(80) NOT NULL COMMENT '接受者Id',
  `message_type` tinyint(1) NOT NULL COMMENT '消息类型,1：普通消息 2：系统消息',
  `message_content` varchar(500) NOT NULL COMMENT '消息内容',
  `send_utc` bigint(20) NOT NULL COMMENT '消息发送时间',
  `status_code` tinyint(4) NOT NULL DEFAULT 1 COMMENT '消息状态 1：未读 2：已读 3：删除'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `qrcodelogin`
--

CREATE TABLE `qrcodelogin` (
  `id` int(11) NOT NULL,
  `qruuid` varchar(32) NOT NULL DEFAULT '',
  `user_odin_uri` varchar(50) DEFAULT NULL,
  `user_sign` mediumtext DEFAULT NULL,
  `status_code` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `sells`
--

CREATE TABLE `sells` (
  `sell_rec_id` bigint(20) UNSIGNED NOT NULL,
  `seller_uri` varchar(80) NOT NULL,
  `full_odin_uri` varchar(120) NOT NULL,
  `asset_id` varchar(64) NOT NULL,
  `recommend_names` varchar(50) NOT NULL,
  `remark` text NOT NULL,
  `coin_type` varchar(32) NOT NULL DEFAULT 'BTC',
  `start_amount` decimal(20,8) NOT NULL,
  `status_code` tinyint(1) NOT NULL DEFAULT 0,
  `start_utc` bigint(20) NOT NULL,
  `end_utc` bigint(20) NOT NULL DEFAULT 2123456789,
  `accepted_bid_rec_id` bigint(20) DEFAULT NULL,
  `accepted_utc` bigint(20) DEFAULT NULL,
  `pub_utc` bigint(20) NOT NULL DEFAULT 0,
  `update_utc` bigint(20) NOT NULL DEFAULT 0,
  `from_want_rec_id` bigint(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `wants`
--

CREATE TABLE `wants` (
  `want_rec_id` bigint(20) UNSIGNED NOT NULL,
  `wanter_uri` varchar(80) NOT NULL,
  `want_names` varchar(50) NOT NULL,
  `remark` text NOT NULL,
  `coin_type` varchar(32) NOT NULL DEFAULT 'BTC',
  `offer_amount` decimal(20,8) NOT NULL,
  `status_code` tinyint(1) NOT NULL DEFAULT 0,
  `start_utc` bigint(20) NOT NULL,
  `end_utc` bigint(20) NOT NULL DEFAULT 2123456789,
  `accepted_sell_rec_id` bigint(20) DEFAULT NULL,
  `accepted_utc` bigint(20) DEFAULT NULL,
  `pub_utc` bigint(20) NOT NULL DEFAULT 0,
  `update_utc` bigint(20) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转储表的索引
--

--
-- 表的索引 `bids`
--
ALTER TABLE `bids`
  ADD PRIMARY KEY (`bid_rec_id`),
  ADD KEY `seller_uri` (`bidder_uri`),
  ADD KEY `sell_rec_id` (`sell_rec_id`);

--
-- 表的索引 `more_address_list`
--
ALTER TABLE `more_address_list`
  ADD PRIMARY KEY (`address_id`),
  ADD UNIQUE KEY `owner_uri_2` (`owner_uri`,`coin_type`),
  ADD KEY `owner_uri` (`owner_uri`);

--
-- 表的索引 `private_message`
--
ALTER TABLE `private_message`
  ADD PRIMARY KEY (`msg_id`);

--
-- 表的索引 `qrcodelogin`
--
ALTER TABLE `qrcodelogin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qruuid` (`qruuid`);

--
-- 表的索引 `sells`
--
ALTER TABLE `sells`
  ADD PRIMARY KEY (`sell_rec_id`),
  ADD KEY `seller_uri` (`seller_uri`),
  ADD KEY `relate_want_rec_id` (`from_want_rec_id`);

--
-- 表的索引 `wants`
--
ALTER TABLE `wants`
  ADD PRIMARY KEY (`want_rec_id`),
  ADD KEY `seller_uri` (`wanter_uri`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `bids`
--
ALTER TABLE `bids`
  MODIFY `bid_rec_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `more_address_list`
--
ALTER TABLE `more_address_list`
  MODIFY `address_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `private_message`
--
ALTER TABLE `private_message`
  MODIFY `msg_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键Id';

--
-- 使用表AUTO_INCREMENT `qrcodelogin`
--
ALTER TABLE `qrcodelogin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sells`
--
ALTER TABLE `sells`
  MODIFY `sell_rec_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wants`
--
ALTER TABLE `wants`
  MODIFY `want_rec_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

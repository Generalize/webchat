-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2017-08-31 11:19:32
-- 服务器版本： 5.5.56-log
-- PHP Version: 7.0.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `think`
--
CREATE DATABASE IF NOT EXISTS `think` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `think`;

-- --------------------------------------------------------

--
-- 表的结构 `online_user`
--

DROP TABLE IF EXISTS `online_user`;
CREATE TABLE IF NOT EXISTS `online_user` (
  `openid` varchar(30) NOT NULL,
  `rid` int(10) UNSIGNED NOT NULL,
  `uid` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `serialize` varchar(5000) NOT NULL COMMENT '序列化的对象'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='在线用户表';

-- --------------------------------------------------------

--
-- 表的结构 `room`
--

DROP TABLE IF EXISTS `room`;
CREATE TABLE IF NOT EXISTS `room` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(24) NOT NULL DEFAULT '' COMMENT '房间名称',
  `avatar` varchar(24) NOT NULL DEFAULT 'mengmei.jpg' COMMENT '房间头像',
  `last_speak` int(11) DEFAULT NULL COMMENT '该房间的最后一条发言',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '房间创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COMMENT='聊天房间表';

-- --------------------------------------------------------

--
-- 表的结构 `room_user_real`
--

DROP TABLE IF EXISTS `room_user_real`;
CREATE TABLE IF NOT EXISTS `room_user_real` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户id',
  `rid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '房间id',
  `exit` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否退出',
  `black` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否黑名单',
  `role` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '角色',
  `join_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '加入时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COMMENT='房间用户关系表';

-- --------------------------------------------------------

--
-- 表的结构 `speak`
--

DROP TABLE IF EXISTS `speak`;
CREATE TABLE IF NOT EXISTS `speak` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `rid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `text` varchar(1000) DEFAULT '',
  `image` varchar(1000) DEFAULT '',
  `audio` varchar(100) DEFAULT '',
  `location` varchar(100) DEFAULT '',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=405 DEFAULT CHARSET=utf8mb4 COMMENT='发言表';

-- --------------------------------------------------------

--
-- 替换视图以便查看 `speak_view`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `speak_view`;
CREATE TABLE IF NOT EXISTS `speak_view` (
`id` int(10) unsigned
,`uid` int(10) unsigned
,`rid` int(10) unsigned
,`text` varchar(1000)
,`image` varchar(1000)
,`audio` varchar(100)
,`location` varchar(100)
,`time` timestamp
);

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nickName` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `language` char(10) NOT NULL DEFAULT '' COMMENT '语言',
  `openId` char(30) NOT NULL DEFAULT '' COMMENT '微信openid',
  `avatarUrl` varchar(80) NOT NULL DEFAULT '' COMMENT '头像',
  `phone` char(11) NOT NULL DEFAULT '' COMMENT '电话',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '密码',
  `gender` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '性别',
  `country` varchar(30) NOT NULL DEFAULT '' COMMENT '国家',
  `province` varchar(30) NOT NULL DEFAULT '' COMMENT '省',
  `city` varchar(30) NOT NULL DEFAULT '' COMMENT '城市',
  `registry_time` varchar(20) NOT NULL COMMENT '注册时间',
  `last_login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后一次登陆时间',
  `s` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用来使last_login_time变化',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- --------------------------------------------------------

--
-- 视图结构 `speak_view`
--
DROP TABLE IF EXISTS `speak_view`;

CREATE ALGORITHM=TEMPTABLE DEFINER=`think`@`localhost` SQL SECURITY DEFINER VIEW `speak_view`  AS  select `speak`.`id` AS `id`,`speak`.`uid` AS `uid`,`speak`.`rid` AS `rid`,`speak`.`text` AS `text`,`speak`.`image` AS `image`,`speak`.`audio` AS `audio`,`speak`.`location` AS `location`,`speak`.`time` AS `time` from `speak` order by `speak`.`id` desc ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

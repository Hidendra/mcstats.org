/*
Navicat MySQL Data Transfer

Source Server         : mcstats
Source Server Version : 50531
Source Host           : 10.10.1.50:3306
Source Database       : metrics

Target Server Type    : MYSQL
Target Server Version : 50531
File Encoding         : 65001

Date: 2013-09-23 23:21:02
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for Author
-- ----------------------------
DROP TABLE IF EXISTS `Author`;
CREATE TABLE `Author` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(30) NOT NULL,
  `Password` varchar(40) NOT NULL,
  `Created` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=3860 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for AuthorACL
-- ----------------------------
DROP TABLE IF EXISTS `AuthorACL`;
CREATE TABLE `AuthorACL` (
  `Author` int(11) NOT NULL,
  `Plugin` int(11) NOT NULL,
  `Pending` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Author`,`Plugin`),
  KEY `Plugin` (`Plugin`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for Country
-- ----------------------------
DROP TABLE IF EXISTS `Country`;
CREATE TABLE `Country` (
  `ShortCode` char(2) NOT NULL,
  `FullName` varchar(40) NOT NULL,
  PRIMARY KEY (`ShortCode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for CustomColumn
-- ----------------------------
DROP TABLE IF EXISTS `CustomColumn`;
CREATE TABLE `CustomColumn` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Plugin` int(11) NOT NULL,
  `Graph` int(11) NOT NULL,
  `Name` varchar(100) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_name_graph_plugin` (`Name`,`Graph`,`Plugin`),
  KEY `idx_plugin_graph` (`Plugin`,`Graph`)
) ENGINE=InnoDB AUTO_INCREMENT=976790 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for CustomData
-- ----------------------------
DROP TABLE IF EXISTS `CustomData`;
CREATE TABLE `CustomData` (
  `Server` int(11) NOT NULL,
  `Plugin` int(11) NOT NULL,
  `ColumnID` int(11) NOT NULL,
  `DataPoint` int(11) NOT NULL,
  `Updated` int(11) NOT NULL,
  UNIQUE KEY `Server` (`Server`,`Plugin`,`ColumnID`),
  KEY `crontrikey` (`Plugin`,`ColumnID`,`Updated`),
  KEY `count` (`ColumnID`,`Plugin`,`Updated`) USING BTREE
) ENGINE=MEMORY DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for Graph
-- ----------------------------
DROP TABLE IF EXISTS `Graph`;
CREATE TABLE `Graph` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Plugin` int(11) NOT NULL,
  `Type` int(11) NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `Readonly` tinyint(1) NOT NULL DEFAULT '0',
  `Position` mediumint(5) NOT NULL DEFAULT '2',
  `Name` varchar(50) NOT NULL,
  `DisplayName` varchar(50) DEFAULT NULL,
  `Scale` varchar(10) DEFAULT 'linear',
  `Halfwidth` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Uniq` (`Plugin`,`Name`) USING BTREE,
  KEY `Plugin` (`Plugin`),
  KEY `Type` (`Type`),
  KEY `Name` (`Name`),
  KEY `Active` (`Active`),
  KEY `Active2` (`Plugin`,`Active`)
) ENGINE=InnoDB AUTO_INCREMENT=85652 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for Plugin
-- ----------------------------
DROP TABLE IF EXISTS `Plugin`;
CREATE TABLE `Plugin` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(40) NOT NULL,
  `Author` varchar(75) DEFAULT NULL,
  `Hidden` tinyint(1) NOT NULL,
  `GlobalHits` int(11) NOT NULL,
  `Parent` int(11) DEFAULT '-1',
  `Created` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` int(11) DEFAULT NULL,
  `Rank` int(11) DEFAULT NULL,
  `LastRank` int(11) DEFAULT NULL,
  `LastRankChange` int(11) DEFAULT NULL,
  `ServerCount30` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name_2` (`Name`),
  KEY `Name` (`Name`),
  KEY `LastUpdated` (`LastUpdated`) USING BTREE,
  KEY `Rank` (`Rank`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11204 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for PluginRequest
-- ----------------------------
DROP TABLE IF EXISTS `PluginRequest`;
CREATE TABLE `PluginRequest` (
  `Author` int(11) NOT NULL,
  `Plugin` int(11) NOT NULL,
  `Email` varchar(100) DEFAULT '',
  `DBO` varchar(100) DEFAULT '',
  `Created` int(11) NOT NULL,
  `Complete` smallint(1) DEFAULT '0',
  UNIQUE KEY `AuthorPlugin` (`Author`,`Plugin`),
  KEY `Plugin` (`Plugin`),
  CONSTRAINT `Author` FOREIGN KEY (`Author`) REFERENCES `Author` (`ID`),
  CONSTRAINT `Plugin` FOREIGN KEY (`Plugin`) REFERENCES `Plugin` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for Server
-- ----------------------------
DROP TABLE IF EXISTS `Server`;
CREATE TABLE `Server` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `GUID` varchar(50) NOT NULL,
  `ServerVersion` varchar(75) NOT NULL,
  `Created` int(11) NOT NULL,
  `Players` smallint(11) DEFAULT NULL,
  `Country` char(2) DEFAULT 'ZZ',
  `ServerSoftware` varchar(30) NOT NULL DEFAULT 'Unknown',
  `MinecraftVersion` varchar(20) NOT NULL DEFAULT 'Unknown',
  `osname` varchar(40) NOT NULL DEFAULT '',
  `osarch` varchar(15) NOT NULL DEFAULT '',
  `osversion` varchar(70) DEFAULT NULL,
  `cores` smallint(6) NOT NULL DEFAULT '0',
  `online_mode` tinyint(4) NOT NULL DEFAULT '-1',
  `java_version` varchar(40) NOT NULL DEFAULT '',
  `java_name` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `GUID` (`GUID`)
) ENGINE=InnoDB AUTO_INCREMENT=9651086 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for ServerBlacklist
-- ----------------------------
DROP TABLE IF EXISTS `ServerBlacklist`;
CREATE TABLE `ServerBlacklist` (
  `Server` int(11) NOT NULL,
  `Violations` int(11) DEFAULT NULL,
  PRIMARY KEY (`Server`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for ServerPlugin
-- ----------------------------
DROP TABLE IF EXISTS `ServerPlugin`;
CREATE TABLE `ServerPlugin` (
  `Server` int(11) NOT NULL,
  `Plugin` int(11) NOT NULL,
  `Version` varchar(100) DEFAULT NULL,
  `Updated` int(11) NOT NULL,
  `Revision` tinyint(2) DEFAULT '0',
  UNIQUE KEY `Server` (`Server`,`Plugin`),
  KEY `Updated` (`Updated`,`Version`,`Plugin`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for VersionHistory
-- ----------------------------
DROP TABLE IF EXISTS `VersionHistory`;
CREATE TABLE `VersionHistory` (
  `Plugin` int(11) NOT NULL,
  `Server` int(11) NOT NULL,
  `Version` int(11) NOT NULL,
  `Created` int(11) NOT NULL,
  KEY `Created` (`Created`),
  KEY `Server` (`Server`),
  KEY `idx_created_version` (`Created`,`Version`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for Versions
-- ----------------------------
DROP TABLE IF EXISTS `Versions`;
CREATE TABLE `Versions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Plugin` int(11) NOT NULL,
  `Version` varchar(100) NOT NULL,
  `Created` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`Plugin`,`Version`),
  KEY `Plugin` (`Plugin`),
  KEY `Created` (`Created`)
) ENGINE=InnoDB AUTO_INCREMENT=39878 DEFAULT CHARSET=latin1;

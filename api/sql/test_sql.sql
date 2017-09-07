CREATE DATABASE `token` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `token`;

-- Authorised users
CREATE TABLE `user` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(40) NOT NULL,
  `Password` char(40) NOT NULL,
  `Permissions` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- PDF file info table
CREATE TABLE `pdf` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Filename` varchar(100) NOT NULL,
  `UploadDate` datetime NOT NULL,
  `FileUrl` varchar(250) NOT NULL,
  `UploadUserID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `fk_pdf_uploader_idx` (`UploadUserID`),
  CONSTRAINT `fk_pdf_uploader` FOREIGN KEY (`UploadUserID`) REFERENCES `user` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Users  with different permissions
INSERT INTO user (Username, Password, Permissions) VALUES
	('employee@token.com',sha1('employee'), '["list","view","upload"]'),
    ('secretary@token.com',sha1('secretary'), '["list","view","upload"]'),
    ('admin@token.com',sha1('admin'), '["list","view","upload"]'),
    ('user@token.com',sha1('user'), '["list"]'),
    ('guest@token.com',sha1('guest'), '[]');
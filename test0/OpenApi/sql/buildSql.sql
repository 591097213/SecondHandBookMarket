CREATE DATABASE bookTrading,
CREATE TABLE `bookTrading`.`book` (
    `bookId` INT NOT NULL,
    `ratingMax` FLOAT NULL,
    `ratingNumRaters` INT NULL,
    `ratingAverage` FLOAT NULL,
    `ratingMin` FLOAT NULL,
    `subtitle` VARCHAR (128) NULL,
    `pubdate` DATE NULL,
    `originTitle` VARCHAR (128) NULL,
    `pages` INT NULL,
    `Isbn10` CHAR (10) NULL,
    `Isbn13` CHAR (13) NOT NULL,
    `title` VARCHAR (128) NOT NULL,
    `altTitle` VARCHAR (128) NULL,
    `price` VARCHAR (128) NULL,
    `classNum` VARCHAR (128) NULL,
    `queryTimes` INT NULL,
    `other` VARCHAR (128) NULL,
    PRIMARY KEY (`bookId`)
) ENGINE = InnoDB;


CREATE TABLE `bookTrading`.`tag` (
    `tagid` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR (128) NOT NULL,
    `title` VARCHAR (128) NOT NULL,
    `count` INT NOT NULL
) ENGINE = InnoDB;


CREATE TABLE `bookTrading`.`user` (
    `openId` VARCHAR (128) NOT NULL PRIMARY KEY,
    `qq` CHAR (12) NULL,
    `tel` char(11) NULL,
    `weChat` VARCHAR (128) NULL,
    `bookCount` SMALLINT NULL,
    `isReg` SMALLINT NULL,
    `regTime` DATE NULL,
    `totalQueryTimes` INT(128) NULL,
    `accusationNum` INT(128) NULL,
    `todayQueryTimes` INT(128) NULL,
    `state` INT(128) NULL,
) ENGINE = InnoDB;


CREATE TABLE `bookTrading`.`bookAuthor` (
    `bookId` INT NOT NULL,
    `author` VARCHAR(128) NOT NULL,
    PRIMARY KEY (`bookId`, `author`),
    FOREIGN KEY (`bookId`) REFERENCES book(`bookId`)
) ENGINE = InnoDB;


CREATE TABLE `bookTrading`.`bookTranslator` (
    `bookId` INT NOT NULL,
    `translator` VARCHAR(128) NOT NULL,
    PRIMARY KEY (`bookId`, `translator`),
    FOREIGN KEY (`bookId`) REFERENCES book(`bookId`)
) ENGINE = InnoDB;


CREATE TABLE `bookTrading`.`bookUser` (
    `bookId` INT NOT NULL,
    `OpenId` VARCHAR(128) NOT NULL,
    `regTime` DATE NOT NULL,
    `isSell` SMALLINT NOT NULL,
    `count` INT(128) NOT NULL,
    PRIMARY KEY (`bookId`, `openId`),
    FOREIGN KEY (`bookId`) REFERENCES book(`bookId`),
    FOREIGN KEY (`openId`) REFERENCES buser(`openId`)
) ENGINE = InnoDB;


CREATE TABLE `bookTrading`.`bookTag` (
    `bookid` INT(128) NOT NULL,
    `tagid` INT(128) NOT NULL,
    PRIMARY KEY(`bookid`, `tagid`),
    FOREIGN KEY (`bookId`) REFERENCES book(`bookId`),
    FOREIGN KEY (`tagId`) REFERENCES tag(`tagId`)
) ENGINE = InnoDB;


CREATE TABLE `bookTrading`.`bookPublisher` (
    `bookId` INT(128) NOT NULL,
    `publisher` VARCHAR(128) NOT NULL,
    PRIMARY KEY (`bookId`, `publisher`),
    FOREIGN KEY (`bookId`) REFERENCES book(`bookId`)
) ENGINE = InnoDB;
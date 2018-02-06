/**************************创建数据库及表**************************/
CREATE DATABASE bookTrading


/*存放书籍信息*/
CREATE TABLE `bookTrading`.`book`(
    `bookId` INT NOT NULL,/*用豆瓣的bookId作为主键比用isbn更合理*/
    `ratingMax` FLOAT NULL,/*豆瓣评分，浮点数格式*/
    `ratingNumRaters` INT NULL,
    `ratingAverage` FLOAT NULL,/*浮点数*/
    `ratingMin` FLOAT NULL,
    `subtitle` VARCHAR(128) NULL,
    `pubdate` DATE NULL,/*yyyy-mm-dd*/
    `originTitle` VARCHAR(128) NULL,
    `pages` INT NULL,
    `Isbn10` CHAR(10) NULL,
    `Isbn13` CHAR(13) NOT NULL,
    `title` VARCHAR(128) NOT NULL,
    `altTitle` VARCHAR(128) NULL,
    `price` VARCHAR(128) NULL,/*豆瓣上的价格会加上“元”，故采用字符串格式记录*/
    `classNum` VARCHAR(128) NULL,/*书籍分类号，豆瓣上无此字段，后续完善*/
    `queryTimes` INT NOT NULL DEFAULT 0,/*该书被查询次数。用于评估该书的热度*/
    `publisher` VARCHAR(128) NULL,/*出版社也可能为空，一本书最多对应一个出版社*/
    `keyWords` VARCHAR(256) null, /*关键词，用于全文查询*/
    PRIMARY KEY(`bookId`),
    FULLTEXT KEY `index` (`subtitle`,`originTitle`,`Isbn10`,`Isbn13`,`title`,`altTitle`,`publisher`,`keyWords`)

    
) ENGINE = InnoDB;


/*标签*/
CREATE TABLE `bookTrading`.`tag`(
    `tagId` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,/*自动增加*/
    `name` VARCHAR(128) NOT NULL UNIQUE,
    `title` VARCHAR(128) NOT NULL
) ENGINE = InnoDB;

/*用户表。用户订阅公众号后应立即添加到用户表里，而不要等到用户第一次扫描后才添加到表中*/
CREATE TABLE `bookTrading`.`user`(
    `openId` VARCHAR(128) NOT NULL,
    `qq` varchar(15) NULL,
    `tel` varchar(15) NULL,
    `weChat` VARCHAR(128) NULL,
    `address` SMALLINT NOT NULL DEFAULT 0,/*用户地址，0表示未知，1表示达理，2表示瑞景，3表示白宫*/ 
    `bookCount` SMALLINT NOT NULL DEFAULT 0,/*登记书籍总数*/
    `isReg` SMALLINT NOT NULL DEFAULT 0,/*用户是否已登记联系方式*/
    `regTime` DATE NULL,/*用户登记联系方式的日期，并非用户关注公众号的日期*/
    `totalQueryTimes` INT(128) NOT NULL DEFAULT 0,/*总查询次数*/
    `accusationNum` INT(128) NOT NULL DEFAULT 0,/*用户参与举报次数，用于评估用户*/
    `reportedNum` INT(128) NOT NULL DEFAULT 0,/*被举报次数，*/
    `todayQueryTimes` INT(128) NOT NULL DEFAULT 0,/*用户本日查询次数。为防范用户联系方式泄露，限制每个用户每日可查询10次*/
    `state` SMALLINT(128) NOT NULL DEFAULT 0,/*用户所处的状态，0表示正常，1表示正在登记联系方式，2表示正在查询“我的已发布信息”,3表示正在举报不良用户*/
    `stateChangeTime` TIMESTAMP NOT NULL DEFAULT NOW(),/*用户状态上一次改变时间。用于计时*/
    PRIMARY KEY(`openId`)
) ENGINE = InnoDB;


CREATE TABLE `bookTrading`.`bookAuthor`(
    `bookId` INT NOT NULL,
    `author` VARCHAR(128) NOT NULL,
    PRIMARY KEY(`bookId`, `author`),
    FOREIGN KEY(`bookId`) REFERENCES book(`bookId`)
) ENGINE = InnoDB;


CREATE TABLE `bookTrading`.`bookTranslator`(
    `bookId` INT NOT NULL,
    `translator` VARCHAR(128) NOT NULL,
    PRIMARY KEY(`bookId`, `translator`),
    FOREIGN KEY(`bookId`) REFERENCES book(`bookId`)
) ENGINE = InnoDB;


CREATE TABLE `bookTrading`.`bookUser`(
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,/*自动增加*/
    `number` INT NULL,
    `bookId` INT NOT NULL,
    `openId` VARCHAR(128) NOT NULL,
    `firstRegTime` TIMESTAMP NOT NULL DEFAULT NOW(),/*用户第一次登记该书的时间*/
    `lastRegTime` TIMESTAMP NOT NULL DEFAULT NOW(),/*用户刷新该书的时间（一次登记后有效期为30天，在30天内或30天后重复登记则刷新生存时间）*/
    `isSell` SMALLINT NOT NULL DEFAULT 0, /*0表示未售出，1表示已售出。在30天有效期内用户可以手动下架该书。30天后自动将置1。置1后刷新时生存时间时则置0*/
    FOREIGN KEY(`bookId`) REFERENCES book(`bookId`), 
    FOREIGN KEY(`openId`) REFERENCES USER(`openId`)
) ENGINE = InnoDB;
    
    
CREATE TABLE `bookTrading`.`bookTag`(
    `bookId` INT NOT NULL,
    `tagId` INT(128) NOT NULL,
    `count` INT NOT NULL,
    PRIMARY KEY(`bookId`, `tagId`),
    FOREIGN KEY(`bookId`) REFERENCES book(`bookId`),
    FOREIGN KEY(`tagId`) REFERENCES tag(`tagId`)
) ENGINE = InnoDB;


/*记录用户给后台发送的文本消息*/
CREATE TABLE `bookTrading`.`Text`(
    `openId` VARCHAR(128) NOT NULL,
    `creatTime` TIMESTAMP NOT NULL,
    `content` VARCHAR(65535) NOT NULL,
    `msgId` INT PRIMARY KEY NOT NULL,
    FOREIGN KEY(`openId`) REFERENCES user(`openId`)
) ENGINE = InnoDB;



/**********************存储过程、事件等****************************************/

/*查看事件监视器是否打开*/
SHOW VARIABLES LIKE 'event_scheduler';

/*打开事件监视器*/
SET GLOBAL event_scheduler = ON;

/*存储过程：将用户状态改为0，并更改stateChangeTime*/
DROP
PROCEDURE IF EXISTS stateTo0;
DELIMITER
    ;;
CREATE
PROCEDURE stateTo0(`id` VARCHAR(128))
BEGIN
    UPDATE
        `user`
    SET
        `state` = 0, `stateChangeTime`=NOW()
    WHERE
        `openId` = `id` ;
END ;;
DELIMITER
    ;


/*存储过程：将用户状态改为1，并更改stateChangeTime*/
DROP
PROCEDURE IF EXISTS stateTo1;
DELIMITER
    ;;
CREATE
PROCEDURE stateTo1(`id` VARCHAR(128))
BEGIN
UPDATE
    `user`
SET
    `state` = 1, `stateChangeTime`=NOW()
WHERE
    `openId` = `id` ;
END ;;
DELIMITER
    ;


/*存储过程：将用户状态改为2，并更改stateChangeTime*/
DROP
PROCEDURE IF EXISTS stateTo2;
DELIMITER
    ;;
CREATE
PROCEDURE stateTo2(`id` VARCHAR(128))
BEGIN
UPDATE
    `user`
SET
    `state` = 2, `stateChangeTime`=NOW()
WHERE
    `openId` = `id` ;
END ;;
DELIMITER
    ;

/*存储过程：将用户对应的书籍isSell位置1*/
DROP
PROCEDURE IF EXISTS isSellTo1;
DELIMITER
    ;;
CREATE
PROCEDURE isSellTo1(`index` int ,`id` VARCHAR(128))
BEGIN
    UPDATE
        `bookUser`
    SET
        `isSell` = 1
    WHERE
        `openId` = `id` AND `number`=`index` AND `isSell`= 0;
END ;;
DELIMITER
    ;


/*存储过程：为用户的在售书籍分配编号*/
DROP PROCEDURE IF EXISTS
    ALLOCATION;
DELIMITER
    ;;
CREATE PROCEDURE ALLOCATION(`PopenId` VARCHAR(128))
BEGIN

    DECLARE  `Pid` INT ;
    DECLARE  `@nu` INT DEFAULT 1;
    -- 遍历数据结束标志
    DECLARE `done` INT DEFAULT FALSE;
    -- 游标
    DECLARE `cur_account` CURSOR FOR select `id` from `bookUser` where `openId`=`PopenId` AND `isSell`=0 ORDER BY `lastRegTime`;
    -- 将结束标志绑定到游标
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET `done` = TRUE;
    
    -- 打开游标
    OPEN  `cur_account`;
    -- 遍历
    `read_loop`: LOOP
            -- 取值 取多个字段
            FETCH  NEXT from `cur_account` INTO `Pid`; 
            IF `done` THEN
                LEAVE `read_loop`;
             END IF;
 
        -- 你自己想做的操作
            UPDATE `bookUser`
            SET `number`= `@nu`
            where `id`=`Pid`;
            set `@nu`=`@nu`+1;
    END LOOP;
    CLOSE cur_account;
END ;;
DELIMITER
    ;



/*存储过程：遍历user表，更改状态*/
DROP PROCEDURE IF EXISTS
    changeState;
delimiter ;;
create PROCEDURE changeState()
BEGIN
    DECLARE  `id` varchar(128);
    DECLARE  `ti`  TIMESTAMP;
    DECLARE  `sta`  SMALLINT;
    -- 遍历数据结束标志
    DECLARE `done` INT DEFAULT FALSE;
    -- 游标
    DECLARE `cur_account` CURSOR FOR select `openId`,`stateChangeTime`,`state` from `user`;
    -- 将结束标志绑定到游标
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET `done` = TRUE;
    
    -- 打开游标
    OPEN  `cur_account`;
    -- 遍历
    `read_loop`: LOOP
            -- 取值 取多个字段
            FETCH  NEXT from `cur_account` INTO `id`,`ti`,`sta`; 
            IF `done` THEN
                LEAVE `read_loop`;
             END IF;
 
        -- 你自己想做的操作
        IF NOW()-`ti`>=300 AND `sta`!=0 THEN
            UPDATE `user`
            SET `state`=0,`stateChangeTime`=NOW()
            WHERE `openId`=`id`;
            END IF;
    END LOOP;
    CLOSE cur_account;
END ;;
DELIMITER
    ;

/*存储过程：全文检索*/
DROP
PROCEDURE IF EXISTS query;
DELIMITER
    ;;
CREATE
PROCEDURE query(`key` VARCHAR(256))
BEGIN
   SELECT `bookId`,`title`,`subtitle`,`publisher`,MATCH (`subtitle`,`originTitle`,`isbn10`,`isbn13`,`title`,`altTitle`,`publisher`,`keyWords`) AGAINST (`key`) as score
   FROM `book`
   WHERE MATCH (`subtitle`,`originTitle`,`isbn10`,`isbn13`,`title`,`altTitle`,`publisher`,`keyWords`) AGAINST (`key` IN NATURAL LANGUAGE MODE) order by score DESC;
END ;;
DELIMITER
    ;


/*事件：每秒查询一次，若用户状态非0超过5分钟，则改为0*/
DROP EVENT IF EXISTS
    EVERY1Min;
CREATE EVENT EVERY1S
ON SCHEDULE EVERY 1 MINUTE
ON COMPLETION NOT PRESERVE
DO
CALL
    changeState();





/****************************************以下表暂未实现**************************************/


/*记录公众号所接收的订阅或取消订阅操作，相当于日志，可用于分析共有多少人订阅过该公众号*/
CREATE TABLE `bookTrading`.`subscribe`(
    `openId` VARCHAR(128) NOT NULL,
    `creatTime` TIMESTAMP NOT NULL,/*订阅或取消订阅的时间*/
    `event` SMALLINT NOT NULL,/*1表示subscribe，0表示unsubscribe*/
    PRIMARY KEY(`openId`, `creatTime`),
    FOREIGN KEY(`openId`) REFERENCES user(`openId`)
) ENGINE = InnoDB;


/*记录进入公众号消息*/
CREATE TABLE `bookTrading`.`enterAgent`(
    `openId` VARCHAR(128) NOT NULL,
    `creatTime` TIMESTAMP NOT NULL,/*进入的时间*/
    PRIMARY KEY(`openId`, `creatTime`),
    FOREIGN KEY(`openId`) REFERENCES user(`openId`)
) ENGINE = InnoDB;


/*记录用户地理位置*/
CREATE TABLE `bookTrading`.`enterAgent`(
    `openId` VARCHAR(128) NOT NULL,
    `creatTime` TIMESTAMP NOT NULL,/*进入的时间*/
    `latitude` FLOAT NOT NULL,/*纬度*/
    `longitude` FLOAT NOT NULL,/*经度*/
    `precision` FLOAT NOT NULL,/*精度*/
    PRIMARY KEY(`openId`, `creatTime`),
    FOREIGN KEY(`openId`) REFERENCES user(`openId`)
) ENGINE = InnoDB;


/*记录各个菜单被点击的次数*/
CREATE TABLE `bookTrading`.`enterAgent`(
) ENGINE = InnoDB;


/*记录扫码次数*/
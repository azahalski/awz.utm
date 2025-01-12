CREATE TABLE IF NOT EXISTS `b_awz_utm` (
    ID int(18) NOT NULL AUTO_INCREMENT,
    PARENT_ID int(18) DEFAULT NULL,
    SITE_ID varchar(2) DEFAULT NULL,
    IP_ADDR varchar(45) DEFAULT NULL,
    U_AGENT varchar(255) DEFAULT NULL,
    REFERER varchar(255) DEFAULT NULL,
    `SOURCE` varchar(25) DEFAULT NULL,
    MEDIUM varchar(25) DEFAULT NULL,
    CAMPAIGN varchar(65) DEFAULT NULL,
    CONTENT varchar(65) DEFAULT NULL,
    TERM varchar(65) DEFAULT NULL,
    DATE_ADD datetime DEFAULT NULL,
    PRIMARY KEY (`ID`),
    index SITE_ID (SITE_ID),
    index DATE_ADD (DATE_ADD)
);
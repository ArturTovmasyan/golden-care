USE `cc_old`;
DELIMITER $$
DROP PROCEDURE IF EXISTS json_row_data $$
CREATE PROCEDURE json_row_data(
  IN tbl_tmp CHAR(255),
  IN tbl_name CHAR(255),
  IN field_name CHAR(255)
)
BEGIN
  SET @max_data_length = 0;
  SET @q_max_len = CONCAT('SELECT MAX(JSON_LENGTH(`', tbl_name, '`.`', field_name, '`)) INTO @max_data_length FROM `', tbl_name, '`');
  SET @q_tmp_drp = CONCAT('DROP TEMPORARY TABLE IF EXISTS `', tbl_tmp, '`');
  SET @q_tmp_crt = CONCAT('CREATE TEMPORARY TABLE `', tbl_tmp, '` (seq BIGINT PRIMARY KEY) ENGINE = MEMORY');
  SET @q_tmp_ins = CONCAT('INSERT INTO `', tbl_tmp, '`(seq) VALUES (@start)');

  PREPARE stmt FROM @q_max_len;
  EXECUTE stmt;
  
  SET @step  = 1;
  SET @start = 1;
  SET @stop  = @max_data_length;


  PREPARE stmt FROM @q_tmp_drp;
  EXECUTE stmt;
  PREPARE stmt FROM @q_tmp_crt;
  EXECUTE stmt;

  WHILE @start <= @stop DO
    PREPARE stmt FROM @q_tmp_ins;
    EXECUTE stmt;
    SET @start = @start + @step;
  END WHILE;
END $$
DELIMITER ;

USE `alms`;
DELIMITER $$
DROP PROCEDURE IF EXISTS json_row_data $$
CREATE PROCEDURE json_row_data(
  IN tbl_tmp CHAR(255),
  IN tbl_name CHAR(255),
  IN field_name CHAR(255)
)
BEGIN
  SET @max_data_length = 0;
  SET @q_max_len = CONCAT('SELECT MAX(JSON_LENGTH(`', tbl_name, '`.`', field_name, '`)) INTO @max_data_length FROM `', tbl_name, '`');
  SET @q_tmp_drp = CONCAT('DROP TEMPORARY TABLE IF EXISTS `', tbl_tmp, '`');
  SET @q_tmp_crt = CONCAT('CREATE TEMPORARY TABLE `', tbl_tmp, '` (seq BIGINT PRIMARY KEY) ENGINE = MEMORY');
  SET @q_tmp_ins = CONCAT('INSERT INTO `', tbl_tmp, '`(seq) VALUES (@start)');

  PREPARE stmt FROM @q_max_len;
  EXECUTE stmt;
  
  SET @step  = 1;
  SET @start = 1;
  SET @stop  = @max_data_length;


  PREPARE stmt FROM @q_tmp_drp;
  EXECUTE stmt;
  PREPARE stmt FROM @q_tmp_crt;
  EXECUTE stmt;

  WHILE @start <= @stop DO
    PREPARE stmt FROM @q_tmp_ins;
    EXECUTE stmt;
    SET @start = @start + @step;
  END WHILE;
END $$
DELIMITER ;

-------------------------------------------------------------------------
INSERT INTO `db_seniorcare_migration`.`oauth2_client` (
    `id`,
    `random_id`,
    `redirect_uris`,
    `secret`,
    `allowed_grant_types`
) VALUES (
    1,
    '3hmo4b44gomcss0sk8okk0gc88wo0kwocco0s0w4w0w48ww0c8',
    'a:0:{}',
    '4tonqf6g79yc4ccsscokcogk08wscs40wookco8048wwk0k0kw',
    'a:5:{i:0;s:18:"authorization_code";i:1;s:8:"password";i:2;s:13:"refresh_token";i:3;s:5:"token";i:4;s:18:"client_credentials";}'
);

INSERT INTO `db_seniorcare_migration`.`tbl_user` (
    `id`, 
    `first_name`,
    `last_name`,
    `username`,
    `password`,
    `email`,
    `enabled`,
    `last_activity_at`,
    `password_requested_at`,
    `password_recovery_hash`,
    `completed`
) VALUES (
    1,
    'user',
    'user',
    'user',
    '$argon2i$v=19$m=1024,t=2,p=2$RmlES0pUNkhWQVZsWDBCVg$j+vH8WyvzWBjR6fieZ+u6dVCcRh24BcM2eWuu7NW0L0',
    'user@user.u',
    1,
    '2018-12-08 15:50:56',
    null,
    '',
    1
);

INSERT INTO `db_seniorcare_migration`.`tbl_space` (`id`, `name`) VALUES (1, 'CiminoCare');

### Payment Source
INSERT INTO `db_seniorcare_migration`.`tbl_payment_source`
 (
    `id_space`,
    `id`,
    `title`
 ) SELECT
    1,
    `alms`.`common_payment_source`.`id`     AS 'id',
    `alms`.`common_payment_source`.`title`  AS 'title'
FROM `alms`.`common_payment_source`;

### Salutation
INSERT INTO `db_seniorcare_migration`.`tbl_salutation`
 (
    `id_space`,
    `id`,
    `title`
 ) SELECT
    1,
    `cc_old`.`salutations`.`Salutation_ID` AS 'id',
    `cc_old`.`salutations`.`Salutation`    AS 'title'
FROM `cc_old`.`salutations`;

### Relationship
INSERT INTO `db_seniorcare_migration`.`tbl_relationship` (
 	`id_space`,
 	`id`,
 	`title`
) SELECT
    1,
    `cc_old`.`relationshiptypes`.`Relationship_ID` AS 'id',
    `cc_old`.`relationshiptypes`.`Relationship`    AS 'title'
FROM `cc_old`.`relationshiptypes` WHERE `cc_old`.`relationshiptypes`.`Relationship_ID` <= 69;

INSERT INTO `db_seniorcare_migration`.`tbl_relationship` (
 	`id_space`,
 	`id`,
 	`title`
) SELECT
    1,
    (`cc_old`.`relationshiptypes`.`Relationship_ID` + 100) AS 'id',
    `cc_old`.`relationshiptypes`.`Relationship`            AS 'title'
FROM `cc_old`.`relationshiptypes` WHERE `cc_old`.`relationshiptypes`.`Relationship_ID` >= 70 and `cc_old`.`relationshiptypes`.`Relationship_ID` <= 73;

INSERT INTO `db_seniorcare_migration`.`tbl_relationship` (
 	`id_space`,
 	`id`,
 	`title`
) SELECT
    1,
    (`alms`.`common_relationship`.`id` + 200) AS 'id',
    `alms`.`common_relationship`.`title`      AS 'title'
FROM `alms`.`common_relationship` WHERE `alms`.`common_relationship`.`id` >= 70 and `alms`.`common_relationship`.`id` <= 72;

### Care Level
INSERT INTO `db_seniorcare_migration`.`tbl_care_level` (
    `id_space`,
    `id`,
    `title`,
    `description`
) SELECT
    1,
    `cc_old`.`careleveltypes`.`Care_Level_ID`    AS 'id',
    `cc_old`.`careleveltypes`.`Care_Level`       AS 'title',
    `cc_old`.`careleveltypes`.`Care_Description` AS 'description'
FROM `cc_old`.`careleveltypes`;

### City/State/Zip
INSERT INTO `db_seniorcare_migration`.`tbl_city_state_zip` (
    `id_space`,
    `id`,
    `state_full`,
    `state_abbr`,
    `zip_main`,
    `zip_sub`,
    `city`
)
SELECT
    1,
    `cc_old`.`citystatezip`.`CSZ_ID`      AS 'id',
    `cc_old`.`citystatezip`.`State_Full`  AS 'state_full',
    `cc_old`.`citystatezip`.`State_2_Ltr` AS 'state_abbr',
    `cc_old`.`citystatezip`.`ZIP_Main`    AS 'zip_main',
    `cc_old`.`citystatezip`.`ZIP_Sub`     AS 'zip_sub',
    `cc_old`.`citystatezip`.`City`        AS 'city'
FROM `cc_old`.`citystatezip` WHERE `cc_old`.`citystatezip`.`CSZ_ID` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_city_state_zip` (
    `id_space`,
    `id`,
    `state_full`,
    `state_abbr`,
    `zip_main`,
    `zip_sub`,
    `city`
)
SELECT
    1,
    (`cc_old`.`citystatezip`.`CSZ_ID` + 10000)      AS 'id',
    `cc_old`.`citystatezip`.`State_Full`            AS 'state_full',
    `cc_old`.`citystatezip`.`State_2_Ltr`           AS 'state_abbr',
    `cc_old`.`citystatezip`.`ZIP_Main`              AS 'zip_main',
    `cc_old`.`citystatezip`.`ZIP_Sub`               AS 'zip_sub',
    `cc_old`.`citystatezip`.`City`                  AS 'city'
FROM `cc_old`.`citystatezip` WHERE `cc_old`.`citystatezip`.`CSZ_ID` >= 2817;

INSERT INTO `db_seniorcare_migration`.`tbl_city_state_zip` (
    `id_space`,
    `id`,
    `state_full`,
    `state_abbr`,
    `zip_main`,
    `zip_sub`,
    `city`
)
SELECT
    1,
    (`alms`.`common_city_state_zip`.`id` + 20000)   AS 'id',
    `alms`.`common_city_state_zip`.`state_full`     AS 'state_full',
    `alms`.`common_city_state_zip`.`state_2_ltr`    AS 'state_abbr',
    `alms`.`common_city_state_zip`.`zip_main`       AS 'zip_main',
    `alms`.`common_city_state_zip`.`zip_sub`        AS 'zip_sub',
    `alms`.`common_city_state_zip`.`city`           AS 'city'
FROM `alms`.`common_city_state_zip` WHERE `alms`.`common_city_state_zip`.`id` >= 2817;

### Diet
INSERT INTO `db_seniorcare_migration`.`tbl_diet` (
    `id_space`,
    `id`,
    `color`,
    `title`
) SELECT
    1,
    `cc_old`.`dietcategory`.`Dietcategory_ID` AS 'id',
    `cc_old`.`dietcategory`.`Color`           AS 'color',
    `cc_old`.`dietcategory`.`Name`            AS 'title'
FROM `cc_old`.`dietcategory`;

### Medical History Condition
INSERT INTO `db_seniorcare_migration`.`tbl_medical_history_condition` (
    `id_space`,
    `id`,
    `title`,
    `description`
) SELECT
    1,
    `cc_old`.`medicalhistoryconditions`.`Medical_History_Condition_ID` AS 'id',
    `cc_old`.`medicalhistoryconditions`.`Condition_Name`               AS 'title',
    `cc_old`.`medicalhistoryconditions`.`Condition_Description`        AS 'description'
FROM `cc_old`.`medicalhistoryconditions`;

### Allergen
INSERT INTO `db_seniorcare_migration`.`tbl_allergen` (
    `id_space`,
    `id`,
    `title`,
    `description`
) SELECT
    1,
    `cc_old`.`allergens`.`Allergen_ID`          AS 'id',
    `cc_old`.`allergens`.`Allergen`             AS 'title',
    `cc_old`.`allergens`.`Allergen_Description` AS 'description'
FROM `cc_old`.`allergens`;

### Diagnosis
INSERT INTO `db_seniorcare_migration`.`tbl_diagnosis` (
    `id_space`,
    `id`,
    `title`,
    `description`,
    `acronym`
) SELECT
    1,
    `cc_old`.`medicalconditions`.`Medical_Condition_ID`  AS 'id',
    `cc_old`.`medicalconditions`.`Condition_Name`        AS 'title',
    `cc_old`.`medicalconditions`.`Condition_Description` AS 'description',
    `cc_old`.`medicalconditions`.`Acronym`               AS 'acronym'
FROM `cc_old`.`medicalconditions` WHERE `cc_old`.`medicalconditions`.`Medical_Condition_ID` <= 2614;

INSERT INTO `db_seniorcare_migration`.`tbl_diagnosis` (
    `id_space`,
    `id`,
    `title`,
    `description`,
    `acronym`
) SELECT
    1,
    (`cc_old`.`medicalconditions`.`Medical_Condition_ID` + 10000)  AS 'id',
    `cc_old`.`medicalconditions`.`Condition_Name`                  AS 'title',
    `cc_old`.`medicalconditions`.`Condition_Description`           AS 'description',
    `cc_old`.`medicalconditions`.`Acronym`                         AS 'acronym'
FROM `cc_old`.`medicalconditions` WHERE `cc_old`.`medicalconditions`.`Medical_Condition_ID` >= 2615;

INSERT INTO `db_seniorcare_migration`.`tbl_diagnosis` (
    `id_space`,
    `id`,
    `title`,
    `description`,
    `acronym`
) SELECT
    1,
    (`alms`.`common_diagnosis`.`id` + 20000)  AS 'id',
    `alms`.`common_diagnosis`.`title`         AS 'title',
    `alms`.`common_diagnosis`.`description`   AS 'description',
    `alms`.`common_diagnosis`.`acronym`       AS 'acronym'
FROM `alms`.`common_diagnosis` WHERE `alms`.`common_diagnosis`.`id` >= 2615;

### Medication
INSERT INTO `db_seniorcare_migration`.`tbl_medication` (
    `id_space`,
    `id`,
    `title`
) SELECT
     1,
     `cc_old`.`medications`.`Medication_ID` AS 'id',
     `cc_old`.`medications`.`Med_Name`      AS 'title'
FROM `cc_old`.`medications` WHERE `cc_old`.`medications`.`Medication_ID` <= 1034;

INSERT INTO `db_seniorcare_migration`.`tbl_medication` (
    `id_space`,
    `id`,
    `title`
) SELECT
     1,
     (`cc_old`.`medications`.`Medication_ID` + 10000 ) AS 'id',
     `cc_old`.`medications`.`Med_Name`                 AS 'title'
FROM `cc_old`.`medications` WHERE `cc_old`.`medications`.`Medication_ID` >= 1035;

INSERT INTO `db_seniorcare_migration`.`tbl_medication` (
    `id_space`,
    `id`,
    `title`
) SELECT
     1,
     (`alms`.`common_medication`.`id` + 20000 ) AS 'id',
     `alms`.`common_medication`.`title`         AS 'title'
FROM `alms`.`common_medication` WHERE `alms`.`common_medication`.`id` >= 1035;

### Physician
INSERT INTO `db_seniorcare_migration`.`tbl_physician` (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `office_phone`,
    `fax`,
    `emergency_phone`,
    `email`,
    `website_url`
) SELECT
    1,
    `cc_old`.`physicians`.`Physician_ID`    AS 'id',
    `cc_old`.`physicians`.`CSZ_ID`          AS 'id_csz',
    `cc_old`.`people`.`Salutation_ID`       AS 'id_salutation',
    `cc_old`.`people`.`First_Name`          AS 'first_name',
    `cc_old`.`people`.`Last_name`           AS 'last_name',
    `cc_old`.`people`.`Middle_Initial`      AS 'middle_name',
    `cc_old`.`physicians`.`Address_1`       AS 'address_1',
    `cc_old`.`physicians`.`Address_2`       AS 'address_2',
    `cc_old`.`physicians`.`Office_Phone`    AS 'office_phone',
    `cc_old`.`physicians`.`Fax`             AS 'fax',
    `cc_old`.`physicians`.`Emergency_Phone` AS 'emergency_phone',
    `cc_old`.`physicians`.`Email`           AS 'email',
    `cc_old`.`physicians`.`Website_URL`     AS 'website_url'
FROM `cc_old`.`physicians`
INNER JOIN `cc_old`.`people` ON `cc_old`.`physicians`.`People_ID` = `cc_old`.`people`.`People_ID`
WHERE `cc_old`.`physicians`.`CSZ_ID` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_physician` (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `office_phone`,
    `fax`,
    `emergency_phone`,
    `email`,
    `website_url`
) SELECT
    1,
    `cc_old`.`physicians`.`Physician_ID`     AS 'id',
    (`cc_old`.`physicians`.`CSZ_ID` + 10000) AS 'id_csz',
    `cc_old`.`people`.`Salutation_ID`        AS 'id_salutation',
    `cc_old`.`people`.`First_Name`           AS 'first_name',
    `cc_old`.`people`.`Last_name`            AS 'last_name',
    `cc_old`.`people`.`Middle_Initial`       AS 'middle_name',
    `cc_old`.`physicians`.`Address_1`        AS 'address_1',
    `cc_old`.`physicians`.`Address_2`        AS 'address_2',
    `cc_old`.`physicians`.`Office_Phone`     AS 'office_phone',
    `cc_old`.`physicians`.`Fax`              AS 'fax',
    `cc_old`.`physicians`.`Emergency_Phone`  AS 'emergency_phone',
    `cc_old`.`physicians`.`Email`            AS 'email',
    `cc_old`.`physicians`.`Website_URL`      AS 'website_url'
FROM `cc_old`.`physicians`
INNER JOIN `cc_old`.`people` ON `cc_old`.`physicians`.`People_ID` = `cc_old`.`people`.`People_ID`
WHERE `cc_old`.`physicians`.`CSZ_ID` >= 2817;

INSERT INTO `db_seniorcare_migration`.`tbl_physician` (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `office_phone`,
    `fax`,
    `emergency_phone`,
    `email`,
    `website_url`
) SELECT
    1,
    (`alms`.`common_physician`.`id` + 10000)         AS `id`,
    `alms`.`common_physician`.`id_common_csz`        AS `id_csz`,
    `alms`.`common_physician`.`id_common_salutation` AS `id_salutation`,
    `alms`.`common_physician`.`first_name`           AS `first_name`,
    `alms`.`common_physician`.`last_name`            AS `last_name`,
    `alms`.`common_physician`.`middle_initial`       AS `middle_name`,
    `alms`.`common_physician`.`address_1`            AS `address_1`,
    `alms`.`common_physician`.`address_2`            AS `address_2`,
    `alms`.`common_physician`.`office_phone`         AS `office_phone`,
    `alms`.`common_physician`.`fax`                  AS `fax`,
    `alms`.`common_physician`.`emergency_phone`      AS `emergency_phone`,
    `alms`.`common_physician`.`email`                AS `email`,
    `alms`.`common_physician`.`website_url`          AS `website_url`
FROM `alms`.`common_physician` WHERE `alms`.`common_physician`.`id_common_csz` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_physician` (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `office_phone`,
    `fax`,
    `emergency_phone`,
    `email`,
    `website_url`
) SELECT
    1,
    (`alms`.`common_physician`.`id` + 10000)            AS `id`,
    (`alms`.`common_physician`.`id_common_csz` + 20000) AS `id_csz`,
    `alms`.`common_physician`.`id_common_salutation`    AS `id_salutation`,
    `alms`.`common_physician`.`first_name`              AS `first_name`,
    `alms`.`common_physician`.`last_name`               AS `last_name`,
    `alms`.`common_physician`.`middle_initial`          AS `middle_name`,
    `alms`.`common_physician`.`address_1`               AS `address_1`,
    `alms`.`common_physician`.`address_2`               AS `address_2`,
    `alms`.`common_physician`.`office_phone`            AS `office_phone`,
    `alms`.`common_physician`.`fax`                     AS `fax`,
    `alms`.`common_physician`.`emergency_phone`         AS `emergency_phone`,
    `alms`.`common_physician`.`email`                   AS `email`,
    `alms`.`common_physician`.`website_url`             AS `website_url`
FROM `alms`.`common_physician` WHERE `alms`.`common_physician`.`id_common_csz` >= 2817;

### Responsible Person
INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person` (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `email`,
    `is_financially`,
    `is_emergency`
) SELECT
    1,
    `cc_old`.`responsibleperson`.`Responsible_Person_ID`              AS 'id',
    `cc_old`.`responsibleperson`.`CSZ_ID`                             AS 'id_csz',
    `cc_old`.`people`.`Salutation_ID`                                 AS 'id_salutation',
    `cc_old`.`people`.`First_Name`                                    AS 'first_name',
    `cc_old`.`people`.`Last_name`                                     AS 'last_name',
    `cc_old`.`people`.`Middle_Initial`                                AS 'middle_name',
    `cc_old`.`responsibleperson`.`Address_1`                          AS 'address_1',
    `cc_old`.`responsibleperson`.`Address_2`                          AS 'address_2',
    REPLACE(`cc_old`.`responsibleperson`.`Email`, 'aaa@aaa.aa', NULL) AS 'email',
    IFNULL(`cc_old`.`responsibleperson`.`Financially`, 0)             AS 'is_financially',
    IFNULL(`cc_old`.`responsibleperson`.`Emergency`, 0)               AS 'is_emergency'
FROM `cc_old`.`responsibleperson`
INNER JOIN `cc_old`.`people` ON `cc_old`.`responsibleperson`.`People_ID` = `cc_old`.`people`.`People_ID`
WHERE `cc_old`.`responsibleperson`.`CSZ_ID` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person` (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `email`,
    `is_financially`,
    `is_emergency`
) SELECT
    1,
    `cc_old`.`responsibleperson`.`Responsible_Person_ID`              AS 'id',
    (`cc_old`.`responsibleperson`.`CSZ_ID` + 10000)                   AS 'id_csz',
    `cc_old`.`people`.`Salutation_ID`                                 AS 'id_salutation',
    `cc_old`.`people`.`First_Name`                                    AS 'first_name',
    `cc_old`.`people`.`Last_name`                                     AS 'last_name',
    `cc_old`.`people`.`Middle_Initial`                                AS 'middle_name',
    `cc_old`.`responsibleperson`.`Address_1`                          AS 'address_1',
    `cc_old`.`responsibleperson`.`Address_2`                          AS 'address_2',
    REPLACE(`cc_old`.`responsibleperson`.`Email`, 'aaa@aaa.aa', NULL) AS 'email',
    IFNULL(`cc_old`.`responsibleperson`.`Financially`, 0)             AS 'is_financially',
    IFNULL(`cc_old`.`responsibleperson`.`Emergency`, 0)               AS 'is_emergency'
FROM `cc_old`.`responsibleperson`
INNER JOIN `cc_old`.`people` ON `cc_old`.`responsibleperson`.`People_ID` = `cc_old`.`people`.`People_ID`
WHERE `cc_old`.`responsibleperson`.`CSZ_ID` >= 2817;

INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person` (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `email`,
    `is_financially`,
    `is_emergency`
) SELECT
    1,
    (`alms`.`common_responsible_person`.`id` + 10000)         AS 'id',
    `alms`.`common_responsible_person`.`id_csz`               AS 'id_csz',
    `alms`.`common_responsible_person`.`id_common_salutation` AS 'id_salutation',
    `alms`.`common_responsible_person`.`first_name`           AS 'first_name',
    `alms`.`common_responsible_person`.`last_name`            AS 'last_name',
    `alms`.`common_responsible_person`.`middle_initial`       AS 'middle_name',
    `alms`.`common_responsible_person`.`address_1`            AS 'address_1',
    `alms`.`common_responsible_person`.`address_2`            AS 'address_2',
    `alms`.`common_responsible_person`.`email`                AS 'email',
    `alms`.`common_responsible_person`.`is_financially`       AS 'is_financially',
    `alms`.`common_responsible_person`.`is_emergency`         AS 'is_emergency'
FROM `alms`.`common_responsible_person` WHERE `alms`.`common_responsible_person`.`id_csz` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person` (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `email`,
    `is_financially`,
    `is_emergency`
) SELECT
    1,
    (`alms`.`common_responsible_person`.`id` + 10000)         AS 'id',
    (`alms`.`common_responsible_person`.`id_csz` + 20000)     AS 'id_csz',
    `alms`.`common_responsible_person`.`id_common_salutation` AS 'id_salutation',
    `alms`.`common_responsible_person`.`first_name`           AS 'first_name',
    `alms`.`common_responsible_person`.`last_name`            AS 'last_name',
    `alms`.`common_responsible_person`.`middle_initial`       AS 'middle_name',
    `alms`.`common_responsible_person`.`address_1`            AS 'address_1',
    `alms`.`common_responsible_person`.`address_2`            AS 'address_2',
    `alms`.`common_responsible_person`.`email`                AS 'email',
    `alms`.`common_responsible_person`.`is_financially`       AS 'is_financially',
    `alms`.`common_responsible_person`.`is_emergency`         AS 'is_emergency'
FROM `alms`.`common_responsible_person` WHERE `alms`.`common_responsible_person`.`id_csz` >= 2817;

### Responsible Peron Phone
INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person_phone` (
  `id_responsible_person`,
  `compatibility`,
  `type`,
  `number`,
  `is_primary`,
  `is_sms_enabled`
) SELECT
    `cc_old`.`residentresponsiblepersonphone`.`Responsible_Person_ID`  AS 'id_responsible_person',
    1                                                                  AS 'compatibility',
    `cc_old`.`residentresponsiblepersonphone`.`number_type`            AS 'type',
    `cc_old`.`residentresponsiblepersonphone`.`number`                 AS 'number',
    `cc_old`.`residentresponsiblepersonphone`.`is_primary`             AS 'is_primary',
    0                                                                  AS 'is_sms_enabled'
FROM `cc_old`.`residentresponsiblepersonphone`;

CALL `alms`.`json_row_data`('tmp_responsible_phone_data', 'common_responsible_person', 'phones');
INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person_phone` (
  `id_responsible_person`,
  `compatibility`,
  `type`,
  `number`,
  `extension`,
  `is_primary`,
  `is_sms_enabled`
) SELECT
  (`alms`.`common_responsible_person`.`id` + 10000)                                                                AS 'id_responsible_person',
  IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, '].c')))='US', 1, 2) AS 'compatibility',
     JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, '].t')))             AS 'type',
     JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, '].n')))             AS 'number',
  NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, '].e'))),"")     AS 'extension',
  IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, '].p')))='true',1,0) AS 'is_primary',
  IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, '].s')))='true',1,0) AS 'is_sms_enabled'
FROM `alms`.`common_responsible_person`
  -- Inline table of sequential values to index into JSON array
JOIN (SELECT `alms`.`tmp_responsible_phone_data`.`seq` AS idx FROM `alms`.`tmp_responsible_phone_data`) AS INDEXES
WHERE `alms`.`common_responsible_person`.`phones` IS NOT NULL AND `alms`.`common_responsible_person`.`phones`!='[]'
  AND JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, ']')) IS NOT NULL;


######################################################
### Resident
INSERT INTO `db_seniorcare_migration`.`tbl_resident` (
    `id_space`,
    `id`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `birthday`,
    `gender`
) SELECT
    1,
    `cc_old`.`residents`.`Resident_ID` AS 'id',
    `cc_old`.`people`.`Salutation_ID`  AS 'id_salutation',
    `cc_old`.`people`.`First_Name`     AS 'first_name',
    `cc_old`.`people`.`Last_name`      AS 'last_name',
    `cc_old`.`people`.`Middle_Initial` AS 'middle_name',
    `cc_old`.`residents`.`DOB`         AS 'birthday',
    `cc_old`.`residents`.`Sex`         AS 'gender'
FROM `cc_old`.`residents`
INNER JOIN `cc_old`.`people` ON `cc_old`.`residents`.`People_ID` = `cc_old`.`people`.`People_ID`;

INSERT INTO `db_seniorcare_migration`.`tbl_resident` (
    `id_space`,
    `id`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `birthday`,
    `gender`
) SELECT
    1,
    (`alms`.`base_resident`.`id` + 10000)          AS 'id',
    `alms`.`base_resident`.`id_common_salutation`  AS 'id_salutation',
    `alms`.`base_resident`.`first_name`            AS 'first_name',
    `alms`.`base_resident`.`last_name`             AS 'last_name',
    `alms`.`base_resident`.`middle_initial`        AS 'middle_name',
    `alms`.`base_resident`.`dob`                   AS 'birthday',
    `alms`.`base_resident`.`sex`                   AS 'gender'
FROM `alms`.`base_resident`;

### Resident Phone
INSERT INTO `db_seniorcare_migration`.`tbl_resident_phone` (
  `id_resident`,
  `compatibility`,
  `type`,
  `number`,
  `is_primary`,
  `is_sms_enabled`
) SELECT
    `cc_old`.`residents`.`Resident_ID`  AS 'id_resident',
    1                                   AS 'compatibility',
    1                                   AS 'type',
    `cc_old`.`residents`.`Phone`        AS 'number',
    1                                   AS 'is_primary',
    0                                   AS 'is_sms_enabled'
FROM `cc_old`.`residents` WHERE `cc_old`.`residents`.`Phone` IS NOT NULL;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_phone` (
  `id_resident`,
  `compatibility`,
  `type`,
  `number`,
  `is_primary`,
  `is_sms_enabled`
) SELECT
    `cc_old`.`resident_phone`.`resident_id` AS 'id_resident',
    1                                       AS 'compatibility',
    `cc_old`.`resident_phone`.`type`        AS 'type',
    `cc_old`.`resident_phone`.`number`      AS 'number',
    `cc_old`.`resident_phone`.`is_primary`  AS 'is_primary',
    0                                       AS 'is_sms_enabled'
FROM `cc_old`.`resident_phone`;

CALL `alms`.`json_row_data`('tmp_resident_phone_data', 'base_resident', 'phones');
INSERT INTO `db_seniorcare_migration`.`tbl_resident_phone` (
  `id_resident`,
  `compatibility`,
  `type`,
  `number`,
  `is_primary`,
  `is_sms_enabled`
) SELECT
  (`alms`.`base_resident`.`id` + 10000)                                                                AS 'id_resident',
  IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_resident`.`phones`, CONCAT('$[', idx, '].c')))='US', 1, 2) AS 'compatibility',
     JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_resident`.`phones`, CONCAT('$[', idx, '].t')))             AS 'type',
     JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_resident`.`phones`, CONCAT('$[', idx, '].n')))             AS 'number',
  IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_resident`.`phones`, CONCAT('$[', idx, '].p')))='true',1,0) AS 'is_primary',
  IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_resident`.`phones`, CONCAT('$[', idx, '].s')))='true',1,0) AS 'is_sms_enabled'
FROM `alms`.`base_resident`
  -- Inline table of sequential values to index into JSON array
JOIN (SELECT `alms`.`tmp_resident_phone_data`.`seq` AS idx FROM `alms`.`tmp_resident_phone_data`) AS INDEXES
WHERE `alms`.`base_resident`.`phones` IS NOT NULL AND `alms`.`base_resident`.`phones`!='[]'
  AND JSON_EXTRACT(`alms`.`base_resident`.`phones`, CONCAT('$[', idx, ']')) IS NOT NULL;


# Resident Physician
INSERT INTO `db_seniorcare_migration`.`tbl_resident_physician` (
  `id_resident`,
  `id_physician`,
  `is_primary`
) SELECT
    `cc_old`.`residents`.`Resident_ID`  AS 'id_resident',
    `cc_old`.`residents`.`Physician_ID` AS 'id_physician',
    1                                   AS 'is_primary'
FROM `cc_old`.`residents` WHERE `cc_old`.`residents`.`Physician_ID` IS NOT NULL;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_physician` (
  `id_resident`,
  `id_physician`,
  `is_primary`
) SELECT
    (`alms`.`base_resident`.`id` + 10000)           AS 'id',
    (`alms`.`base_resident`.`id_physician` + 10000) AS 'id_physician',
    1                                               AS 'is_primary'
FROM `alms`.`base_resident` WHERE `alms`.`base_resident`.`id_physician` IS NOT NULL;

# Resident Responsible Person
INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person` (
  `id_resident`,
  `id_responsible_person`,
  `id_relationship`
) SELECT
  `cc_old`.`residentresponsibleperson`.`Resident_ID`           AS 'id_resident',
  `cc_old`.`residentresponsibleperson`.`Responsible_Person_ID` AS 'id_responsible_person',
  `cc_old`.`residentresponsibleperson`.`Relationship_ID`       AS 'id_relationship'
FROM `cc_old`.`residentresponsibleperson`
WHERE `cc_old`.`residentresponsibleperson`.`Relationship_ID` <= 69;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person` (
  `id_resident`,
  `id_responsible_person`,
  `id_relationship`
) SELECT
  `cc_old`.`residentresponsibleperson`.`Resident_ID`             AS 'id_resident',
  `cc_old`.`residentresponsibleperson`.`Responsible_Person_ID`   AS 'id_responsible_person',
  (`cc_old`.`residentresponsibleperson`.`Relationship_ID` + 100) AS 'id_relationship'
FROM `cc_old`.`residentresponsibleperson`
WHERE `cc_old`.`residentresponsibleperson`.`Relationship_ID` >= 70 and `cc_old`.`residentresponsibleperson`.`Relationship_ID` <= 73;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person` (
  `id_resident`,
  `id_responsible_person`,
  `id_relationship`
) SELECT
    (`alms`.`base_resident_responsible_person`.`id_resident` + 10000)           AS 'id_resident',
    (`alms`.`base_resident_responsible_person`.`id_responsible_person` + 10000) AS 'id_physician',
    `alms`.`base_resident_responsible_person`.`id_relationship`                 AS 'id_relationship'
FROM `alms`.`base_resident_responsible_person`
WHERE `alms`.`base_resident_responsible_person`.`id_relationship` <= 69;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person` (
  `id_resident`,
  `id_responsible_person`,
  `id_relationship`
) SELECT
    (`alms`.`base_resident_responsible_person`.`id_resident` + 10000)           AS 'id_resident',
    (`alms`.`base_resident_responsible_person`.`id_responsible_person` + 10000) AS 'id_physician',
    (`alms`.`base_resident_responsible_person`.`id_relationship` + 200)         AS 'id_relationship'
FROM `alms`.`base_resident_responsible_person`
WHERE `alms`.`base_resident_responsible_person`.`id_relationship` >= 70 and `alms`.`base_resident_responsible_person`.`id_relationship` <= 72;


--- Add to end of tbl_resident_responsible_person

UPDATE `db_seniorcare_migration`.`tbl_resident_responsible_person`
   SET `db_seniorcare_migration`.`tbl_resident_responsible_person`.`id_relationship` = 172
 WHERE `db_seniorcare_migration`.`tbl_resident_responsible_person`.`id_relationship` = 270;

UPDATE `db_seniorcare_migration`.`tbl_resident_responsible_person`
   SET `db_seniorcare_migration`.`tbl_resident_responsible_person`.`id_relationship` = 170
 WHERE `db_seniorcare_migration`.`tbl_resident_responsible_person`.`id_relationship` = 272;

DELETE FROM `db_seniorcare_migration`.`tbl_relationship` WHERE `db_seniorcare_migration`.`tbl_relationship`.`id` = 270;
DELETE FROM `db_seniorcare_migration`.`tbl_relationship` WHERE `db_seniorcare_migration`.`tbl_relationship`.`id` = 272;

########################################################
# Facility
INSERT INTO `db_seniorcare_migration`.`tbl_facility` (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
) SELECT
    1,
    `cc_old`.`facilities`.`Facility_ID`    AS 'id',
    `cc_old`.`facilities`.`CSZ_ID`         AS 'id_csz',
    `cc_old`.`facilities`.`Name`           AS 'name',
    NULL                                   AS 'description',
    `cc_old`.`facilities`.`Shorthand`      AS 'shorthand',
    `cc_old`.`facilities`.`Phone`          AS 'phone',
    `cc_old`.`facilities`.`Fax`            AS 'fax',
    `cc_old`.`facilities`.`Street_Address` AS 'address',
    `cc_old`.`facilities`.`License`        AS 'license',
    `cc_old`.`facilities`.`MaxBedsNumber`  AS 'license_capacity',
    `cc_old`.`facilities`.`MaxBedsNumber`  AS 'capacity'
FROM `cc_old`.`facilities` WHERE `facilities`.`CSZ_ID` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_facility` (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
) SELECT
    1,
    `cc_old`.`facilities`.`Facility_ID`      AS 'id',
    (`cc_old`.`facilities`.`CSZ_ID` + 10000) AS 'id_csz',
    `cc_old`.`facilities`.`Name`             AS 'name',
    NULL                                     AS 'description',
    `cc_old`.`facilities`.`Shorthand`        AS 'shorthand',
    `cc_old`.`facilities`.`Phone`            AS 'phone',
    `cc_old`.`facilities`.`Fax`              AS 'fax',
    `cc_old`.`facilities`.`Street_Address`   AS 'address',
    `cc_old`.`facilities`.`License`          AS 'license',
    `cc_old`.`facilities`.`MaxBedsNumber`    AS 'license_capacity',
    `cc_old`.`facilities`.`MaxBedsNumber`    AS 'capacity'
FROM `cc_old`.`facilities` WHERE `cc_old`.`facilities`.`CSZ_ID` >= 2817;

INSERT INTO `db_seniorcare_migration`.`tbl_facility` (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
) SELECT
    1,
    (`alms`.`base_group`.`id` + 10000)        AS 'id',
    `alms`.`base_group`.`id_csz`              AS 'id_csz',
    `alms`.`base_group`.`name`                AS 'name',
    `alms`.`base_group`.`description`         AS 'description',
    `alms`.`base_group`.`shorthand`           AS 'shorthand',
    `alms`.`base_group`.`phone`               AS 'phone',
    `alms`.`base_group`.`fax`                 AS 'fax',
    `alms`.`base_group`.`street_address`      AS 'address',
    `alms`.`base_group`.`license`             AS 'license',
    `alms`.`base_group`.`max_beds_number`     AS 'license_capacity',
    `alms`.`base_group`.`max_beds_number`     AS 'capacity'
FROM `alms`.`base_group` WHERE `alms`.`base_group`.`discriminator` = 'FFC' AND `alms`.`base_group`.`id_csz` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_facility` (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
) SELECT
    1,
    (`alms`.`base_group`.`id` + 10000)        AS 'id',
    (`alms`.`base_group`.`id_csz` + 20000)    AS 'id_csz',
    `alms`.`base_group`.`name`                AS 'name',
    `alms`.`base_group`.`description`         AS 'description',
    `alms`.`base_group`.`shorthand`           AS 'shorthand',
    `alms`.`base_group`.`phone`               AS 'phone',
    `alms`.`base_group`.`fax`                 AS 'fax',
    `alms`.`base_group`.`street_address`      AS 'address',
    `alms`.`base_group`.`license`             AS 'license',
    0                                         AS 'license_capacity',
    `alms`.`base_group`.`max_beds_number`     AS 'capacity'
FROM `alms`.`base_group` WHERE `alms`.`base_group`.`discriminator` = 'FFC' AND `alms`.`base_group`.`id_csz` >= 2817;

# Facility Dining Room
INSERT INTO `db_seniorcare_migration`.`tbl_dining_room` (
    `id`,
    `id_facility`,
    `title`
) SELECT
    `cc_old`.`dinningroom`.`Dinningroom_ID` AS 'id',
    `cc_old`.`dinningroom`.`Facility_ID`    AS 'id_facility',
    `cc_old`.`dinningroom`.`Name`           AS 'title'
FROM `cc_old`.`dinningroom`;

INSERT INTO `db_seniorcare_migration`.`tbl_dining_room` (
    `id`,
    `id_facility`,
    `title`
) SELECT
    (`alms`.`residents_ffc_dining_room`.`id` + 10000)          AS 'id',
    (`alms`.`residents_ffc_dining_room`.`id_facility` + 10000) AS 'id_facility',
    `alms`.`residents_ffc_dining_room`.`title`                 AS 'title'
FROM `alms`.`residents_ffc_dining_room`;


# Facility Room
SELECT
CONCAT('INSERT INTO `db_seniorcare_migration`.`tbl_facility_room` (`db_seniorcare_migration`.`tbl_facility_room`.`id_facility`, `db_seniorcare_migration`.`tbl_facility_room`.`number`, `db_seniorcare_migration`.`tbl_facility_room`.`floor`, `db_seniorcare_migration`.`tbl_facility_room`.`notes`) SELECT ',
`cc_old`.`facilityrooms`.`Facility_ID`,
', \'',
REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$1'),
'\', \'',
`cc_old`.`facilityrooms`.`Floor`,
'\', "',
IFNULL(TRIM(`cc_old`.`facilityrooms`.`Notes`), ''),
'" WHERE NOT EXISTS ( SELECT `id` FROM `db_seniorcare_migration`.`tbl_facility_room` WHERE `db_seniorcare_migration`.`tbl_facility_room`.`id_facility` = ',
    `cc_old`.`facilityrooms`.`Facility_ID`,
    ' AND `db_seniorcare_migration`.`tbl_facility_room`.`number` = \'',
    REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$1'),
'\');' 
) AS 'statement'
FROM `cc_old`.`facilityrooms`  WHERE `cc_old`.`facilityrooms`.`Facility_ID` IS NOT NULL;

INSERT INTO `db_seniorcare_migration`.`tbl_facility_bed` (
    `id`,
    `id_facility_room`,
    `number`
) SELECT
    `cc_old`.`facilityRooms`.`Room_ID`                                                       AS 'id',
    (SELECT `id` FROM `db_seniorcare_migration`.`tbl_facility_room`
     WHERE `db_seniorcare_migration`.`tbl_facility_room`.`id_facility` = `cc_old`.`facilityrooms`.`Facility_ID`
     AND `db_seniorcare_migration`.`tbl_facility_room`.`number` = REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$1'))
                                                                                             AS 'id_facility_room',
    IF(TRIM(REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$2'))='', 'A', TRIM(REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$2')))
                                                                                             AS 'number'
FROM `cc_old`.`facilityRooms` WHERE `cc_old`.`facilityrooms`.`Facility_ID` IS NOT NULL;

--- Review room bed shared/private issues

------
INSERT INTO `db_seniorcare_migration`.`tbl_facility_room` (
    `id`,
    `id_facility`,
    `number`,
    `floor`,
    `notes`
) SELECT
    (`alms`.`base_sub_group`.`id` + 10000)                                            AS 'id',
    (`alms`.`base_sub_group`.`id_group` + 10000)                                      AS 'id_facility',
    REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$1')   AS 'number',
    `alms`.`base_sub_group`.`floor`                                                   AS 'floor',
    `alms`.`base_sub_group`.`notes`                                                   AS 'notes'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'FFC' AND
`alms`.`base_sub_group`.`parent_room_id` IS NULL
/***/ AND `alms`.`base_sub_group`.`id` NOT IN (4, 7, 64, 85, 130, 94, 100, 79, /**p**/133, 135, 8, 9, 65, 66, 80, 81, 86, 87, 95, 96, 101, 102, 131, 132);

INSERT INTO `db_seniorcare_migration`.`tbl_facility_bed` (
    `id`,
    `id_facility_room`,
    `number`
) SELECT
    (`alms`.`base_sub_group`.`id` + 10000)                                            AS 'id',
    (`alms`.`base_sub_group`.`id` + 10000)                                            AS 'id_facility_room',
    REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2')   AS 'number'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'FFC' AND
`alms`.`base_sub_group`.`parent_room_id` IS NULL AND `alms`.`base_sub_group`.`is_shared` = 0
/***/ AND `alms`.`base_sub_group`.`id` NOT IN (4, 7, 64, 85, 130, 94, 100, 79, /**p**/133, 135, 8, 9, 65, 66, 80, 81, 86, 87, 95, 96, 101, 102, 131, 132);

INSERT INTO `db_seniorcare_migration`.`tbl_facility_bed` (
    `id`,
    `id_facility_room`,
    `number`
) SELECT
    (`alms`.`base_sub_group`.`id` + 10000)                                            AS 'id',
    (`alms`.`base_sub_group`.`parent_room_id` + 10000)                                AS 'id_facility_room',
    REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2')   AS 'number'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'FFC' AND
`alms`.`base_sub_group`.`parent_room_id` IS NOT NULL AND `alms`.`base_sub_group`.`is_shared` = 0
/***/ AND `alms`.`base_sub_group`.`id` NOT IN (4, 7, 64, 85, 130, 94, 100, 79, /**p**/133, 135, 8, 9, 65, 66, 80, 81, 86, 87, 95, 96, 101, 102, 131, 132);

# Apartment
INSERT INTO `db_seniorcare_migration`.`tbl_apartment` (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
) SELECT
    1,
    `cc_old`.`il_apartment`.`id`                 AS 'id',
    `cc_old`.`il_apartment`.`CSZ_ID`             AS 'id_csz',
    `cc_old`.`il_apartment`.`name`               AS 'name',
    NULL                                         AS 'description',
    `cc_old`.`il_apartment`.`Shorthand`          AS 'shorthand',
    `cc_old`.`il_apartment`.`Phone`              AS 'phone',
    `cc_old`.`il_apartment`.`Fax`                AS 'fax',
    `cc_old`.`il_apartment`.`Street_Address`     AS 'address',
    `cc_old`.`il_apartment`.`License`            AS 'license',
    0                                            AS 'license_capacity',
    `cc_old`.`il_apartment`.`MaxBedsNumber`      AS 'license_capacity'
FROM `cc_old`.`il_apartment` WHERE `cc_old`.`il_apartment`.`CSZ_ID` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_apartment` (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
) SELECT
    1,
    `cc_old`.`il_apartment`.`id`                 AS 'id',
    (`cc_old`.`il_apartment`.`CSZ_ID` + 10000)   AS 'id_csz',
    `cc_old`.`il_apartment`.`name`               AS 'name',
    NULL                                         AS 'description',
    `cc_old`.`il_apartment`.`Shorthand`          AS 'shorthand',
    `cc_old`.`il_apartment`.`Phone`              AS 'phone',
    `cc_old`.`il_apartment`.`Fax`                AS 'fax',
    `cc_old`.`il_apartment`.`Street_Address`     AS 'address',
    `cc_old`.`il_apartment`.`License`            AS 'license',
    0                                            AS 'license_capacity',
    `cc_old`.`il_apartment`.`MaxBedsNumber`      AS 'license_capacity'
FROM `cc_old`.`il_apartment` WHERE `cc_old`.`il_apartment`.`CSZ_ID` >= 2817;

INSERT INTO `db_seniorcare_migration`.`tbl_apartment` (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
) SELECT
    1,
    (`alms`.`base_group`.`id` + 10000)        AS 'id',
    `alms`.`base_group`.`id_csz`              AS 'id_csz',
    `alms`.`base_group`.`name`                AS 'name',
    `alms`.`base_group`.`description`         AS 'description',
    `alms`.`base_group`.`shorthand`           AS 'shorthand',
    `alms`.`base_group`.`phone`               AS 'phone',
    `alms`.`base_group`.`fax`                 AS 'fax',
    `alms`.`base_group`.`street_address`      AS 'address',
    `alms`.`base_group`.`license`             AS 'license',
    0                                         AS 'license_capacity',
    `alms`.`base_group`.`max_beds_number`     AS 'capacity'
FROM `alms`.`base_group` WHERE `alms`.`base_group`.`discriminator` = 'IL' AND `alms`.`base_group`.`id_csz` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_apartment` (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
) SELECT
    1,
    (`alms`.`base_group`.`id` + 10000)        AS 'id',
    (`alms`.`base_group`.`id_csz` + 20000)    AS 'id_csz',
    `alms`.`base_group`.`name`                AS 'name',
    `alms`.`base_group`.`description`         AS 'description',
    `alms`.`base_group`.`shorthand`           AS 'shorthand',
    `alms`.`base_group`.`phone`               AS 'phone',
    `alms`.`base_group`.`fax`                 AS 'fax',
    `alms`.`base_group`.`street_address`      AS 'address',
    `alms`.`base_group`.`license`             AS 'license',
    0                                         AS 'license_capacity',
    `alms`.`base_group`.`max_beds_number`     AS 'capacity'
FROM `alms`.`base_group` WHERE `alms`.`base_group`.`discriminator` = 'IL' AND `alms`.`base_group`.`id_csz` >= 2817;

# Apartment Room
SELECT
CONCAT('INSERT INTO `db_seniorcare_migration`.`tbl_apartment_room` (`db_seniorcare_migration`.`tbl_apartment_room`.`id_apartment`, `db_seniorcare_migration`.`tbl_apartment_room`.`number`, `db_seniorcare_migration`.`tbl_apartment_room`.`floor`, `db_seniorcare_migration`.`tbl_apartment_room`.`notes`) SELECT ',
`cc_old`.`il_room`.`apartment_id`,
', \'',
REGEXP_REPLACE(`cc_old`.`il_room`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$1'),
'\', \'',
`cc_old`.`il_room`.`Floor`,
'\', "',
IFNULL(TRIM(`cc_old`.`il_room`.`Notes`), ''),
'" WHERE NOT EXISTS ( SELECT `id` FROM `db_seniorcare_migration`.`tbl_apartment_room` WHERE `db_seniorcare_migration`.`tbl_apartment_room`.`id_apartment` = ',
    `cc_old`.`il_room`.`apartment_id`,
    ' AND `db_seniorcare_migration`.`tbl_apartment_room`.`number` = \'',
    REGEXP_REPLACE(`cc_old`.`il_room`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$1'),
'\');' 
) AS 'statement'
FROM `cc_old`.`il_room`  WHERE `cc_old`.`il_room`.`apartment_id` IS NOT NULL;

INSERT INTO `db_seniorcare_migration`.`tbl_apartment_bed` (
    `id`,
    `id_apartment_room`,
    `number`
) SELECT
    `cc_old`.`il_room`.`id`                                                                  AS 'id',
    (SELECT `id` FROM `db_seniorcare_migration`.`tbl_apartment_room`
     WHERE `db_seniorcare_migration`.`tbl_apartment_room`.`id_apartment` = `cc_old`.`il_room`.`apartment_id`
     AND `db_seniorcare_migration`.`tbl_apartment_room`.`number` = REGEXP_REPLACE(`cc_old`.`il_room`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$1'))
                                                                                             AS 'id_apartment_room',
    IF(TRIM(REGEXP_REPLACE(`cc_old`.`il_room`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2'))='', 'A', TRIM(REGEXP_REPLACE(`cc_old`.`il_room`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2')))
                                                                                             AS 'number'
FROM `cc_old`.`il_room` WHERE `cc_old`.`il_room`.`apartment_id` IS NOT NULL;

--- Review room bed shared/private issues
INSERT INTO `db_seniorcare_migration`.`tbl_apartment_room` (
    `id`,
    `id_apartment`,
    `number`,
    `floor`,
    `notes`
) SELECT
    (`alms`.`base_sub_group`.`id` + 10000)                                            AS 'id',
    (`alms`.`base_sub_group`.`id_group` + 10000)                                      AS 'id_apartment',
    REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$1')   AS 'number',
    `alms`.`base_sub_group`.`floor`                                                   AS 'floor',
    `alms`.`base_sub_group`.`notes`                                                   AS 'notes'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'IL' AND
`alms`.`base_sub_group`.`parent_room_id` IS NULL;

INSERT INTO `db_seniorcare_migration`.`tbl_apartment_bed` (
    `id`,
    `id_apartment_room`,
    `number`
) SELECT
    (`alms`.`base_sub_group`.`id` + 10000)                                            AS 'id',
    (`alms`.`base_sub_group`.`id` + 10000)                                            AS 'id_apartment_room',
    REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2')   AS 'number'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'IL' AND
`alms`.`base_sub_group`.`parent_room_id` IS NULL AND `alms`.`base_sub_group`.`is_shared` = 0;

INSERT INTO `db_seniorcare_migration`.`tbl_apartment_bed` (
    `id`,
    `id_apartment_room`,
    `number`
) SELECT
    (`alms`.`base_sub_group`.`id` + 10000)                                            AS 'id',
    (`alms`.`base_sub_group`.`parent_room_id` + 10000)                                AS 'id_apartment_room',
    REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2')   AS 'number'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'IL' AND
`alms`.`base_sub_group`.`parent_room_id` IS NOT NULL AND `alms`.`base_sub_group`.`is_shared` = 0;

# Region
INSERT INTO `db_seniorcare_migration`.`tbl_region` (
    `id_space`,
    `id`,
    `name`,
    `description`,
    `shorthand`
) SELECT
    1,
    `cc_old`.`region`.`id`               AS 'id',
    `cc_old`.`region`.`name`             AS 'name',
    `cc_old`.`region`.`description`      AS 'description',
    ''                                   AS 'shorthand'
FROM `cc_old`.`region`;


INSERT INTO `db_seniorcare_migration`.`tbl_region` (
    `id_space`,
    `id`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`
) SELECT
    1,
    (`alms`.`base_group`.`id` + 10000)        AS 'id',
    `alms`.`base_group`.`name`                AS 'name',
    `alms`.`base_group`.`description`         AS 'description',
    `alms`.`base_group`.`shorthand`           AS 'shorthand',
    `alms`.`base_group`.`phone`               AS 'phone',
    `alms`.`base_group`.`fax`                 AS 'fax'
FROM `alms`.`base_group` WHERE `alms`.`base_group`.`discriminator` = 'IHC';




# Resident Diagnosis
INSERT INTO `db_seniorcare_migration`.`tbl_resident_diagnosis` (
   `id_resident`,
   `id_diagnosis`,
   `type`,
   `notes`
) SELECT
   `cc_old`.`Residentmedcondition`.`Resident_ID`               AS 'id_resident',
   `cc_old`.`Residentmedcondition`.`Medical_Condition_ID`      AS 'id_diagnosis',
   (`cc_old`.`Residentmedcondition`.`Condition_Type` + 1)      AS 'type',
   `cc_old`.`Residentmedcondition`.`Notes`                     AS 'notes'
FROM `cc_old`.`Residentmedcondition`
WHERE `cc_old`.`Residentmedcondition`.`Medical_Condition_ID` <= 2614;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_diagnosis` (
   `id_resident`,
   `id_diagnosis`,
   `type`,
   `notes`
) SELECT
   `cc_old`.`Residentmedcondition`.`Resident_ID`                     AS 'id_resident',
   (`cc_old`.`Residentmedcondition`.`Medical_Condition_ID` + 10000)  AS 'id_diagnosis',
   (`cc_old`.`Residentmedcondition`.`Condition_Type` + 1)            AS 'type',
   `cc_old`.`Residentmedcondition`.`Notes`                           AS 'notes'
FROM `cc_old`.`Residentmedcondition`
WHERE `cc_old`.`Residentmedcondition`.`Medical_Condition_ID` >= 2615;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_diagnosis` (
   `id_resident`,
   `id_diagnosis`,
   `type`,
   `notes`
) SELECT
   (`alms`.`base_resident_diagnosis`.`id_resident` + 10000) AS 'id_resident',
   `alms`.`base_resident_diagnosis`.`id_diagnosis`          AS 'id_diagnosis',
   `alms`.`base_resident_diagnosis`.`id_diagnosis_type`     AS 'type',
   `alms`.`base_resident_diagnosis`.`notes`                 AS 'notes'
FROM `alms`.`base_resident_diagnosis`
WHERE `alms`.`base_resident_diagnosis`.`id_diagnosis` <= 2614;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_diagnosis` (
   `id_resident`,
   `id_diagnosis`,
   `type`,
   `notes`
) SELECT
   (`alms`.`base_resident_diagnosis`.`id_resident` + 10000)  AS 'id_resident',
   (`alms`.`base_resident_diagnosis`.`id_diagnosis` + 20000) AS 'id_diagnosis',
   `alms`.`base_resident_diagnosis`.`id_diagnosis_type`      AS 'type',
   `alms`.`base_resident_diagnosis`.`notes`                  AS 'notes'
FROM `alms`.`base_resident_diagnosis`
WHERE `alms`.`base_resident_diagnosis`.`id_diagnosis` >= 2615;


# Resident Medication Allergy
INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication_allergy` (
    `id_resident`,
    `id_medication`,
    `notes`
) SELECT
    `cc_old`.`drugallergy`.`Resident_ID`     AS 'id_resident',
    `cc_old`.`drugallergy`.`Medication_ID`   AS 'id_medication',
    NULL                                     AS 'notes'
FROM `cc_old`.`drugallergy`
WHERE `cc_old`.`drugallergy`.`Medication_ID` <= 1034;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication_allergy` (
    `id_resident`,
    `id_medication`,
    `notes`
) SELECT
    `cc_old`.`drugallergy`.`Resident_ID`             AS 'id_resident',
    (`cc_old`.`drugallergy`.`Medication_ID` + 10000) AS 'id_medication',
    NULL                                             AS 'notes'
FROM `cc_old`.`drugallergy`
WHERE `cc_old`.`drugallergy`.`Medication_ID` >= 1035;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication_allergy` (
    `id_resident`,
    `id_medication`,
    `notes`
) SELECT
    (`alms`.`base_resident_medication_allergy`.`id_resident` + 10000)  AS 'id_resident',
    `alms`.`base_resident_medication_allergy`.`id_medication`          AS 'id_medication',
    `alms`.`base_resident_medication_allergy`.`notes`                  AS 'notes'
FROM `alms`.`base_resident_medication_allergy`
WHERE `alms`.`base_resident_medication_allergy`.`id_medication` <= 1034;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication_allergy` (
    `id_resident`,
    `id_medication`,
    `notes`
) SELECT
    (`alms`.`base_resident_medication_allergy`.`id_resident` + 10000)    AS 'id_resident',
    (`alms`.`base_resident_medication_allergy`.`id_medication` + 20000)  AS 'id_medication',
    `alms`.`base_resident_medication_allergy`.`notes`                    AS 'notes'
FROM `alms`.`base_resident_medication_allergy`
WHERE `alms`.`base_resident_medication_allergy`.`id_medication` >= 1035;


# Resident Medicatal History Condition
INSERT INTO `db_seniorcare_migration`.`tbl_resident_medical_history_condition` (
    `id_resident`,
    `id_medical_history_condition`,
    `date`,
    `notes`
) SELECT
    `cc_old`.`residentmedhistory`.`Resident_ID`                   AS 'id_resident',
    `cc_old`.`residentmedhistory`.`Medical_History_Condition_ID`  AS 'id_medical_history_condition',
    CONCAT(`cc_old`.`residentmedhistory`.`Med_Date`, ' 00:00:00') AS 'date',
    `cc_old`.`residentmedhistory`.`Notes`                         AS 'notes'
FROM `cc_old`.`residentmedhistory`;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_medical_history_condition` (
    `id_resident`,
    `id_medical_history_condition`,
    `date`,
    `notes`
) SELECT
    (`alms`.`base_resident_medical_history_condition`.`id_resident` + 10000)         AS 'id_resident',
    `alms`.`base_resident_medical_history_condition`.`id_medical_history_condition`  AS 'id_medical_history_condition',
    `alms`.`base_resident_medical_history_condition`.`date`                          AS 'date',
    `alms`.`base_resident_medical_history_condition`.`notes`                         AS 'notes'
FROM `alms`.`base_resident_medical_history_condition`;

# Resident Allergen
INSERT INTO `db_seniorcare_migration`.`tbl_resident_allergen` (
    `id_resident`,
    `id_allergen`,
    `notes`
)
SELECT
    `cc_old`.`residentallergies`.`Resident_ID`         AS 'id_resident',
    `cc_old`.`residentallergies`.`Allergen_ID`         AS 'id_allergen',
    `cc_old`.`residentallergies`.`notes`               AS 'notes'
FROM `cc_old`.`residentallergies`;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_allergen` (
    `id_resident`,
    `id_allergen`,
    `notes`
)
SELECT
    (`alms`.`base_resident_allergen`.`id_resident` + 10000)  AS 'id_resident',
    `alms`.`base_resident_allergen`.`id_allergen`            AS 'id_allergen',
    `alms`.`base_resident_allergen`.`notes`                  AS 'notes'
FROM `alms`.`base_resident_allergen`;

# Resident Diet
INSERT INTO `db_seniorcare_migration`.`tbl_resident_diet` (
    `id_resident`,
    `id_diet`,
    `description`
)
SELECT
    `cc_old`.`dietresidentrestriction`.`Resident_ID`     AS 'id_resident',
    `cc_old`.`dietresidentrestriction`.`Dietcategory_ID` AS 'id_diet',
    `cc_old`.`dietresidentrestriction`.`Name`            AS 'description'
FROM `cc_old`.`dietresidentrestriction`;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_diet` (
    `id_resident`,
    `id_diet`,
    `description`
)
SELECT
    (`alms`.`base_resident_diet`.`id_resident` + 10000)  AS 'id_resident',
    `alms`.`base_resident_diet`.`id_diet`                AS 'id_diet',
    `alms`.`base_resident_diet`.`description`            AS 'description'
FROM `alms`.`base_resident_diet`;

# Resident Medication
INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication` (
    `id_resident`,
    `id_physician`,
    `id_medication`,
    `dosage`,
    `dosage_unit`,
    `prescription_number`,
    `notes`,
    `medication_am`,
    `medication_nn`,
    `medication_pm`,
    `medication_hs`,
    `medication_prn`,
    `medication_discontinued`,
    `medication_treatment`
) SELECT
    `cc_old`.`residentmeds`.`Resident_ID`         AS 'id_resident',
    `cc_old`.`residentmeds`.`Physician_ID`        AS 'id_physician',
    `cc_old`.`residentmeds`.`Medication_ID`       AS 'id_medication',
    IFNULL(`cc_old`.`residentmeds`.`Dosage`, 0)   AS 'dosage',
    `cc_old`.`residentmeds`.`Dosage_Units`        AS 'dosage_unit',
    `cc_old`.`residentmeds`.`Prescription_Number` AS 'prescription_number',
    `cc_old`.`residentmeds`.`Medication_Notes`    AS 'notes',
    `cc_old`.`residentmeds`.`AM`                  AS 'medication_am',
    `cc_old`.`residentmeds`.`NN`                  AS 'medication_nn',
    `cc_old`.`residentmeds`.`PM`                  AS 'medication_pm',
    `cc_old`.`residentmeds`.`HS`                  AS 'medication_hs',
    `cc_old`.`residentmeds`.`PRN`                 AS 'medication_prn',
    `cc_old`.`residentmeds`.`DISC`                AS 'medication_discontinued',
    0                                             AS 'medication_treatment'
FROM `cc_old`.`residentmeds`
WHERE `cc_old`.`residentmeds`.`Medication_ID` <= 1034;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication` (
    `id_resident`,
    `id_physician`,
    `id_medication`,
    `dosage`,
    `dosage_unit`,
    `prescription_number`,
    `notes`,
    `medication_am`,
    `medication_nn`,
    `medication_pm`,
    `medication_hs`,
    `medication_prn`,
    `medication_discontinued`,
    `medication_treatment`
) SELECT
    `cc_old`.`residentmeds`.`Resident_ID`              AS 'id_resident',
    `cc_old`.`residentmeds`.`Physician_ID`             AS 'id_physician',
    (`cc_old`.`residentmeds`.`Medication_ID` + 10000)  AS 'id_medication',
    IFNULL(`cc_old`.`residentmeds`.`Dosage`, 0)        AS 'dosage',
    `cc_old`.`residentmeds`.`Dosage_Units`             AS 'dosage_unit',
    `cc_old`.`residentmeds`.`Prescription_Number`      AS 'prescription_number',
    `cc_old`.`residentmeds`.`Medication_Notes`         AS 'notes',
    `cc_old`.`residentmeds`.`AM`                       AS 'medication_am',
    `cc_old`.`residentmeds`.`NN`                       AS 'medication_nn',
    `cc_old`.`residentmeds`.`PM`                       AS 'medication_pm',
    `cc_old`.`residentmeds`.`HS`                       AS 'medication_hs',
    `cc_old`.`residentmeds`.`PRN`                      AS 'medication_prn',
    `cc_old`.`residentmeds`.`DISC`                     AS 'medication_discontinued',
    0                                                  AS 'medication_treatment'
FROM `cc_old`.`residentmeds`
WHERE `cc_old`.`residentmeds`.`Medication_ID` >= 1035;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication` (
    `id_resident`,
    `id_physician`,
    `id_medication`,
    `dosage`,
    `dosage_unit`,
    `prescription_number`,
    `notes`,
    `medication_am`,
    `medication_nn`,
    `medication_pm`,
    `medication_hs`,
    `medication_prn`,
    `medication_discontinued`,
    `medication_treatment`
) SELECT
    (`alms`.`base_resident_medication`.`id_resident`  + 10000)  AS 'id_resident',
    (`alms`.`base_resident_medication`.`id_physician` + 10000)  AS 'id_physician',
    `alms`.`base_resident_medication`.`id_medication`           AS 'id_medication',
    `alms`.`base_resident_medication`.`dosage`                  AS 'dosage',
    `alms`.`base_resident_medication`.`dosage_unit`             AS 'dosage_unit',
    `alms`.`base_resident_medication`.`prescription_number`     AS 'prescription_number',
    `alms`.`base_resident_medication`.`notes`                   AS 'notes',
    `alms`.`base_resident_medication`.`medication_am`           AS 'medication_am',
    `alms`.`base_resident_medication`.`medication_nn`           AS 'medication_nn',
    `alms`.`base_resident_medication`.`medication_pm`           AS 'medication_pm',
    `alms`.`base_resident_medication`.`medication_hs`           AS 'medication_hs',
    `alms`.`base_resident_medication`.`medication_prn`          AS 'medication_prn',
    `alms`.`base_resident_medication`.`medication_disc`         AS 'medication_discontinued',
    `alms`.`base_resident_medication`.`medication_treatment`    AS 'medication_treatment'
FROM `alms`.`base_resident_medication`
WHERE `alms`.`base_resident_medication`.`id_medication` <= 1034;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication` (
    `id_resident`,
    `id_physician`,
    `id_medication`,
    `dosage`,
    `dosage_unit`,
    `prescription_number`,
    `notes`,
    `medication_am`,
    `medication_nn`,
    `medication_pm`,
    `medication_hs`,
    `medication_prn`,
    `medication_discontinued`,
    `medication_treatment`
) SELECT
    (`alms`.`base_resident_medication`.`id_resident`   + 10000)  AS 'id_resident',
    (`alms`.`base_resident_medication`.`id_physician`  + 10000)  AS 'id_physician',
    (`alms`.`base_resident_medication`.`id_medication` + 20000)  AS 'id_medication',
    `alms`.`base_resident_medication`.`dosage`                   AS 'dosage',
    `alms`.`base_resident_medication`.`dosage_unit`              AS 'dosage_unit',
    `alms`.`base_resident_medication`.`prescription_number`      AS 'prescription_number',
    `alms`.`base_resident_medication`.`notes`                    AS 'notes',
    `alms`.`base_resident_medication`.`medication_am`            AS 'medication_am',
    `alms`.`base_resident_medication`.`medication_nn`            AS 'medication_nn',
    `alms`.`base_resident_medication`.`medication_pm`            AS 'medication_pm',
    `alms`.`base_resident_medication`.`medication_hs`            AS 'medication_hs',
    `alms`.`base_resident_medication`.`medication_prn`           AS 'medication_prn',
    `alms`.`base_resident_medication`.`medication_disc`         AS 'medication_discontinued',
    `alms`.`base_resident_medication`.`medication_treatment`    AS 'medication_treatment'
FROM `alms`.`base_resident_medication`
WHERE `alms`.`base_resident_medication`.`id_medication` >= 1035;





### Assessments 
INSERT INTO `db_seniorcare_migration`.`tbl_assessment_care_level_group` (
    `id_space`,
    `id`,
    `title`
) SELECT
    1,
    `cc_old`.`assessment_care_level_group`.`id`             AS 'id',
    `cc_old`.`assessment_care_level_group`.`level_name`     AS 'title'
FROM `cc_old`.`assessment_care_level_group`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_care_level` (
    `id`,
    `id_care_level_group`,
    `title`,
    `level_low`,
    `level_high`
) SELECT
    `cc_old`.`assessment_care_level`.`id`         AS 'id',
    `cc_old`.`assessment_care_level`.`id_group`   AS 'id_care_level_group',
    `cc_old`.`assessment_care_level`.`level_name` AS 'title',
    `cc_old`.`assessment_care_level`.`level_low`  AS 'level_low',
    `cc_old`.`assessment_care_level`.`level_high` AS 'level_high'
FROM `cc_old`.`assessment_care_level`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_form` (
    `id_space`,
    `id`,
    `title`
) SELECT
    1,
    `cc_old`.`assessment_form`.`id`    AS 'id',
    `cc_old`.`assessment_form`.`title` AS 'title'
FROM `cc_old`.`assessment_form`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_category` (
    `id_space`,
    `id`,
    `title`,
    `multi_item`
) SELECT
    1,
    `cc_old`.`assessment_category`.`id`         AS 'id',
    `cc_old`.`assessment_category`.`title`      AS 'title',
    `cc_old`.`assessment_category`.`multi_item` AS 'multi_item'
FROM `cc_old`.`assessment_category`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_form_category` (
    `id_category`,
    `id_form`,
    `order_number`
) SELECT
    `cc_old`.`assessment_form_category`.`id_category` AS 'id_category',
    `cc_old`.`assessment_form_category`.`id_form`     AS 'id_form',
    0                                                 AS 'order_number'
FROM `cc_old`.`assessment_form_category`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_form_care_level_group` (
    `id_care_level_group`,
    `id_form`
) SELECT
    `cc_old`.`assessment_care_level_groups`.`assessmentcarelevelgroup_id` AS 'id_care_level_group',
    `cc_old`.`assessment_care_level_groups`.`assessmentform_id`           AS 'id_form'
FROM `cc_old`.`assessment_care_level_groups`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_row` (
    `id`,
    `id_category`,
    `title`,
    `score`,
    `order_number`
) SELECT
    `cc_old`.`assessment_row`.`id`          AS 'id',
    `cc_old`.`assessment_row`.`id_category` AS 'id_category',
    `cc_old`.`assessment_row`.`title`       AS 'title',
    `cc_old`.`assessment_row`.`value`       AS 'score',
    `cc_old`.`assessment_row`.`order_no`    AS 'order_number'
FROM `cc_old`.`assessment_row`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment` (
    `id`,
    `id_form`,
    `id_resident`,
    `date`,
    `performed_by`,
    `notes`,
    `score`
) SELECT
    `cc_old`.`assessment`.`id`           AS 'id',
    `cc_old`.`assessment`.`id_form`      AS 'id_form',
    `cc_old`.`assessment`.`id_resident`  AS 'id_resident',
    `cc_old`.`assessment`.`date`         AS 'date',
    `cc_old`.`assessment`.`performed_by` AS 'performed_by',
    `cc_old`.`assessment`.`notes`        AS 'notes',
    `cc_old`.`assessment`.`score`        AS 'score'
FROM `cc_old`.`assessment` WHERE `cc_old`.`assessment`.`discriminator`='r';

CALL `cc_old`.`json_row_data`('tmp_assessment_data', 'assessment', 'data');
INSERT INTO `db_seniorcare_migration`.`tbl_assessment_assessment_row` (
    `id_assessment`,
    `id_row`,
    `score`
) SELECT
    `cc_old`.`assessment`.`id`                                                        AS 'id_assessment',
    JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`assessment`.`data`, CONCAT('$[', idx, ']')))  AS 'id_row',
    (SELECT `cc_old`.`assessment_row`.`value` FROM `cc_old`.`assessment_row`
      WHERE `cc_old`.`assessment_row`.`id` = JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`assessment`.`data`, CONCAT('$[', idx, ']')))) AS 'score'
FROM `cc_old`.`assessment`
JOIN (SELECT `cc_old`.`tmp_assessment_data`.`seq` AS idx FROM `cc_old`.`tmp_assessment_data`) AS INDEXES
WHERE `cc_old`.`assessment`.`discriminator`='r'
  AND `cc_old`.`assessment`.`data` IS NOT NULL
  AND `cc_old`.`assessment`.`data` != '[]'
  AND JSON_EXTRACT(`cc_old`.`assessment`.`data`, CONCAT('$[', idx, ']')) IS NOT NULL;


INSERT INTO `db_seniorcare_migration`.`tbl_assessment` (
    `id`,
    `id_form`,
    `id_resident`,
    `date`,
    `performed_by`,
    `notes`,
    `score`
) SELECT
    (`alms`.`common_assessment_assessment`.`id` + 10000)   AS 'id',
    `alms`.`common_assessment_assessment`.`id_form`        AS 'id_form',
    `alms`.`common_assessment_assessment`.`id_assesstable` AS 'id_resident',
    `alms`.`common_assessment_assessment`.`date`           AS 'date',
    `alms`.`common_assessment_assessment`.`performed_by`   AS 'performed_by',
    `alms`.`common_assessment_assessment`.`notes`          AS 'notes',
    `alms`.`common_assessment_assessment`.`score`          AS 'score'
FROM `alms`.`common_assessment_assessment` WHERE `alms`.`common_assessment_assessment`.`discriminator`='residents';

CALL `alms`.`json_row_data`('tmp_assessment_data', 'common_assessment_assessment', 'data');
INSERT INTO `db_seniorcare_migration`.`tbl_assessment_assessment_row` (
    `id_assessment`,
    `id_row`,
    `score`
) SELECT
    (`alms`.`common_assessment_assessment`.`id` + 10000)                                              AS 'id_assessment',
    JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_assessment_assessment`.`data`, CONCAT('$[', idx, ']')))  AS 'id_row',
    (SELECT `alms`.`common_assessment_row`.`value` FROM `alms`.`common_assessment_row`
      WHERE `alms`.`common_assessment_row`.`id` = JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_assessment_assessment`.`data`, CONCAT('$[', idx, ']')))) AS 'score'
FROM `alms`.`common_assessment_assessment`
JOIN (SELECT `alms`.`tmp_assessment_data`.`seq` AS idx FROM `alms`.`tmp_assessment_data`) AS INDEXES
WHERE `alms`.`common_assessment_assessment`.`discriminator`='residents'
  AND `alms`.`common_assessment_assessment`.`data` IS NOT NULL
  AND `alms`.`common_assessment_assessment`.`data` != '[]'
  AND JSON_EXTRACT(`alms`.`common_assessment_assessment`.`data`, CONCAT('$[', idx, ']')) IS NOT NULL;


### Event Definitions 
INSERT INTO `db_seniorcare_migration`.`tbl_event_definition` (
    `id_space`,
    `id`,
    `title`,
    `show_resident_ffc`,
    `show_resident_ihc`,
    `show_resident_il`,
    `show_physician`,
    `show_responsible_person`,
    `show_additional_date`
) SELECT
    1,
    `cc_old`.`eventdefinition`.`Event_Definition_ID`                                             AS 'id',
    `cc_old`.`eventdefinition`.`Event_Name`                                                      AS 'title',
    `cc_old`.`eventdefinition`.`facility_show`                                                   AS 'show_resident_ffc',
    `cc_old`.`eventdefinition`.`in_home_show`                                                    AS 'show_resident_ihc',
    `cc_old`.`eventdefinition`.`il_show`                                                         AS 'show_resident_il',
    IF(`cc_old`.`eventdefinition`.`Event_Field_Options` LIKE '%"name"%:%"phycisian"%',     1, 0) AS 'show_physician',
    IF(`cc_old`.`eventdefinition`.`Event_Field_Options` LIKE '%"name"%:%"rpPerson"%',      1, 0) AS 'show_responsible_person',
    IF(`cc_old`.`eventdefinition`.`Event_Field_Options` LIKE '%"name"%:%"dischargeDate"%', 1, 0) AS 'show_additional_date'
FROM `cc_old`.`eventdefinition`
WHERE `cc_old`.`eventdefinition`.`Event_Code`     LIKE 'res_%'
  AND `cc_old`.`eventdefinition`.`Event_Code` NOT LIKE 'res_old_'
  AND `cc_old`.`eventdefinition`.`Event_Code` NOT LIKE '%_assignment'
  AND `cc_old`.`eventdefinition`.`Event_Code` NOT LIKE '%_transfer'
  AND `cc_old`.`eventdefinition`.`Event_Code` NOT LIKE '%_admit'
  AND `cc_old`.`eventdefinition`.`Event_Code` NOT LIKE '%_checkout';

### Events
--- fix Sheila record 850 - P - 621
SET @@SESSION.sql_mode='ALLOW_INVALID_DATES';
INSERT INTO `db_seniorcare_migration`.`tbl_resident_event` (
    `id`,
    `id_resident`,
    `id_definition`,
    `notes`,
    `date`,
    `additional_date`,
    `id_physician`,
    `id_responsible_person`
) SELECT
    `cc_old`.`events`.`Event_ID`            AS 'id',
    `cc_old`.`events`.`Resident_ID`         AS 'id_resident',
    `cc_old`.`events`.`Event_Definition_ID` AS 'id_definition',
    `cc_old`.`events`.`Event_Notes`         AS 'notes',
    `cc_old`.`events`.`Event_Date`          AS 'date',
    IF(JSON_VALID(`cc_old`.`events`.`Event_Data`), STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`events`.`Event_Data`, '$.fields.dischargeDate')), '%m/%d/%Y'), NULL) AS 'additional_date',
    IF(JSON_VALID(`cc_old`.`events`.`Event_Data`), CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`events`.`Event_Data`, '$.fields.phycisian.id')), SIGNED INTEGER), NULL)  AS 'id_physician',
    IF(JSON_VALID(`cc_old`.`events`.`Event_Data`), CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`events`.`Event_Data`, '$.fields.rpPerson.id')), SIGNED INTEGER), NULL)   AS 'id_responsible_person'
FROM `cc_old`.`events`
WHERE `cc_old`.`events`.`Resident_ID` IS NOT NULL
AND `cc_old`.`events`.`Event_Definition_ID` IN (SELECT `id` FROM `db_seniorcare_migration`.`tbl_event_definition`)
;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_event` (
    `id`,
    `id_resident`,
    `id_definition`,
    `notes`,
    `date`,
    `additional_date`,
    `id_physician`,
    `id_responsible_person`
) SELECT
    (`alms`.`base_event`.`id` + 10000)           AS 'id',
    (`alms`.`base_event`.`id_resident` + 10000)  AS 'id_resident',
    `alms`.`base_event`.`id_definition`          AS 'id_definition',
    `alms`.`base_event`.`notes`                  AS 'notes',
    `alms`.`base_event`.`date`                   AS 'date',
    IF(JSON_VALID(`alms`.`base_event`.`data`), STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_event`.`data`, '$.fields.dischargeDate')), '%m/%d/%Y'), NULL) AS 'additional_date',
    IF(JSON_VALID(`alms`.`base_event`.`data`), CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_event`.`data`, '$.fields.phycisian.id')), SIGNED INTEGER), NULL)  AS 'id_physician',
    IF(JSON_VALID(`alms`.`base_event`.`data`), CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_event`.`data`, '$.fields.rpPerson.id')), SIGNED INTEGER), NULL)   AS 'id_responsible_person'
FROM `alms`.`base_event`
WHERE `alms`.`base_event`.`id_resident` IS NOT NULL
AND `alms`.`base_event`.`id_definition` IN (SELECT `id` FROM `db_seniorcare_migration`.`tbl_event_definition`)
;

### Rent
INSERT INTO `db_seniorcare_migration`.`tbl_resident_rent` (
    `id`,
    `id_resident`,
    `rent_period`,
    `start`,
    `end`,
    `amount`,
    `notes`,
    `source`
) SELECT
    `cc_old`.`contract`.`id`              AS 'id',
    `cc_old`.`contract`.`owner`           AS 'id_resident',
    `cc_old`.`contract`.`type`            AS 'rent_period',
    `cc_old`.`contract`.`start`           AS 'start',
    `cc_old`.`contract`.`end`             AS 'end',
    `cc_old`.`contract`.`amount`          AS 'amount',
    `cc_old`.`contract`.`note`            AS 'notes',
    `cc_old`.`contract`.`source`          AS 'source'
FROM `cc_old`.`contract`
INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`contract`.`owner`
WHERE `cc_old`.`contract`.`owner_type` = 1;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_rent` (
    `id`,
    `id_resident`,
    `rent_period`,
    `start`,
    `end`,
    `amount`,
    `notes`,
    `source`
) SELECT
    (`alms`.`contract`.`id` + 10000)          AS 'id',
    (`alms`.`contract`.`id_resident` + 10000) AS 'id_resident',
    `alms`.`contract`.`type`                  AS 'rent_period',
    `alms`.`contract`.`start`                 AS 'start',
    `alms`.`contract`.`end`                   AS 'end',
    `alms`.`contract`.`amount`                AS 'amount',
    `alms`.`contract`.`note`                  AS 'notes',
    `alms`.`contract`.`source`                AS 'source'
FROM `alms`.`contract`;

### Hosting/Action Event
INSERT INTO `db_seniorcare_migration`.`tbl_contract` (
    `id`,
    `id_resident`,
    `start`,
    `end`,
    `type`
) SELECT
    `cc_old`.`residents`.`Resident_ID`      AS 'id',
    `cc_old`.`residents`.`Resident_ID`      AS 'id_resident',
    `cc_old`.`residents`.`Date_Admitted`    AS 'start',
    `cc_old`.`residents`.`Date_Left`        AS 'end',
    CASE
        WHEN `cc_old`.`residents`.`type` = 'r'    THEN 1
        WHEN `cc_old`.`residents`.`type` = 'il'   THEN 2
        WHEN `cc_old`.`residents`.`type` = 'rihc' THEN 3
    END                                     AS 'type'
FROM `cc_old`.`residents`;

INSERT INTO `db_seniorcare_migration`.`tbl_contract_facility_option` (
    `id_contract`,
    `id_dining_room`,
    `id_facility_bed`,
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
    `state`
) SELECT
    `cc_old`.`residents`.`Resident_ID`      AS 'id_contract',
    IF(`cc_old`.`residents`.`Dinningroom_ID`=0, NULL, `cc_old`.`residents`.`Dinningroom_ID`)
                                            AS 'id_dining_room',
    `cc_old`.`residents`.`Room_ID`          AS 'id_facility_bed',
    `cc_old`.`residents`.`Care_Level_ID`    AS 'id_care_level',
    `cc_old`.`residents`.`Care_Group`       AS 'care_group',
    `cc_old`.`residents`.`DNR`              AS 'dnr',
    `cc_old`.`residents`.`POLST`            AS 'polst',
    `cc_old`.`residents`.`Ambulatory`       AS 'ambulatory',
    CASE
        WHEN `cc_old`.`residents`.`State_ID` = 1    THEN 1
        WHEN `cc_old`.`residents`.`State_ID` = 2    THEN 3
    END                                     AS 'state'
FROM `cc_old`.`residents` WHERE `cc_old`.`residents`.`type` = 'r';

INSERT INTO `db_seniorcare_migration`.`tbl_contract_apartment_option` (
    `id_contract`,
    `id_apartment_bed`,
    `state`
) SELECT
    `cc_old`.`residents`.`Resident_ID`      AS 'id_contract',
    `cc_old`.`residents`.`il_room_id`       AS 'id_apartment_bed',
    CASE
        WHEN `cc_old`.`residents`.`State_ID` = 1    THEN 1
        WHEN `cc_old`.`residents`.`State_ID` = 2    THEN 3
    END                                     AS 'state'
FROM `cc_old`.`residents` WHERE `cc_old`.`residents`.`type` = 'il';

INSERT INTO `db_seniorcare_migration`.`tbl_contract_region_option` (
    `id_contract`,
    `id_region`,
    `id_csz`,
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
    `address`,
    `state`
)SELECT
    `cc_old`.`residents`.`Resident_ID`               AS 'id_contract',
    `cc_old`.`residents`.`region_id`                 AS 'id_region',
    `cc_old`.`residents`.`csz_id`                    AS 'id_csz',
    `cc_old`.`residents`.`Care_Level_ID`             AS 'id_care_level',
    `cc_old`.`residents`.`Care_Group`                AS 'care_group',
    `cc_old`.`residents`.`DNR`                       AS 'dnr',
    `cc_old`.`residents`.`POLST`                     AS 'polst',
    `cc_old`.`residents`.`Ambulatory`                AS 'ambulatory',
    IFNULL(`cc_old`.`residents`.`street_address`,'') AS 'address',
    CASE
        WHEN `cc_old`.`residents`.`State_ID` = 1    THEN 1
        WHEN `cc_old`.`residents`.`State_ID` = 2    THEN 3
    END                                     AS 'state'
FROM `cc_old`.`residents` WHERE `cc_old`.`residents`.`type` = 'rihc' AND `cc_old`.`residents`.`csz_id` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_contract_region_option` (
    `id_contract`,
    `id_region`,
    `id_csz`,
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
    `address`,
    `state`
)SELECT
    `cc_old`.`residents`.`Resident_ID`               AS 'id_contract',
    `cc_old`.`residents`.`region_id`                 AS 'id_region',
    `cc_old`.`residents`.`csz_id`                    AS 'id_csz',
    `cc_old`.`residents`.`Care_Level_ID`             AS 'id_care_level',
    `cc_old`.`residents`.`Care_Group`                AS 'care_group',
    `cc_old`.`residents`.`DNR`                       AS 'dnr',
    `cc_old`.`residents`.`POLST`                     AS 'polst',
    `cc_old`.`residents`.`Ambulatory`                AS 'ambulatory',
    IFNULL(`cc_old`.`residents`.`street_address`,'') AS 'address',
    CASE
        WHEN `cc_old`.`residents`.`State_ID` = 1    THEN 1
        WHEN `cc_old`.`residents`.`State_ID` = 2    THEN 3
    END                                     AS 'state'
FROM `cc_old`.`residents` WHERE `cc_old`.`residents`.`type` = 'rihc' AND `cc_old`.`residents`.`csz_id` >= 2817;
------------------------------------------------------------------------
INSERT INTO `db_seniorcare_migration`.`tbl_contract` (
    `id`,
    `id_resident`,
    `start`,
    `end`,
    `type`
)SELECT
    (`alms`.`base_resident`.`id` + 10000)  AS 'id',
    (`alms`.`base_resident`.`id` + 10000)  AS 'id_resident',
    `alms`.`base_resident`.`date_admitted` AS 'start',
    `alms`.`base_resident`.`date_left`     AS 'end',
    CASE
        WHEN `alms`.`base_resident`.`discriminator` = 'FFC'    THEN 1
        WHEN `alms`.`base_resident`.`discriminator` = 'IL'     THEN 2
        WHEN `alms`.`base_resident`.`discriminator` = 'IHC'    THEN 3
    END                                    AS 'type'
FROM `alms`.`base_resident`;

INSERT INTO `db_seniorcare_migration`.`tbl_contract_facility_option` (
    `id_contract`,
    `id_dining_room`,
    `id_facility_bed`,
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
    `state`
) SELECT
    (`alms`.`base_resident`.`id` + 10000)             AS 'id_contract',
    (`alms`.`base_resident`.`id_dining_room` + 10000) AS 'id_dining_room',
    (`alms`.`base_resident`.`id_sub_group` + 10000)   AS 'id_facility_bed',
    `alms`.`base_resident`.`id_care_level`            AS 'id_care_level',
    `alms`.`base_resident`.`care_group`               AS 'care_group',
    `alms`.`base_resident`.`dnr`                      AS 'dnr',
    `alms`.`base_resident`.`polst`                    AS 'polst',
    `alms`.`base_resident`.`ambulatory`               AS 'ambulatory',
    CASE
        WHEN `alms`.`base_resident`.`state` = 1    THEN 1
        WHEN `alms`.`base_resident`.`state` = 2    THEN 3
    END                                               AS 'state'
FROM `alms`.`base_resident` WHERE `alms`.`base_resident`.`discriminator` = 'FFC'
/***/ AND `alms`.`base_resident`.`id_sub_group` NOT IN (4, 7, 64, 85, 130, 94, 100, 79, /**p**/133, 135, 8, 9, 65, 66, 80, 81, 86, 87, 95, 96, 101, 102, 131, 132);

INSERT INTO `db_seniorcare_migration`.`tbl_contract_apartment_option` (
    `id_contract`,
    `id_apartment_bed`,
    `state`
) SELECT
    (`alms`.`base_resident`.`id` + 10000)            AS 'id_contract',
    (`alms`.`base_resident`.`id_sub_group` + 10000)  AS 'id_apartment_bed',
    CASE
        WHEN `alms`.`base_resident`.`state` = 1    THEN 1
        WHEN `alms`.`base_resident`.`state` = 2    THEN 3
    END                                     AS 'state'
FROM `alms`.`base_resident` WHERE `alms`.`base_resident`.`discriminator` = 'IL';

INSERT INTO `db_seniorcare_migration`.`tbl_contract_region_option` (
    `id_contract`,
    `id_region`,
    `id_csz`,
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
    `address`,
    `state`
) SELECT
    (`alms`.`base_resident`.`id`       + 10000) AS 'id_contract',
    (`alms`.`base_resident`.`id_group` + 10000) AS 'id_region',
    `alms`.`base_resident`.`id_csz`             AS 'id_csz',
    `alms`.`base_resident`.`id_care_level`      AS 'id_care_level',
    `alms`.`base_resident`.`care_group`         AS 'care_group',
    `alms`.`base_resident`.`dnr`                AS 'dnr',
    `alms`.`base_resident`.`polst`              AS 'polst',
    `alms`.`base_resident`.`ambulatory`         AS 'ambulatory',
    `alms`.`base_resident`.`address`            AS 'address',
    CASE
        WHEN `alms`.`base_resident`.`state` = 1    THEN 1
        WHEN `alms`.`base_resident`.`state` = 2    THEN 3
    END                                     AS 'state'
FROM `alms`.`base_resident` WHERE `alms`.`base_resident`.`discriminator` = 'IHC' AND `alms`.`base_resident`.`id_csz` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_contract_region_option` (
    `id_contract`,
    `id_region`,
    `id_csz`,
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
    `address`,
    `state`
) SELECT
    (`alms`.`base_resident`.`id`       + 10000) AS 'id_contract',
    (`alms`.`base_resident`.`id_group` + 10000) AS 'id_region',
    (`alms`.`base_resident`.`id_csz`   + 20000) AS 'id_csz',
    `alms`.`base_resident`.`id_care_level`      AS 'id_care_level',
    `alms`.`base_resident`.`care_group`         AS 'care_group',
    `alms`.`base_resident`.`dnr`                AS 'dnr',
    `alms`.`base_resident`.`polst`              AS 'polst',
    `alms`.`base_resident`.`ambulatory`         AS 'ambulatory',
    `alms`.`base_resident`.`address`            AS 'address',
    CASE
        WHEN `alms`.`base_resident`.`state` = 1    THEN 1
        WHEN `alms`.`base_resident`.`state` = 2    THEN 3
    END                                     AS 'state'
FROM `alms`.`base_resident` WHERE `alms`.`base_resident`.`discriminator` = 'IHC' AND `alms`.`base_resident`.`id_csz` >= 2817;

# Hosting
INSERT INTO `db_seniorcare_migration`.`tbl_contract_action` (
    `id_contract`,
    `start`,
    `end`,
    
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,

    `id_facility_bed`,
  
    `state`
)SELECT
    `cc_old`.`hosting`.`owner`              AS 'id_contract',
    `cc_old`.`hosting`.`start`              AS 'start',
    `cc_old`.`hosting`.`end`                AS 'end',
  
    `cc_old`.`residents`.`Care_Level_ID`    AS 'id_care_level',
    `cc_old`.`residents`.`Care_Group`       AS 'care_group',
    `cc_old`.`residents`.`DNR`              AS 'dnr',
    `cc_old`.`residents`.`POLST`            AS 'polst',
    `cc_old`.`residents`.`Ambulatory`       AS 'ambulatory',
  
    `cc_old`.`hosting`.`object`             AS 'id_facility_bed',

    1                                       AS 'state'
FROM `cc_old`.`hosting`
INNER JOIN `cc_old`.`residents`    ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`hosting`.`owner`
INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`hosting`.`object`
WHERE `cc_old`.`hosting`.`owner_type` = 1 AND `cc_old`.`hosting`.`object`;

INSERT INTO `db_seniorcare_migration`.`tbl_contract_action` (
    `id_contract`,
    `start`,
    `end`,

    `id_apartment_bed`,

    `state`
) SELECT
    `cc_old`.`hosting`.`owner`              AS 'id_contract',
    `cc_old`.`hosting`.`start`              AS 'start',
    `cc_old`.`hosting`.`end`                AS 'end',

    `cc_old`.`hosting`.`object`             AS 'id_apartment_bed',

    1                                       AS 'state'
FROM `cc_old`.`hosting` WHERE `cc_old`.`hosting`.`owner_type` = 3;

INSERT INTO `db_seniorcare_migration`.`tbl_contract_action` (
    `id_contract`,
    `start`,
    `end`,
    
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
  
    `id_csz`,
    `address`,
  
    `id_region`,
  
    `state`
) SELECT
    `cc_old`.`hosting`.`owner`              AS 'id_contract',
    `cc_old`.`hosting`.`start`              AS 'start',
    `cc_old`.`hosting`.`end`                AS 'end',
  
    `cc_old`.`residents`.`Care_Level_ID`    AS 'id_care_level',
    `cc_old`.`residents`.`Care_Group`       AS 'care_group',
    `cc_old`.`residents`.`DNR`              AS 'dnr',
    `cc_old`.`residents`.`POLST`            AS 'polst',
    `cc_old`.`residents`.`Ambulatory`       AS 'ambulatory',
  
    `cc_old`.`residents`.`csz_id`           AS 'id_csz',
    `cc_old`.`residents`.`street_address`   AS 'address',
  
    `cc_old`.`hosting`.`object_group`       AS 'id_region',

    1                                       AS 'state'
FROM `cc_old`.`hosting`
INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`hosting`.`owner`
WHERE `cc_old`.`hosting`.`owner_type` = 2 AND `cc_old`.`residents`.`csz_id` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_contract_action` (
    `id_contract`,
    `start`,
    `end`,
    
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
  
    `id_csz`,
    `address`,
  
    `id_region`,
  
    `state`
) SELECT
    `cc_old`.`hosting`.`owner`              AS 'id_contract',
    `cc_old`.`hosting`.`start`              AS 'start',
    `cc_old`.`hosting`.`end`                AS 'end',
  
    `cc_old`.`residents`.`Care_Level_ID`    AS 'id_care_level',
    `cc_old`.`residents`.`Care_Group`       AS 'care_group',
    `cc_old`.`residents`.`DNR`              AS 'dnr',
    `cc_old`.`residents`.`POLST`            AS 'polst',
    `cc_old`.`residents`.`Ambulatory`       AS 'ambulatory',
  
    (`cc_old`.`residents`.`csz_id` + 10000) AS 'id_csz',
    `cc_old`.`residents`.`street_address`   AS 'address',
  
    `cc_old`.`hosting`.`object_group`       AS 'id_region',

    1                                       AS 'state'
FROM `cc_old`.`hosting`
INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`hosting`.`owner`
WHERE `cc_old`.`hosting`.`owner_type` = 2 AND `cc_old`.`residents`.`csz_id` >= 2817;

----
INSERT INTO `db_seniorcare_migration`.`tbl_contract_action` (
    `id_contract`,
    `start`,
    `end`,
    
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
  
    `id_facility_bed`,
  
    `state`
) SELECT
    (`alms`.`hosting`.`id_resident` + 10000)           AS 'id_contract',
    `alms`.`hosting`.`start`                           AS 'start',
    `alms`.`hosting`.`end`                             AS 'end',
  
    `alms`.`base_resident`.`id_care_level`             AS 'id_care_level',
    `alms`.`base_resident`.`care_group`                AS 'care_group',
    `alms`.`base_resident`.`dnr`                       AS 'dnr',
    `alms`.`base_resident`.`polst`                     AS 'polst',
    `alms`.`base_resident`.`ambulatory`                AS 'ambulatory',
  
    (`alms`.`hosting`.`id_sub_group` + 10000)          AS 'id_facility_bed',

    1                                                  AS 'state'
FROM
  `alms`.`hosting`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id` = `alms`.`hosting`.`id_resident`
WHERE `alms`.`base_resident`.`discriminator` = 'FFC'
/***/ AND `alms`.`hosting`.`id_sub_group` NOT IN (4, 7, 64, 85, 130, 94, 100, 79, /**p**/133, 135, 8, 9, 65, 66, 80, 81, 86, 87, 95, 96, 101, 102, 131, 132);;


INSERT INTO `db_seniorcare_migration`.`tbl_contract_action` (
    `id_contract`,
    `start`,
    `end`,
  
    `id_apartment_bed`,
  
    `state`
) SELECT
    (`alms`.`hosting`.`id_resident` + 10000)           AS 'id_contract',
    `alms`.`hosting`.`start`                           AS 'start',
    `alms`.`hosting`.`end`                             AS 'end',
  
    (`alms`.`hosting`.`id_sub_group` + 10000)          AS 'id_apartment_bed',

    1                                                  AS 'state'
FROM
  `alms`.`hosting`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id` = `alms`.`hosting`.`id_resident`
WHERE `alms`.`base_resident`.`discriminator` = 'IL';

INSERT INTO `db_seniorcare_migration`.`tbl_contract_action` (
    `id_contract`,
    `start`,
    `end`,
    
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
  
    `id_csz`,
    `address`,
  
    `id_region`,
  
    `state`
) SELECT
    (`alms`.`hosting`.`id_resident` + 10000)           AS 'id_contract',
    `alms`.`hosting`.`start`                           AS 'start',
    `alms`.`hosting`.`end`                             AS 'end',
  
    `alms`.`base_resident`.`id_care_level`   AS 'id_care_level',
    `alms`.`base_resident`.`care_group`      AS 'care_group',
    `alms`.`base_resident`.`dnr`             AS 'dnr',
    `alms`.`base_resident`.`polst`           AS 'polst',
    `alms`.`base_resident`.`ambulatory`      AS 'ambulatory',
  
    `alms`.`base_resident`.`id_csz`          AS 'id_csz',
    `alms`.`base_resident`.`address`         AS 'address',
  
    (`alms`.`hosting`.`id_group` + 10000)              AS 'id_region',

    1                                                  AS 'state'
FROM
  `alms`.`hosting`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id` = `alms`.`hosting`.`id_resident`
WHERE `alms`.`base_resident`.`discriminator` = 'IHC' AND `alms`.`base_resident`.`id_csz` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_contract_action` (
    `id_contract`,
    `start`,
    `end`,
    
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
  
    `id_csz`,
    `address`,
  
    `id_region`,
  
    `state`
) SELECT
    (`alms`.`hosting`.`id_resident` + 10000)            AS 'id_contract',
    `alms`.`hosting`.`start`                            AS 'start',
    `alms`.`hosting`.`end`                              AS 'end',
   
    `alms`.`base_resident`.`id_care_level`              AS 'id_care_level',
    `alms`.`base_resident`.`care_group`                 AS 'care_group',
    `alms`.`base_resident`.`dnr`                        AS 'dnr',
    `alms`.`base_resident`.`polst`                      AS 'polst',
    `alms`.`base_resident`.`ambulatory`                 AS 'ambulatory',
            
    (`alms`.`base_resident`.`id_csz` + 10000)           AS 'id_csz',
    `alms`.`base_resident`.`address`                    AS 'address',
  
    (`alms`.`hosting`.`id_group` + 10000)               AS 'id_region',

    1                                                   AS 'state'
FROM
  `alms`.`hosting`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id` = `alms`.`hosting`.`id_resident`
WHERE `alms`.`base_resident`.`discriminator` = 'IHC' AND `alms`.`base_resident`.`id_csz` >= 2817;

UPDATE `db_name`.`tbl_contract_action` AS `ca`
    SET   `ca`.`state` = 3
    WHERE `ca`.`end` IS NOT NULL AND
          `ca`.`id` =
    (SELECT `mm`.`max_id` FROM (SELECT MAX(`cam`.`id`) AS `max_id` FROM `db_name`.`tbl_contract_action` AS `cam` WHERE `cam`.`id_contract` = `ca`.`id_contract`) AS `mm`);


### Resident Photo
SELECT
    `cc_old`.`residents`.`Resident_ID`                                                       AS 'id',
    CONCAT('https://ccdb.ciminocare.com/uploads/documents/', `cc_old`.`residents`.`Photo`)   AS 'photo' 
FROM `cc_old`.`residents` WHERE `cc_old`.`residents`.`Photo`!=''
UNION
SELECT
    (`alms`.`base_resident`.`id` + 10000)                                                    AS 'id',
    CONCAT('https://alms.ciminocare.com/uploads/documents/', `alms`.`base_resident`.`photo`) AS 'photo' 
FROM `alms`.`base_resident` WHERE `alms`.`base_resident`.`photo`!='';

# Use app:migrate:photos command to import these photos to SeniorCare.

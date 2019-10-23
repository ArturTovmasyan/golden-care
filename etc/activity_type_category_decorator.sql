DROP FUNCTION IF EXISTS `udf_ActivityTypeCategoryDecorator`;
DELIMITER ;;
CREATE FUNCTION `udf_ActivityTypeCategoryDecorator`(input VARCHAR(4000)) RETURNS JSON
  DETERMINISTIC
BEGIN
  DECLARE category JSON DEFAULT '[]';
  DECLARE category_value INT;
  DECLARE i INT DEFAULT 0;

  WHILE i < JSON_LENGTH(input) DO
  SELECT JSON_EXTRACT(input, CONCAT('$[', i, ']')) INTO category_value;

  IF category_value IS NOT NULL THEN
    SELECT JSON_ARRAY_APPEND(category, '$',
      CASE
        WHEN category_value = 1 THEN 'Lead'
        WHEN category_value = 2 THEN 'Referral'
        WHEN category_value = 3 THEN 'Organization'
        WHEN category_value = 4 THEN 'Outreach'
        WHEN category_value = 5 THEN 'Contact'
        ELSE ''
      END) INTO category;
  END IF;

  SELECT i + 1 INTO i;
  END WHILE;

  RETURN category;
END
;;
DELIMITER ;
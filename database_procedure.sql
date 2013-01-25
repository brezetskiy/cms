
DELIMITER $$
--
-- Процедуры
--
CREATE DEFINER=`cformat_cms`@`%` PROCEDURE `admin_auth`(IN _login CHAR(30), IN _passwd CHAR(32), IN _ip CHAR(15), IN _local_ip CHAR(15), OUT _user_id INTEGER(10))
    SQL SECURITY INVOKER
BEGIN
	 DECLARE test, user_id, group_id, priv_ip INT DEFAULT 0;
	 DECLARE order_ip, passwd CHAR(32) DEFAULT 'deny_all';
	
     SELECT
         tb_user.id,
         tb_user.group_id,
         tb_group.order_ip,
         tb_user.passwd
     INTO
         @user_id,
         @group_id,
         @order_ip,
         @passwd
     FROM admin_auth_user AS tb_user
     INNER JOIN admin_auth_group AS tb_group ON tb_group.id=tb_user.group_id
     WHERE
          tb_user.login=_login
          AND tb_user.passwd=MD5(_passwd)
          AND tb_user.active='true'
          AND tb_group.active='true';
	
	
     SELECT COUNT(*) INTO @priv_ip
	 FROM admin_auth_group_priv_ip
	 WHERE
          group_id = @group_id
          AND (
	 	  ( INET_ATON(_ip) >= ip_from AND INET_ATON(_ip) <= ip_to )
	 	  OR
	 	  ( INET_ATON(_local_ip) >= ip_from AND INET_ATON(_local_ip) <= ip_to)
        );
		
     IF @order_ip = 'allow_all' AND @priv_ip > 0 THEN
        SET @user_id=0;
     ELSEIF @order_ip = 'deny_all' AND @priv_ip = 0 THEN
        SET @user_id=0;
     END IF;
     SET _user_id = @user_id;
END$$

CREATE DEFINER=`cformat_cms`@`%` PROCEDURE `build_relation`(IN table_name VARCHAR(64), IN parent_field VARCHAR(64), IN relation_table VARCHAR(64), OUT fetch_rows INTEGER(11))
    SQL SECURITY INVOKER
BEGIN
    DECLARE
           who_has_parents,
           first_level_update,
           copy_existent_relations,
           insert_new_relation
           TEXT DEFAULT '';
    DECLARE done, var_id, var_parent_id INT DEFAULT 0;
    DECLARE has_parents CURSOR FOR SELECT * FROM tmp_relation_update;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    
	SET @who_has_parents = CONCAT("
	    CREATE TEMPORARY TABLE `tmp_relation_update`
        SELECT tb_structure.id, tb_structure.`",parent_field,"`
        FROM `",table_name,"` AS tb_structure
		INNER JOIN `", relation_table, "` AS tb_parent_relation
              ON tb_parent_relation.id = tb_structure.`",parent_field,"`
		LEFT JOIN `", relation_table, "` AS tb_relation
             ON tb_relation.id = tb_structure.id
		WHERE tb_relation.id IS NULL
    ");
	SET @first_level_update = CONCAT("
        REPLACE INTO `",relation_table,"` (`id`, `parent`, `priority`)
        SELECT tb_structure.id, tb_structure.id AS parent, 1 AS priority
        FROM `",table_name,"` AS tb_structure
    	LEFT JOIN `",relation_table,"` AS tb_relation
             ON tb_relation.id=tb_structure.id
 	    WHERE
             tb_structure.`",parent_field,"` = 0
             AND tb_relation.id IS NULL;
    ");
    SET @copy_existent_relations = CONCAT("
        INSERT IGNORE INTO `",relation_table,"` (`id`, `parent`, `priority`)
        SELECT ?, parent, priority
        FROM `",relation_table,"` AS tb_relation
        WHERE id = ?
        ORDER BY priority ASC
    ");
    SET @insert_new_relation = CONCAT("
		INSERT IGNORE INTO `",relation_table,"` (`id`, `parent`, `priority`)
		SELECT ?, ?, MAX(priority) + 1
		FROM `",relation_table,"`
		WHERE id = ?;
    ");
    
    PREPARE who_has_parents FROM @who_has_parents;
    PREPARE first_level_update FROM @first_level_update;
    PREPARE copy_existent_relations FROM @copy_existent_relations;
    PREPARE insert_new_relation FROM @insert_new_relation;
    
    EXECUTE first_level_update;
    EXECUTE who_has_parents;
    OPEN has_parents;
	SELECT FOUND_ROWS() INTO fetch_rows;
	REPEAT
		FETCH has_parents INTO var_id, var_parent_id;
		IF NOT done THEN
            SET @var_id = var_id;
            SET @var_parent_id = var_parent_id;
			EXECUTE copy_existent_relations USING @var_id, @var_parent_id;
			EXECUTE insert_new_relation USING @var_id, @var_id, @var_parent_id;
		END IF;
	UNTIL done END REPEAT;
	
	CLOSE has_parents;
	
    DEALLOCATE PREPARE copy_existent_relations;
    DEALLOCATE PREPARE insert_new_relation;
    DEALLOCATE PREPARE first_level_update;
    DEALLOCATE PREPARE who_has_parents;
    
    DROP TEMPORARY TABLE IF EXISTS `tmp_relation_update`;
END$$

CREATE DEFINER=`cformat_cms`@`%` PROCEDURE `clean_relation`(IN table_name VARCHAR(64), IN id INTEGER(11))
    SQL SECURITY INVOKER
BEGIN
     DECLARE delete_query, insert_query TEXT DEFAULT '';
     SET @insert_query = CONCAT("
         INSERT INTO `tmp_relation` (`id`)
         SELECT id
         FROM `",table_name,"`
         WHERE parent='",id,"'
     ");
     SET @delete_query = CONCAT("
          DELETE FROM `",table_name,"` WHERE id IN (
                 SELECT id FROM `tmp_relation`
          )
     ");
     CREATE TEMPORARY TABLE `tmp_relation` (
            `id` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`id`)
     ) ENGINE=MEMORY;
     PREPARE insert_query FROM @insert_query;
     PREPARE delete_query FROM @delete_query;
     EXECUTE insert_query;
     EXECUTE delete_query;
     DEALLOCATE PREPARE insert_query;
     DEALLOCATE PREPARE delete_query;
     DROP TEMPORARY TABLE IF EXISTS tmp_relation;
END$$

CREATE DEFINER=`cformat_cms`@`%` PROCEDURE `get_currency_rate`(IN _date DATETIME, IN _currency_from_id SMALLINT, IN _currency_to_id SMALLINT, IN _currency_cross_id SMALLINT, OUT _rate DECIMAL(10,4))
    SQL SECURITY INVOKER
BEGIN
DECLARE x_rate_dtime DATETIME;
DECLARE x_rate, x_rate_from, x_rate_to DECIMAL(10,2) DEFAULT 0;
DECLARE x_total_rows INT(10);
IF _currency_from_id = _currency_to_id THEN
   SET _rate = 1;
ELSE
    SELECT MAX(dtime) INTO @x_rate_dtime
    FROM currency_rate
    WHERE dtime <= _date;
    
    SELECT COUNT(*), MIN(rate) INTO @x_total_rows, @x_rate
    FROM currency_rate
    WHERE
            dtime=@x_rate_dtime
        AND currency_from_id=_currency_from_id
        AND currency_to_id=_currency_to_id;
    IF @x_total_rows > 0 THEN
        SET _rate = @x_rate;
    ELSE
        IF _currency_from_id=_currency_cross_id THEN
            SET @x_rate_from = 1;
        ELSE
            SELECT COUNT(*), MIN(rate) INTO @x_total_rows, @x_rate_from
            FROM currency_rate
            WHERE
                dtime=@x_rate_dtime
                AND currency_from_id=_currency_from_id
                AND currency_to_id=_currency_cross_id;
        END IF;
        
        IF _currency_to_id=_currency_cross_id THEN
            SET @x_rate_to = 1;
        ELSE
            SELECT COUNT(*), MIN(rate) INTO @x_total_rows, @x_rate_to
            FROM currency_rate
            WHERE
                dtime=@x_rate_dtime
                AND currency_from_id=_currency_cross_id
                AND currency_to_id=_currency_to_id;
        END IF;
        SET _rate=CAST(@x_rate_from * @x_rate_to AS DECIMAL(10,4));
    END IF;
    IF _rate IS NULL THEN
        SET _rate = 0;
    END IF;
END IF;	
END$$

CREATE DEFINER=`cformat_cms`@`%` PROCEDURE `hosting_get_ip`()
    READS SQL DATA
BEGIN
select tb_ip.id, tb_ip.ip, cast(concat(tb_ip.ip, IFNULL(concat(' @ ', group_concat(tb_used.name_fqdn)), '')) as char) as hostname
from hosting_ip as tb_ip 
left join (
(
	select ip, name_fqdn from hosting_server where server_id=0
) union (
	select tb_server.ip, tb_server.name_fqdn
	from hosting_server as tb_server 
	inner join hosting_server_ip as tb_server_ip on tb_server_ip.server_id=tb_server.id
	where tb_server.server_id=0
	group by tb_server.ip
) union (
	select tb_ip.ip, tb_dedicated.name
	from hosting_dedicated_order_ip as tb_order_ip
	inner join hosting_dedicated_order as tb_order on tb_order.id=tb_order_ip.order_id
	inner join hosting_dedicated_server as tb_dedicated on tb_dedicated.id=tb_order.server_id
    inner join hosting_ip as tb_ip on tb_ip.id=tb_order_ip.ip_id
	group by tb_ip.ip
) union (
	select tb_ip.ip, tb_server.name
	from hosting_dedicated_server as tb_server
    inner join hosting_ip as tb_ip on tb_ip.id=tb_server.ip_id
) union (
	select tb_ip.ip, concat('vps-', tb_vps.id)
	from hosting_vps_ip as tb_vps_ip
	inner join hosting_vps as tb_vps on tb_vps.id=tb_vps_ip.vps_id
	inner join hosting_ip as tb_ip on tb_ip.id=tb_vps_ip.ip_id
)) as tb_used on tb_used.ip=tb_ip.ip
group by tb_ip.id;
END$$

--
-- Функции
--
CREATE DEFINER=`cformat_cms`@`%` FUNCTION `get_age`(birthday DATE, `date` DATE) RETURNS tinyint(4)
    DETERMINISTIC
RETURN
      CASE
          WHEN birthday IS NULL THEN 0
          WHEN MONTH(date) < MONTH(birthday) THEN YEAR(date) - YEAR(birthday) - 1
          WHEN MONTH(date) > MONTH(birthday) THEN YEAR(date) - YEAR(birthday)
          WHEN DAYOFMONTH(date) < DAYOFMONTH(birthday) THEN YEAR(date) - YEAR(birthday) - 1
          WHEN DAYOFMONTH(date) > DAYOFMONTH(birthday) THEN YEAR(date) - YEAR(birthday)
          ELSE YEAR(date) - YEAR(birthday)
      END$$

CREATE DEFINER=`cformat_cms`@`%` FUNCTION `html_editor`(id INTEGER(11), table_name VARCHAR(70), field_name VARCHAR(70), title VARCHAR(255)) RETURNS text CHARSET cp1251
    DETERMINISTIC
RETURN CONCAT("<a href=\"#\" onclick=\"EditorWindow('event=editor/content&id=",id,"&table_name=",table_name,"&field_name=",field_name,"', '",table_name,id,"');return false;\">",title,"</a>")$$

CREATE DEFINER=`cformat_cms`@`%` FUNCTION `interval_intersect`(x_from BIGINT, x_to BIGINT, y_from BIGINT, y_to BIGINT) RETURNS tinyint(4)
    DETERMINISTIC
RETURN
      CASE
          WHEN x_from > x_to OR y_from > y_to THEN -1
          WHEN x_from <= y_from                    AND x_to >= y_from AND x_to <= y_to THEN 1
          WHEN x_from >= y_from AND x_from <= y_to AND x_to >= y_from AND x_to <= y_to THEN 2
          WHEN x_from >= y_from AND x_from <= y_to                    AND x_to >= y_to THEN 3
          WHEN x_from <= y_from                                       AND x_to >= y_to THEN 4
          ELSE 0
      END$$

CREATE DEFINER=`cformat_cms`@`%` FUNCTION `text_editor`(id INTEGER(11), table_name VARCHAR(70), field_name VARCHAR(70), title VARCHAR(255)) RETURNS text CHARSET cp1251
    DETERMINISTIC
RETURN CONCAT("<a href=\"#\" onclick=\"EditScript('id=",id,"&table_name=",table_name,"&field_name=",field_name,"', '",table_name,id,"');return false;\">",title,"</a>")$$

CREATE DEFINER=`cformat_cms`@`%` FUNCTION `user_check`(user_registered ENUM('true','false'), user_confirmed ENUM('true','false'), user_checked ENUM('true','false'), required_type ENUM('any','registered','confirmed','checked')) RETURNS enum('true','false') CHARSET cp1251
    DETERMINISTIC
    SQL SECURITY INVOKER
return case
       when required_type='any' then 'true'
       when required_type='registered' and user_registered='true' then 'true'
       when required_type='confirmed' and user_confirmed='true' or user_checked='true' then 'true'
       when required_type='checked' and user_checked='true' then 'true'
       else 'false'
end$$

DELIMITER ;

-- --------------------------------------------------------
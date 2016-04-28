DELIMITER $$
DROP function IF EXISTS `func_split_TotalLength` $$
CREATE FUNCTION `func_split_TotalLength`
(f_string varchar(10000),f_delimiter varchar(5)) RETURNS int(11)
BEGIN
    # 计算传入字符串的总length
    return 1+(length(f_string) - length(replace(f_string,f_delimiter,'')));
END$$
DELIMITER ;


# 函数：func_split
DELIMITER $$
DROP function IF EXISTS `func_split` $$
CREATE FUNCTION `func_split`
(f_string varchar(10000),f_delimiter varchar(5),f_order int(11)) RETURNS varchar(255) CHARSET utf8
BEGIN
    # 拆分传入的字符串，返回拆分后的新字符串
    declare result varchar(255) default '';
    set result = reverse(substring_index(reverse(substring_index(f_string,f_delimiter,f_order)),f_delimiter,1));
    return result;
END$$
DELIMITER ;

# 存储过程：setWeshareDefaultImage
DELIMITER $$
DROP PROCEDURE IF EXISTS `setWeshareDefaultImage` $$
CREATE PROCEDURE `setWeshareDefaultImage`
(IN f_string varchar(10000),IN f_delimiter varchar(5), IN weshare_id int(11))
BEGIN
# 拆分结果
declare cnt int default 0;
set cnt = func_split_TotalLength(f_string,f_delimiter);
if cnt > 0 then
	update cake_weshares set default_image=func_split(f_string,f_delimiter,1) where id=weshare_id;
end if;
END$$
DELIMITER ;


drop procedure IF EXISTS batchSetWeshareDefaultImg;
delimiter //
create procedure batchSetWeshareDefaultImg()
begin
	-- 声明一个标志done， 用来判断游标是否遍历完成
	DECLARE done INT DEFAULT 0;
	-- 声明一个变量，用来存放从游标中提取的数据
	-- 特别注意这里的名字不能与由游标中使用的列明相同，否则得到的数据都是NULL
	DECLARE p_id int(11) DEFAULT NULL;
	DECLARE p_images varchar(10000) DEFAULT NULL;
	-- 声明游标对应的 SQL 语句
	DECLARE cur CURSOR FOR
		select id, images from cake_weshares;
	-- 在游标循环到最后会将 done 设置为 1
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
	-- 执行查询
	open cur;
	-- 遍历游标每一行
	REPEAT
		-- 把一行的信息存放在对应的变量中
		FETCH cur INTO p_id, p_images;
		if not done then
			-- 这里就可以使用 tname， tpass 对应的信息了
			call setWeshareDefaultImage(p_images, '|', p_id);
		end if;
 	UNTIL done END REPEAT;
	CLOSE cur;
end
//
delimiter ;


call batchSetWeshareDefaultImg();


-- 初始化阅读量
update cake_weshares set view_count = FLOOR(190 + (RAND() * 500));
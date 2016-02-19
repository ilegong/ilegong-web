SELECT cp.id,cp.name FROM cake_products cp where cp.comment_nums !=(select count(*) from cake_comments cc where cc.data_id=cp.id and cc.status=1);

update cake_products cp set cp.comment_nums=(select count(*) from cake_comments cc where cc.data_id=cp.id and cc.status=1);
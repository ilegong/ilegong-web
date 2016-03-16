alter table cake_opt_logs add column obj_creator int after obj_id;

update cake_opt_logs as cpl join cake_weshares as cw set cpl.obj_creator = cw.creator where cpl.obj_id = cw.id;

alter table cake_opt_logs add index idx_cake_opt_logs_created(created);
alter table cake_opt_logs add index idx_cake_opt_logs_obj_id(obj_id);

alter table cake_user_levels add index idx_cake_user_levels_data_id(data_id);
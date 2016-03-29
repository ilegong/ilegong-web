drop index idx_share_id on cake_new_opt_logs;
drop index idx_proxy_id on cake_new_opt_logs;
drop index idx_customer_id on cake_new_opt_logs;
drop index idx_time on cake_new_opt_logs;

alter table cake_new_opt_logs add index idx_new_opt_logs_share_id (share_id);
alter table cake_new_opt_logs add index idx_new_opt_logs_proxy_id (proxy_id);
alter table cake_new_opt_logs add index idx_new_opt_logs_customer_id (customer_id);
alter table cake_new_opt_logs add index idx_new_opt_logs_time (time);

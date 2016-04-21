alter table cake_index_products add column measurement varchar(255) after description;
alter table cake_users add column label varchar(15) after is_proxy;

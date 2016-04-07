alter table cake_pool_products add column m_share_img varchar(255) after share_img;
alter table cake_pool_products add column m_share_name varchar(60) after share_img;

update cake_pool_products set m_share_img = share_img, m_share_name = share_name;
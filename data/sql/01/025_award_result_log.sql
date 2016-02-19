alter table cake_award_results add column `award_type` char(12) not null default '';
alter table cake_award_results add column `award_data` varchar(255) not null default '';
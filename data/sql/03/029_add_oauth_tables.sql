CREATE TABLE cake_oauth_clients (client_id VARCHAR(80) NOT NULL, client_secret VARCHAR(80) NOT NULL, redirect_uri VARCHAR(255) NOT NULL, grant_types VARCHAR(80), scope VARCHAR(100), user_id VARCHAR(80), CONSTRAINT clients_client_id_pk PRIMARY KEY (client_id));
CREATE TABLE cake_oauth_access_tokens (access_token VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), expires TIMESTAMP NOT NULL, scope VARCHAR(255), CONSTRAINT access_token_pk PRIMARY KEY (access_token));
CREATE TABLE cake_oauth_authorization_codes (authorization_code VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), redirect_uri VARCHAR(255), expires TIMESTAMP NOT NULL, scope VARCHAR(255), CONSTRAINT auth_code_pk PRIMARY KEY (authorization_code));
CREATE TABLE cake_oauth_refresh_tokens (refresh_token VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), expires TIMESTAMP NOT NULL, scope VARCHAR(255), CONSTRAINT refresh_token_pk PRIMARY KEY (refresh_token));
CREATE TABLE cake_oauth_users (username VARCHAR(50) NOT NULL, password VARCHAR(255), first_name VARCHAR(255), last_name VARCHAR(255), CONSTRAINT username_pk PRIMARY KEY (username));
CREATE TABLE cake_oauth_scopes (scope TEXT, is_default BOOLEAN);
CREATE TABLE cake_oauth_jwt (client_id VARCHAR(80) NOT NULL, subject VARCHAR(80), public_key VARCHAR(255), CONSTRAINT jwt_client_id_pk PRIMARY KEY (client_id));

INSERT INTO cake_oauth_clients (client_id, client_secret, redirect_uri) VALUES ("testclient", "testpass", "http://dev.tongshijia.com/");
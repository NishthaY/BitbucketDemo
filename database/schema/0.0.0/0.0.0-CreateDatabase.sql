create role :db;
ALTER ROLE :db WITH NOSUPERUSER INHERIT NOCREATEROLE NOCREATEDB LOGIN NOREPLICATION PASSWORD 'md5ff09e67e1ab3852e0240bd9f5deae3c4';
create database :db owner :db;
GRANT ALL PRIVILEGES ON DATABASE :db to :username ;

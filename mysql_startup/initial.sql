-- noinspection SqlIdentifierLengthForFile

create database if not exists app character set utf8mb4 collate utf8mb4_0900_ai_ci;

use app;
CREATE TABLE IF NOT EXISTS users
(
    user_handle   varchar(50)     not null,
    password_hash varchar(64)     not null, /* sha256 hash */
    password_salt varchar(5)      not null, /* random 5 character string */
    name          varchar(100)    not null,
    timezone      varchar(100)    not null default 'GMT',
    category_id   bigint unsigned not null,
    index (user_handle),
    primary key (user_handle)
);

CREATE TABLE IF NOT EXISTS categories
(
    category_id        bigint unsigned not null auto_increment,
    user_handle        varchar(50)     not null,
    parent_category_id bigint unsigned not null default 0,
    name               varchar(1000),
    index (category_id, user_handle),
    primary key (category_id)
);

CREATE TABLE IF NOT EXISTS websites
(
    website_id  bigint unsigned not null auto_increment,
    user_handle varchar(50)     not null,
    website_url varchar(1000)   not null,
    category_id bigint unsigned not null,
    title       varchar(1000)   not null default '',
    index (website_id, user_handle),
    primary key (website_id)
);

CREATE TABLE IF NOT EXISTS highlights
(
    highlight_id bigint unsigned not null auto_increment,
    website_id   bigint unsigned not null,
    strings      varchar(10000)  not null,
    comment      varchar(1000)   not null,
    index (highlight_id, website_id),
    primary key (highlight_id)
);

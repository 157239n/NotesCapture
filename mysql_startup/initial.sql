-- noinspection SqlIdentifierLengthForFile

create database if not exists app character set utf8mb4 collate utf8mb4_0900_ai_ci;

use app;
CREATE TABLE IF NOT EXISTS users
(
    user_handle   varchar(100)    not null,
    password_hash varchar(64)     not null default '', /* sha256 hash, federated=0 */
    password_salt varchar(5)      not null default '', /* random 5 character string, federated=0 */
    name          varchar(1000)   not null,
    timezone      varchar(100)    not null default 'GMT',
    category_id   bigint unsigned not null,
    federated     varchar(100)    not null default '', /* '', 'google', 'facebook' */
    picture_url    varchar(1000)   not null,
    index (user_handle),
    primary key (user_handle)
);

/* a category can have multiple child categories and multiple websites */
CREATE TABLE IF NOT EXISTS categories
(
    category_id        bigint unsigned not null auto_increment,
    user_handle        varchar(100)    not null,
    parent_category_id bigint unsigned not null default 0,
    name               varchar(1000),
    index (category_id, user_handle),
    primary key (category_id)
);

/* a website can have multiple highlights */
CREATE TABLE IF NOT EXISTS websites
(
    website_id  bigint unsigned not null auto_increment,
    user_handle varchar(100)    not null,
    website_url varchar(1000)   not null,
    category_id bigint unsigned not null,
    title       varchar(1000)   not null default '',
    index (website_id, user_handle),
    primary key (website_id)
);

/* a highlight can have multiple comments that form a chain */
CREATE TABLE IF NOT EXISTS highlights
(
    highlight_id bigint unsigned not null auto_increment,
    website_id   bigint unsigned not null,
    strings      varchar(10000)  not null,
    index (highlight_id, website_id),
    primary key (highlight_id)
);

CREATE TABLE IF NOT EXISTS comments
(
    comment_id        bigint unsigned not null auto_increment,
    highlight_id      bigint unsigned not null,
    user_handle       varchar(100)    not null,
    parent_comment_id bigint unsigned not null default 0,
    unix_time         bigint unsigned not null,
    content           varchar(1000)   not null,
    index (comment_id, highlight_id),
    primary key (comment_id)
);

/* what user has what access to what websites? 0: readable and writable, 1: readable */
CREATE TABLE IF NOT EXISTS permissions
(
    permission_id bigint unsigned not null auto_increment,
    website_id    bigint unsigned not null,
    user_handle   varchar(100)    not null,
    access        int unsigned    not null,
    index (permission_id, website_id),
    primary key (permission_id)
);

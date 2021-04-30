\set db advice2pay

-- Create a new table called KeyPool that will hold security keys
-- that can be grabbed when needed.
create table "KeyPool"
(
    "Id" serial not null constraint """KeyPool""_pk" primary key,
    "Name" text not null,
    "EncryptionKey" text,
    "Enabled" boolean default false not null
);
create unique index """KeyPool""_""Id""_uindex" on "KeyPool" ("Id");


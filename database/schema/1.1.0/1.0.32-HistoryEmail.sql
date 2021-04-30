\set db advice2pay

create table "HistoryEmail"
(
  "Id" serial not null constraint historyemail_id_pk primary key,
  "CompanyId" integer,
  "UserId" integer,
  "SentDate" timestamp with time zone default now() not null,
  "To" text,
  "ToAddress" text,
  "FromAddress" text,
  "From" text,
  "Subject" text,
  "Body" text
);
ALTER TABLE "HistoryEmail" OWNER TO :db;
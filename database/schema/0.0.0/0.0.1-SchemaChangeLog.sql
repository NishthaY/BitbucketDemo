-- SchemaChangeLog
-- Create the table and insert the records so that we know these scripts
-- have been executed.

CREATE TABLE "SchemaChangeLog"
(
  "SchemaChangeId" bigserial NOT NULL,
  "MajorReleaseNumber" integer,
  "MinorReleaseNumber" integer,
  "PointReleaseNumber" integer,
  "ScriptName" text,
  "DateApplied" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  CHECK(EXTRACT(TIMEZONE FROM "DateApplied") = '0'),    -- Force UTC
  CONSTRAINT "SchemaChangeLog_pkey" PRIMARY KEY ("SchemaChangeId")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "SchemaChangeLog" OWNER TO :db;

insert into "SchemaChangeLog" ( "MajorReleaseNumber", "MinorReleaseNumber", "PointReleaseNumber", "ScriptName", "DateApplied" ) values ( 0, 0, 0, '0.0.0-CreateDatabase.sql', CURRENT_TIMESTAMP );
insert into "SchemaChangeLog" ( "MajorReleaseNumber", "MinorReleaseNumber", "PointReleaseNumber", "ScriptName", "DateApplied" ) values ( 0, 0, 1, '0.0.1-SchemaChangeLog.sql', CURRENT_TIMESTAMP );


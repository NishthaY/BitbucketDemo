\set db advice2pay

DROP TABLE "FileTransfer";

DROP TABLE "CompanyFileTransfer";
CREATE TABLE "CompanyFileTransfer"
(
  "Id" bigserial NOT NULL,
  "CompanyId" integer NOT NULL,
  "Enabled" boolean NOT NULL DEFAULT false,
  "Hostname" text NOT NULL,
  "Username" text NULL,
  "EncryptedPassword" text NULL,
  "EncryptedSSHKey" text NULL,
  "Protocol" text NOT NULL,
  "Port" integer NULL,
  "DestinationPath" text NULL,
  CONSTRAINT "CompanyFileTransferId" PRIMARY KEY ("Id")
)
WITH (
OIDS=FALSE
);
ALTER TABLE "CompanyFileTransfer" OWNER TO :db;

DROP TABLE "CompanyParentFileTransfer";
CREATE TABLE "CompanyParentFileTransfer"
(
  "Id" bigserial NOT NULL,
  "CompanyParentId" integer NOT NULL,
  "Enabled" boolean NOT NULL DEFAULT false,
  "Hostname" text NOT NULL,
  "Username" text NULL,
  "EncryptedPassword" text NULL,
  "EncryptedSSHKey" text NULL,
  "Protocol" text NOT NULL,
  "Port" integer NULL,
  "DestinationPath" text NULL,
  CONSTRAINT "CompanyParentFileTransferId" PRIMARY KEY ("Id")
)
WITH (
OIDS=FALSE
);
ALTER TABLE "CompanyParentFileTransfer" OWNER TO :db;


CREATE TABLE "CompanyParentPreference"
(
    "CompanyParentId" int not null
  , "Group" text NOT null
  , "GroupCode" text null
  , "Value" text NULL
)
WITH (
OIDS=FALSE
);
ALTER TABLE "CompanyParentPreference" OWNER TO :db;
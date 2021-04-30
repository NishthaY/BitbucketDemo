\set db advice2pay

CREATE TABLE "CompanyParentFileTransfer"
(
    "Id" bigserial NOT NULL,
    "CompanyParentId" integer NOT NULL,
    "FileTransferId" integer NOT NULL,
    "Enabled" boolean NOT NULL DEFAULT false,
    CONSTRAINT "CompanyParentFileTransferId" PRIMARY KEY ("Id")
)
WITH (
OIDS=FALSE
);
ALTER TABLE "CompanyParentFileTransferId" OWNER TO :db;

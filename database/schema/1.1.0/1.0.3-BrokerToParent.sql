
-- CompanyParent
CREATE TABLE "CompanyParent"
(
    "Id" bigserial NOT NULL,
    "Name" text NOT NULL,
    "Address" text NULL,
    "City" text NULL,
    "State" text NULL,
    "Postal" text NULL,
    "Seats" INT NOT NULL default 0,
    "Enabled" boolean NOT NULL DEFAULT false,
    CONSTRAINT "CompanyParentId" PRIMARY KEY ("Id")
)
WITH (
OIDS=FALSE
);
ALTER TABLE "CompanyParent" OWNER TO :db;

-- UserCompanyParent
CREATE TABLE "UserCompanyParentRelationship"
(
    "UserId" integer NOT NULL,
    "CompanyParentId" integer NOT NULL
)
WITH (
OIDS=FALSE
);
ALTER TABLE "UserCompanyParentRelationship" OWNER TO :db;

-- CompanyParentCompanyRelationship
CREATE TABLE "CompanyParentCompanyRelationship"
(
    "CompanyParentId" integer NOT NULL,
    "CompanyId" integer NOT NULL
)
WITH (
OIDS=FALSE
);
ALTER TABLE "CompanyParentCompanyRelationship" OWNER TO :db;

-- Alter existing tables that reference BrokerId
ALTER TABLE "Log" RENAME COLUMN "BrokerId" TO "CompanyParentId";
ALTER TABLE "Audit" RENAME COLUMN "BrokerId" TO "CompanyParentId";

-- Migrate Broker table data to CompanyParent data.
INSERT INTO "CompanyParent" SELECT * FROM "Broker";
INSERT INTO "UserCompanyParentRelationship" SELECT * from "UserBroker";
INSERT INTO "CompanyParentCompanyRelationship" select * from "BrokerCompany";

-- Drop the Broker Tables.
DROP TABLE "BrokerCompany";
DROP TABLE "UserBroker";
DROP TABLE "Broker";

-- Migrate Broker ACLs to CompanyParent ACLs
update "Acl" set "Name"='company_parent_write' where "Name"='broker_write';
update "Acl" set "Description"='Grants read/write access to company parent data.' where "Name"='company_parent_write';
update "Acl" set "Name"='company_parent_read' where "Name"='broker_read';
update "Acl" set "Description"='Grants read-only access to company parent data.' where "Name"='company_parent_read';
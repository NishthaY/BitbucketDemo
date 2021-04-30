\set db advice2pay

CREATE TABLE "CompanyParentMappingColumns"
(
  "CompanyParentId" INT NOT NULL,
  "Name" text NOT NULL,
  "Required" boolean NOT NULL DEFAULT false
)
WITH (
OIDS=FALSE
);
ALTER TABLE "CompanyParentMappingColumns" OWNER TO :db;
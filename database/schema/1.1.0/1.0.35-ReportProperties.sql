\set db advice2pay

DROP TABLE "CompanyParentMappingColumns";

CREATE TABLE "ReportProperties"
(
  "ReportCode" text not null,
  "Group" text not null,
  "Key" text not null,
  "Value" text
);
ALTER TABLE "ReportProperties" OWNER TO :db;
CREATE INDEX reportproperties_group_index on "ReportProperties" ("ReportCode", "Group");
CREATE INDEX reportproperties_group_key_index on "ReportProperties" ("ReportCode", "Group", "Key");


-- detail
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'detail', 'REQUIRED_COLUMN', 'coverage_end_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'detail', 'REQUIRED_COLUMN', 'coverage_start_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'detail', 'REQUIRED_COLUMN', 'dob', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'detail', 'REQUIRED_COLUMN', 'monthly_cost', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'detail', 'REQUIRED_COLUMN', 'plan_type', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'detail', 'REQUIRED_COLUMN', 'volume', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'detail', 'REQUIRED_COLUMN', 'last_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'detail', 'REQUIRED_COLUMN', 'first_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'detail', 'REQUIRED_COLUMN', 'eid', 'TRUE' );

-- summary
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'summary', 'REQUIRED_COLUMN', 'coverage_end_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'summary', 'REQUIRED_COLUMN', 'coverage_start_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'summary', 'REQUIRED_COLUMN', 'dob', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'summary', 'REQUIRED_COLUMN', 'monthly_cost', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'summary', 'REQUIRED_COLUMN', 'plan_type', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'summary', 'REQUIRED_COLUMN', 'volume', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'summary', 'REQUIRED_COLUMN', 'last_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'summary', 'REQUIRED_COLUMN', 'first_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'summary', 'REQUIRED_COLUMN', 'eid', 'TRUE' );

-- pe_detail
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_detail', 'REQUIRED_COLUMN', 'coverage_end_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_detail', 'REQUIRED_COLUMN', 'coverage_start_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_detail', 'REQUIRED_COLUMN', 'dob', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_detail', 'REQUIRED_COLUMN', 'monthly_cost', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_detail', 'REQUIRED_COLUMN', 'plan_type', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_detail', 'REQUIRED_COLUMN', 'volume', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_detail', 'REQUIRED_COLUMN', 'last_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_detail', 'REQUIRED_COLUMN', 'first_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_detail', 'REQUIRED_COLUMN', 'eid', 'TRUE' );

-- pe_summary
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_summary', 'REQUIRED_COLUMN', 'coverage_end_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_summary', 'REQUIRED_COLUMN', 'coverage_start_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_summary', 'REQUIRED_COLUMN', 'dob', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_summary', 'REQUIRED_COLUMN', 'monthly_cost', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_summary', 'REQUIRED_COLUMN', 'plan_type', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_summary', 'REQUIRED_COLUMN', 'volume', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_summary', 'REQUIRED_COLUMN', 'last_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_summary', 'REQUIRED_COLUMN', 'first_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'pe_summary', 'REQUIRED_COLUMN', 'eid', 'TRUE' );

-- transamerica_actuarial
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'coverage_end_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'coverage_start_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'dob', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'monthly_cost', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'plan_type', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'volume', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'last_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'first_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'eid', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'policy', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'state', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'postalcode', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'group_number', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_actuarial', 'REQUIRED_COLUMN', 'enrollment_state', 'TRUE' );

-- transamerica_commission
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_commission', 'REQUIRED_COLUMN', 'coverage_end_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_commission', 'REQUIRED_COLUMN', 'coverage_start_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_commission', 'REQUIRED_COLUMN', 'dob', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_commission', 'REQUIRED_COLUMN', 'monthly_cost', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_commission', 'REQUIRED_COLUMN', 'plan_type', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_commission', 'REQUIRED_COLUMN', 'volume', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_commission', 'REQUIRED_COLUMN', 'last_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_commission', 'REQUIRED_COLUMN', 'first_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_commission', 'REQUIRED_COLUMN', 'eid', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_commission', 'REQUIRED_COLUMN', 'policy', 'TRUE' );

-- transamerica_eligibility
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'coverage_end_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'coverage_start_date', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'dob', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'monthly_cost', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'plan_type', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'volume', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'last_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'first_name', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'eid', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'group_number', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'enrollment_state', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'relationship', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'address1', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'city', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'state', 'TRUE' );
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value" ) values ( 'transamerica_eligibility', 'REQUIRED_COLUMN', 'postalcode', 'TRUE' );

-- Moving the table "CompanyMappingColumn" to "CompanyBestMappedCoumn"
CREATE TABLE "CompanyBestMappedColumn"
(
  "Id" bigserial NOT NULL,
  "CompanyId" integer NOT NULL,
  "ColumnName" text NULL,
  "ColumnNameNormalized" text NULL,
  "MappingColumnName" text NULL,
  "ColumnNumber" integer NULL,
  CONSTRAINT "CompanyBestMappedColumnId" PRIMARY KEY ("Id")
)
WITH (
OIDS=FALSE
);
ALTER TABLE "CompanyBestMappedColumn" OWNER TO :db;

INSERT INTO "CompanyBestMappedColumn" ( "CompanyId", "ColumnName", "ColumnNameNormalized", "MappingColumnName", "ColumnNumber")
  SELECT "CompanyId", "ColumnName", "ColumnNameNormalized", "MappingColumnName", "ColumnNumber" FROM "CompanyMappingColumn";

DROP TABLE "CompanyMappingColumn";


-- Create a table identical to MappingColumns but specifically for a company.
CREATE TABLE "CompanyMappingColumn"
(
  "CompanyId" INT NOT NULL,
  "Name" text not null,
  "Display" text not null,
  "Required" boolean default false not null,
  "DefaultValue" text,
  "ColumnName" text,
  "Encrypted" boolean default false
)
WITH (
OIDS=FALSE
);
ALTER TABLE "CompanyMappingColumn" OWNER TO :db;



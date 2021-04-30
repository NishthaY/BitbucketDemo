\set db advice2pay


-- Advice2Pay Companies
CREATE TABLE "Company"
(
  "Id" bigserial NOT NULL,
  "CompanyName" text NOT NULL,
  "CompanyAddress" text NULL,
  "CompanyCity" text NULL,
  "CompanyState" text NULL,
  "CompanyPostal" text NULL,
  "Enabled" boolean NOT NULL DEFAULT false,
  CONSTRAINT "CompanyId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "Company" OWNER TO :db;

INSERT INTO "Company" ( "CompanyName", "Enabled", "CompanyAddress", "CompanyCity", "CompanyState", "CompanyPostal" ) VALUES ( 'Advice2Pay', true, '1204 S. 3rd St', 'Indianola', 'IA', '50125');


CREATE TABLE "CompanyPreference"
(
    "CompanyId" int not null
    , "Group" text NOT null
    , "GroupCode" text null
    , "Value" text NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyPreference" OWNER TO :db;
-- Advice2Pay Users
CREATE TABLE "User"
(
  "Id" bigserial NOT NULL,
  "EmailAddress" text NOT NULL,
  "Password" text NOT NULL,
  "FirstName" text NULL,
  "LastName" text NULL,
  "Enabled" boolean NOT NULL DEFAULT false,
  CONSTRAINT "Id" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "User" OWNER TO :db;

-- We no longer want the guest user, but I don't want to shift user_ids for the other so create it and then delete it.
INSERT INTO "User" ( "EmailAddress", "Password", "Enabled") VALUES ( 'guest@nolasoft.com', '{SHA1}35675e68f4b5af7b995d9205ad0fc43842f16450', true);
DELETE FROM "USER" where "EmailAddress" = 'guest@nolasoft.com';





CREATE TABLE "UserPreference"
(
    "UserId" int not null
    , "Group" text NOT null
    , "GroupCode" text null
    , "Value" text NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "UserPreference" OWNER TO :db;


-- Access Control Lists
CREATE TABLE "Acl"
(
  "Id" bigserial NOT NULL,
  "Name" text NOT NULL,
  "Description" text NOT NULL,
  CONSTRAINT "AclId" PRIMARY KEY ("Id")
)
WITH ( OIDS=FALSE );
ALTER TABLE "Acl" OWNER TO advice2pay;

insert into "Acl" ( "Name", "Description" ) values ( 'admin', 'Grants administration rights.' );
insert into "Acl" ( "Name", "Description" ) values ( 'company_write', 'Grants readwrite access to company data.' );
insert into "Acl" ( "Name", "Description" ) values ( 'company_read', 'Grants read-only access to company data.' );
insert into "Acl" ( "Name", "Description" ) values ( 'broker_write', 'Grants read/write access to broker data.' );
insert into "Acl" ( "Name", "Description" ) values ( 'broker_read', 'Grants read-only access to broker data.' );

-- Assign User Permissions
CREATE TABLE "UserAcl"
(
  "UserId" integer NOT NULL,
  "AclId" integer NOT NULL
)
WITH ( OIDS=FALSE );
ALTER TABLE "UserAcl" OWNER TO advice2pay;

-- Advice2Pay Users
CREATE TABLE "UserCompany"
(
  "UserId" integer NOT NULL,
  "CompanyId" integer NOT NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "UserCompany" OWNER TO :db;

-- Advice2Pay Brokers
CREATE TABLE "Broker"
(
  "Id" bigserial NOT NULL,
  "BrokerName" text NOT NULL,
  "BrokerAddress" text NULL,
  "BrokerCity" text NULL,
  "BrokerState" text NULL,
  "BrokerPostal" text NULL,
  "Seats" INT NOT NULL default 0,
  "Enabled" boolean NOT NULL DEFAULT false,
  CONSTRAINT "BrokerId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "Broker" OWNER TO :db;
-- Advice2Pay Users
CREATE TABLE "UserBroker"
(
  "UserId" integer NOT NULL,
  "BrokerId" integer NOT NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "UserBroker" OWNER TO :db;
-- Advice2Pay Users
CREATE TABLE "BrokerCompany"
(
  "BrokerId" integer NOT NULL,
  "CompanyId" integer NOT NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "BrokerCompany" OWNER TO :db;
-- Log Table
CREATE TABLE "Log"
(
  "Id" bigserial NOT NULL,
  "LogDate" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  "UserId" int NULL,
  "ShortDesc" text NOT NULL,
  "BrokerId" INT NULL,
  "CompanyId" INT NULL,
  "LongDesc" text NULL,
  "Payload" text NULL,
  "Session" text NOT NULL,
  CHECK(EXTRACT(TIMEZONE FROM "LogDate") = '0'),    -- Force UTC
  CONSTRAINT "LogId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "Log" OWNER TO :db;

-- Function that will remove old Log rows.
CREATE FUNCTION delete_old_rows() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
  DELETE FROM "Log" WHERE "LogDate" < NOW() - INTERVAL '2 days';
  RETURN NULL;
END;
$$;

-- Trigger that will remove old rows everytime an insert happens.
CREATE TRIGGER trigger_delete_old_rows
    AFTER INSERT ON "Log"
    EXECUTE PROCEDURE delete_old_rows();

CREATE TABLE "Audit"
(
  "Id" bigserial NOT NULL,
  "AuditDate" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  "UserId" int NULL,
  "BrokerId" INT NULL,
  "CompanyId" INT NULL,
  "Description" text NOT NULL,
  "Payload" text NULL,
  CHECK(EXTRACT(TIMEZONE FROM "AuditDate") = '0'),    -- Force UTC
  CONSTRAINT "AuditId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "Audit" OWNER TO :db;


-- Log Table
CREATE TABLE "BackgroundTask"
(
  "Id" bigserial NOT NULL,
  "Name" text NOT NULL,
  "RefreshMinutes" INT NOT NULL DEFAULT 0,
  "RefreshEnabled" boolean NOT NULL DEFAULT false,
  "DebugUser" text NULL,
  "InfoUser" text NULL,
  CONSTRAINT "BackgroundTaskId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "BackgroundTask" OWNER TO :db;


insert into "BackgroundTask" ( "Name", "RefreshMinutes", "RefreshEnabled", "DebugUser", "InfoUser") values ( 'dashboard_task', 1, true, 'brian@advice2pay.com', 'brian@advice2pay.com');


CREATE TABLE "CompanyPlanType"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "Carrier" text NOT NULL
    , "PlanType" text NOT NULL
    , "PlanTypeCode" text NULL
    , "RetroRule" text NULL
    , "WashRule" text NULL
    , "Ignored" boolean NOT NULL DEFAULT false,
    CONSTRAINT "CompanyPlanTypeId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyPlanType" OWNER TO :db;

CREATE TABLE "MappingColumns"
(
  "Name" text NOT NULL,
  "Display" text NOT NULL,
  "Required" boolean NOT NULL DEFAULT false
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "MappingColumns" OWNER TO :db;

-- Required
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'carrier', 'Carrier', true );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'coverage_end_date', 'Coverage End Date', true );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'coverage_start_date', 'Coverage Start Date', true );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'dob', 'Date Of Birth', true );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'eid', 'Employee ID', true );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'first_name', 'First Name', true );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'last_name', 'Last Name', true );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'monthly_cost', 'Monthly Cost', true );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'plan_type', 'Plan Type', true );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'volume', 'Volume', true );

-- Optional
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'address1', 'Address1', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'address2', 'Address2', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'annual_salary', 'Annual Salary', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'business_unit', 'Business Unit', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'city', 'City', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'coverage_tier', 'Coverage Tier', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'department', 'Department', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'division', 'Division', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'email1', 'Email 1', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'email2', 'Email 2', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'employment_active', 'Employment Active (Y/N)', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'employment_end', 'Employment End', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'employment_start', 'Employment Start', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'gender', 'Gender', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'middle_name', 'Middle Name', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'original_effective_date', 'Original Effective Date', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'phone1', 'Phone 1', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'phone2', 'Phone 2', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'plan', 'Plan', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'postalcode', 'Postal Code', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'reason', 'Reason', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'relationship', 'Relationship', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'ssn', 'SSN', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'state', 'State/Province', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'suffix', 'Suffix', false );
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'tobacco_user', 'Tobacco User', false );







CREATE TABLE "MappingColumnHeaders"
(
    "Name" text NOT NULL,
    "Header" text NOT NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "MappingColumnHeaders" OWNER TO :db;

insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'address1', 'Address');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'address1', 'Address 1');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'address2', 'Address 2');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'address1', 'Addr');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'address1', 'Addr 1');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'address2', 'Addr 2');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'annual_salary', 'Annual Salary');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'annual_salary', 'Salary');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'business_unit', 'Business Unit');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'carrier', 'Carrier');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'carrier', 'Carrier Name');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'city', 'City');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'city', 'Municipality');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_end_date', 'Coverage End');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_end_date', 'Coverage End Date');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_end_date', 'Coverage End Dt');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_end_date', 'Coverage Stop');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_end_date', 'Coverage Stop Date');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_end_date', 'Coverage Stop Dt');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_start_date', 'Coverage Start');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_start_date', 'Coverage Start Date');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_start_date', 'Coverage Start Dt');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_tier', 'Coverage Tier');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_tier', 'Tier');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_tier', 'Coverage Level');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'department', 'Department');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'department', 'Dept');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'division', 'Division');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'dob', 'DOB');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'dob', 'Birthdate');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'dob', 'Birthday');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'dob', 'Date Of Birth');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'eid', 'EID');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'eid', 'Employee Id');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'eid', 'Employer Id');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'eid', 'Employee Number');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'email1', 'Email');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'email1', 'Email 1');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'email1', 'Primary Email');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'email2', 'Email 2');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'email2', 'Alternate Email');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'email2', 'Alt Email');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_active', 'Employment Active');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_active', 'Employed');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_end', 'Employment End Date');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_end', 'Employment End Dt');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_end', 'Employment End');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_end', 'Employment Stop Date');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_end', 'Employment Stop Dt');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_end', 'Employment Stop');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_start', 'Employment Start Date');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_start', 'Employment Start Dt');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_start', 'Employment St');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_start', 'Employment Start');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employment_start', 'Date Of Hire');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'first_name', 'First Name');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'first_name', 'First');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'first_name', 'Fname');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'gender', 'Gender');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'last_name', 'Last Name');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'last_name', 'Last');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'last_name', 'Lname');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'middle_name', 'Middle Name');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'middle_name', 'Middle');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'monthly_cost', 'Monthly Cost');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'monthly_cost', 'Cost');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'monthly_cost', 'Total Monthly Cost');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'monthly_cost', 'Total Cost');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'monthly_cost', 'premium');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'original_effective_date', 'Original Effective Date');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'original_effective_date', 'Original Effective Dt');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'plan', 'Plan');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'plan', 'Plan Name');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'plan_type', 'Plan Type');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'plan_type', 'Benefit');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'plan_type', 'Benefit Type');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone1', 'Phone');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone1', 'Telephone');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone1', 'Phone 1');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone1', 'Telephone 1');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone1', 'Primary Phone');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone1', 'Primary Telephone');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone2', 'Phone 2');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone2', 'Telephone 2');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone2', 'Alt Phone');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone2', 'Alternate Phone');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone2', 'Alternate Telephone');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone2', 'Alt Telephone');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'postalcode', 'Postal Code');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'postalcode', 'Postal Code');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'postalcode', 'Postal');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'postalcode', 'Zip');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'postalcode', 'Zip Code');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'reason', 'Reason Code' );
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'reason', 'Rtart Reason' );
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'reason', 'Reason' );
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'relationship', 'Relationship');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'ssn', 'SSN');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'ssn', 'Social Security Number');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'state', 'State/Province');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'state', 'State');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'state', 'Province');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'suffix', 'Suffix');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'tobacco_user', 'Tobacco User');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'tobacco_user', 'Tobacco Use');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'tobacco_user', 'Tobacco');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'volume', 'Volume');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'volume', 'Benefit Amount');


-- Activate the crypto feature for POSTGRES
--CREATE EXTENSION pgcrypto;

-- Create a table to hold all of the information during the wizard
-- process.  This will be temp data only available until the process
-- end or 24 hours has passed.
CREATE TABLE "Wizard"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "UserId" integer NOT NULL
    , "WizardDate" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
    , "StartupComplete" boolean NOT NULL DEFAULT false
    , "UploadComplete" boolean NOT NULL DEFAULT false
    , "ParsingComplete" boolean NOT NULL DEFAULT false
    , "MatchComplete" boolean NOT NULL DEFAULT false
    , "ValidationComplete" boolean NOT NULL DEFAULT false
    , "CorrectionComplete" boolean NOT NULL DEFAULT false
    , "SavingComplete" boolean NOT NULL DEFAULT false
    , "RelationshipComplete" boolean NOT NULL DEFAULT false
    , "PlanReviewComplete" boolean NOT NULL DEFAULT false
    , "ReportGenerationComplete" boolean NOT NULL DEFAULT false
    , "AdjustmentsComplete" boolean NOT NULL DEFAULT true
    , "Finalizing" boolean NOT NULL DEFAULT false
    , "UploadFile" text NULL --bytea NULL
    , CHECK(EXTRACT(TIMEZONE FROM "WizardDate") = '0')    -- Force UTC
    , CONSTRAINT "WizardId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "Wizard" OWNER TO :db;


CREATE TABLE "PlanTypes"
(
  "Name" text NOT NULL,
  "Display" text NOT NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "PlanTypes" OWNER TO :db;

insert into "PlanTypes" ( "Name", "Display" ) values ( 'medical', 'Medical' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'dental', 'Dental' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'vision', 'Vision' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'accident', 'Accident' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'hospital_indemity', 'Hospital Indemity' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'basic_life', 'Basic Life' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'basic_add', 'Basic AD&D' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'supplemental_term_life', 'Supplemental Term Life' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'supplemental_add', 'Supplemental AD&D' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'short_term_disability', 'Short Term Disability (STD)' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'volume_std', 'Vol STD' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'long_term_disability', 'Long Term Disability (LTD)' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'vol_ltd', 'Vol LTD' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'legal', 'Legal' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'flexible_spending_accounts', 'Flexible Spending Accounts' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'health_savings_account', 'Health Savings Account' );
insert into "PlanTypes" ( "Name", "Display" ) values ( 'universal_plan', 'Universal Plan' );


CREATE TABLE "RetroRules"
(
  "Name" text NOT NULL,
  "Display" text NOT NULL,
  "Priority" int NOT NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "RetroRules" OWNER TO :db;
insert into "RetroRules" ( "Name", "Display", "Priority" ) values ( '30', '30 days', 1 );
insert into "RetroRules" ( "Name", "Display", "Priority" ) values ( '60', '60 days', 2 );
insert into "RetroRules" ( "Name", "Display", "Priority" ) values ( '90', '90 days', 3 );

CREATE TABLE "WashRules"
(
  "Name" text NOT NULL,
  "Display" text NOT NULL,
  "Priority" int NOT NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "WashRules" OWNER TO :db;
insert into "WashRules" ( "Name", "Display", "Priority" ) values ( '1', '1st', 1 );
insert into "WashRules" ( "Name", "Display", "Priority" ) values ( '15', '15th', 2 );


-- Advice2Pay Users
CREATE TABLE "Verbiage"
(
  "Key" text NOT NULL,
  "Verbiage" text NOT NULL,
  "Notes" text NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "Verbiage" OWNER TO :db;


INSERT INTO "Verbiage" ( "Key", "Verbiage", "Notes") VALUES ( 'no_column_specified', 'No column was specifed for review.', 'Wizard, column validation error message.' );
INSERT INTO "Verbiage" ( "Key", "Verbiage", "Notes") VALUES ( 'no_data', 'The dataset contained no data.', 'Wizard, column validation error message.' );
INSERT INTO "Verbiage" ( "Key", "Verbiage", "Notes") VALUES ( 'no_plan_type', 'The column you specifed as plan type contained no data.  This column is required.', 'Wizard, plan_type column validation error message.' );
INSERT INTO "Verbiage" ( "Key", "Verbiage", "Notes") VALUES ( 'no_mapped_plan_types', 'You must assign at least one plan type to continue.', 'Wizard: Plan type assignment. Minimum of one mapping required.' );


CREATE TABLE "ImportData"
(
	"Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
	, "ImportDate" date NOT NULL
	, "Finalized" boolean NOT NULL DEFAULT false
	, "RowNumber" integer NOT NULL
	, "EmployeeId" text NULL
	, "PlanType" text NULL
	, "PlanTypeCode" text NULL
	, "FirstName" text NULL
	, "LastName" text NULL
	, "CoverageStartDate" date NULL
	, "CoverageEndDate" date NULL
	, "AnnualSalary" decimal(18,4) NULL
	, "Carrier" text NULL
	, "CoverageTier" text NULL
	, "DateOfBirth" date NULL
	, "MonthlyCost" decimal(18,4) NULL
	, "EmploymentActive" boolean NULL
	, "EmploymentEnd" date NULL
	, "EmploymentStart" date NULL
	, "MiddleName" text NULL
	, "Gender" char(1) NULL
	, "Plan" text NULL
	, "SSN" text NULL
	, "SSNDisplay" text NULL
	, "TobaccoUser" boolean NULL
	, "Volume" decimal(18,4) NULL
	, "Relationship" text NULL
	, "Reason" text NULL
	, "Address1" text NULL
	, "Address2" text NULL
	, "City" text NULL
	, "State" text NULL
	, "PostalCode" text NULL
	, "Phone1" text NULL
	, "Phone2" text NULL
	, "Email1" text NULL
	, "Email2" text NULL
	, "Suffix" text NULL
	, "Division" text NULL
	, "Department" text NULL
	, "BusinessUnit" text NULL
	, "OriginalEffectiveDate" date NULL
	, CHECK( "Gender" in ('m', 'f') )
	, CONSTRAINT "ImportDataId" PRIMARY KEY("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "ImportData" OWNER TO :db;
CREATE INDEX importdata_id_idx on "ImportData"("Id");
CREATE INDEX importdata_companyid_importdate_idx on "ImportData"("CompanyId", "ImportDate");


-- Advice2Pay Users
CREATE TABLE "ProcessQueue"
(
  "Id" bigserial NOT NULL,
  "ExecutionTime" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  "Controller" text NOT NULL,
  "Function" text NULL,
  "Payload" text NULL,
  "QueueTime" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  "StartTime" TIMESTAMP WITH TIME ZONE NULL,
  "EndTime" TIMESTAMP WITH TIME ZONE NULL,
  "Failed" boolean NULL,
  "ErrorMessage" text NULL,
  CHECK(EXTRACT(TIMEZONE FROM "ExecutionTime") = '0'),     -- Force UTC
  CHECK(EXTRACT(TIMEZONE FROM "QueueTime") = '0'),         -- Force UTC
  CHECK(EXTRACT(TIMEZONE FROM "StartTime") = '0'),         -- Force UTC
  CHECK(EXTRACT(TIMEZONE FROM "EndTime") = '0'),           -- Force UTC
  CONSTRAINT "ProcessQueueId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "ProcessQueue" OWNER TO :db;


-- Advice2Pay ValidationErrors
CREATE TABLE "ValidationErrors"
(
  "Id" bigserial NOT NULL,
  "UploadKey" text NOT NULL,
  "CompanyId" integer NOT NULL,
  "RowNumber" integer NOT NULL,
  "ShortCode" text NOT NULL,
  "ErrorMessage" text NOT NULL,
  "ColumnName" text NULL,
  "ColumnNo" integer NULL,
  CONSTRAINT "ValidationErrorsId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "ValidationErrors" OWNER TO :db;


CREATE TABLE "AgeBand"
(
  "Id" bigserial NOT NULL
  , "CompanyAgeBandId" integer not NULL
  , "AgeBandStart" integer NOT NULL
  , "AgeBandEnd" integer NOT NULL
  , CONSTRAINT "AgeBandId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "AgeBand" OWNER TO :db;

CREATE TABLE "PlanTypeAgeBand"
(
  "Id" bigserial NOT NULL
  , "PlanTypeCode" text NOT NULL
  , CONSTRAINT "PlanTypeAgeBandId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "PlanTypeAgeBand" OWNER TO :db;
insert into "PlanTypeAgeBand" ( "PlanTypeCode" ) values ( 'hospital_indemity' );
insert into "PlanTypeAgeBand" ( "PlanTypeCode" ) values ( 'basic_life' );
insert into "PlanTypeAgeBand" ( "PlanTypeCode" ) values ( 'basic_add' );
insert into "PlanTypeAgeBand" ( "PlanTypeCode" ) values ( 'supplemental_term_life' );
insert into "PlanTypeAgeBand" ( "PlanTypeCode" ) values ( 'short_term_disability' );
insert into "PlanTypeAgeBand" ( "PlanTypeCode" ) values ( 'volume_std' );
insert into "PlanTypeAgeBand" ( "PlanTypeCode" ) values ( 'long_term_disability' );
insert into "PlanTypeAgeBand" ( "PlanTypeCode" ) values ( 'vol_ltd' );                         

CREATE TABLE "CompanyAgeBand"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "Carrier" text not NULL
    , "PlanTypeCode" text not NULL
    , "Plan" text not NULL
    , "CoverageTier" text not NULL
    , "Ignored" boolean NOT NULL DEFAULT false
    , CONSTRAINT "CompanyAgeBandId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyAgeBand" OWNER TO :db;


--DROP TABLE "CompanyCarrier";
--DROP TABLE "ReportReview";
--DROP TABLE "FinalizedData";
--ALTER TABLE "ImportData" ADD COLUMN "PlanTypeCode" text NULL;
--ALTER TABLE "CompanyPlanType" DROP COLUMN "CompanyCarrierId";
--delete from "SchemaChangeLog" where "ScriptName" = '0.0.18-FinalizedData.sql';


CREATE TABLE "CompanyCarrier"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "CarrierCode" text NOT NULL
    , CONSTRAINT "CompanyCarrierId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyCarrier" OWNER TO :db;

CREATE TABLE "ReportReview"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "CompanyCarrierId"  integer NOT NULL
    , "Total" decimal(18,4) NULL
    , "Adjustments" decimal(18,4) NOT NULL DEFAULT 0
    , CONSTRAINT "ReportReviewId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "ReportReview" OWNER to :db;


CREATE TABLE "FinalizedData"
(
  "Id" bigserial NOT NULL
  , "ImportDataId" integer NOT NULL
  , "CompanyId" integer NOT NULL
  , "ImportDate" date NOT NULL
  , "Finalized" integer NOT NULL
  , "PlanTypeCode" integer NOT NULL
  , "AgeBandStart" integer NULL
  , "AgeBandEnd" integer NULL
  , CONSTRAINT "FinalizedDataId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "FinalizedData" OWNER TO :db;


-- Remove the PlanTypeCode from ImportData.  That data
-- has moved to FinalizedData now.
ALTER TABLE "ImportData" DROP COLUMN "PlanTypeCode";
ALTER TABLE "CompanyPlanType" ADD COLUMN "CompanyCarrierId" text NULL;


-- Dropping FinalizedData.  We will create a SummaryReport table instead below.
DROP TABLE "FinalizedData";
DROP TABLE "CompanyAgeBand";

-- Reconstruct CompanyCarrier
DROP TABLE "CompanyCarrier";
CREATE TABLE "CompanyCarrier"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "CarrierNormalized" text NOT NULL
    , "UserDescription" text NULL
    , CONSTRAINT "CompanyCarrierId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyCarrier" OWNER TO :db;
CREATE INDEX companycarrier_id_idx on "CompanyCarrier"("Id");
CREATE INDEX companycarrier_companyid_idx on "CompanyCarrier"("CompanyId");

-- Reconstruct CompanyPlanType
DROP TABLE "CompanyPlanType";
CREATE TABLE "CompanyPlanType"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "CarrierId" integer NOT NULL
    , "PlanTypeNormalized" text NOT NULL
    , "UserDescription" text  NULL
    , "PlanTypeCode" text NULL
    , "RetroRule" text NULL
    , "WashRule" text NULL
    , "Ignored" boolean NOT NULL DEFAULT false
    , CONSTRAINT "CompanyPlanTypeId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyPlanType" OWNER TO :db;
CREATE INDEX companyplantype_id_idx on "CompanyPlanType"("Id");
CREATE INDEX companyplantype_companyid_idx on "CompanyPlanType"("CompanyId");

-- Create CompanyPlan
CREATE TABLE "CompanyPlan"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "CarrierId" integer NOT NULL
    , "PlanTypeId" integer not null
    , "PlanNormalized" text NOT NULL
    , "UserDescription" text NULL
    , CONSTRAINT "CompanyPlanId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyPlan" OWNER TO :db;
CREATE INDEX companyplan_id_idx on "CompanyPlan"("Id");
CREATE INDEX companyplan_companyid_idx on "CompanyPlan"("CompanyId");

-- Create CompanyCoverageTier
CREATE TABLE "CompanyCoverageTier"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "CarrierId" integer NOT NULL
    , "PlanTypeId" integer not null
    , "PlanId" integer NOT NULL
    , "CoverageTierNormalized" text NOT NULL
    , "UserDescription" text NULL
    , "AgeBandIgnored" boolean NOT NULL DEFAULT false
    , "TobaccoIgnored" boolean NOT NULL DEFAULT true
    , "GenderIgnored" boolean NOT NULL DEFAULT true
    , CONSTRAINT "CompanyCoverageTierId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyCoverageTier" OWNER TO :db;
CREATE INDEX companycoveragetier_id_idx on "CompanyCoverageTier"("Id");
CREATE INDEX companycoveragetier_companyid_idx on "CompanyCoverageTier"("CompanyId");


DROP TABLE "AgeBand";
CREATE TABLE "AgeBand"
(
  "Id" bigserial NOT NULL
  , "CompanyCoverageTierId" integer not NULL
  , "AgeBandStart" integer NOT NULL
  , "AgeBandEnd" integer NOT NULL
  , CONSTRAINT "AgeBandId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "AgeBand" OWNER TO :db;


CREATE TABLE "WashedData"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "ImportDataId" integer NOT NULL
    , "ImportDate" date NOT NULL
    , "CarrierId" integer NOT NULL
    , "PlanTypeId" integer NOT NULL
    , "PlanId" integer NOT NULL
    , "CoverageTierId" integer NOT NULL
    , "EmployeeIdentifier" text NOT NULL
    , "EmployeeIdentifierType" text NOT NULL
    , "CoverageStartDate" date NOT NULL
    , "CoverageEndDate" date NULL
    , "PlanTypeCode" text NOT NULL
    , "WashRule" text NOT NULL
    , "WashedOutFlg" boolean NULL
    , "WashDescription" text NULL
    , CONSTRAINT "WashedDataId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "WashedData" OWNER TO :db;
CREATE INDEX washeddata_id_idx on "WashedData"("Id");
CREATE INDEX washeddata_companyid_importdate_idx on "WashedData"("CompanyId", "ImportDate");


CREATE TABLE "ReportReviewWarnings"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "ImportDataId" integer NOT NULL
    , "ImportDate" date NOT NULL
    , "Issue" text NOT NULL
    , CONSTRAINT "ReportReviewWarningsId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "ReportReviewWarnings" OWNER TO :db;
CREATE INDEX reportreviewwarnings_id_idx on "ReportReviewWarnings"("Id");
CREATE INDEX reportreviewwarnings_companyid_importdate_idx on "ReportReviewWarnings"("CompanyId", "ImportDate");


-- We no longer need the Reporting Review table.
-- We will leverage the SummaryData table for that report.
DROP TABLE "ReportReview";

CREATE TABLE "SummaryData"
(
    "Id" bigserial NOT NULL
    , "PreparedDate" date NOT NULL
    , "CompanyId" integer NOT NULL
    , "ImportDate" date NOT NULL
    , "CarrierId" integer NOT NULL
    , "PlanTypeId" integer NULL
    , "PlanId" integer NULL
    , "CoverageTierId" integer NULL
    , "AgeBandId" integer NULL
    , "TobaccoUser" boolean NULL
    , "Gender" text NULL
    , "Lives" integer not null default 0
    , "Volume" decimal(18,4) NULL default 0
    , "Premium" decimal(18,4) NULL default 0
    , "AdjustedLives" integer not null default 0
    , "AdjustedVolume" decimal(18,4) NULL default 0
    , "AdjustedPremium" decimal(18,4) NULL default 0
    , CONSTRAINT "SummaryDataId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "SummaryData" OWNER TO :db;
CREATE INDEX summarydata_id_idx on "SummaryData"("Id");
CREATE INDEX summarydata_companyid_importdate_idx on "SummaryData"("CompanyId", "ImportDate");


CREATE TABLE "Age"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "ImportDataId" integer NOT NULL
    , "ImportDate" date NOT NULL
    , "CarrierId" integer NOT NULL
    , "PlanTypeId" integer NOT NULL
    , "PlanId" integer NOT NULL
    , "CoverageTierId" integer NOT NULL
    , "Age" integer NOT NULL
    , "AgeTypeId" integer NOT NULL
    , "AgeDescription" text NULL
    , CONSTRAINT "AgeId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "Age" OWNER TO :db;
CREATE INDEX age_id_idx on "Age"("Id");
CREATE INDEX age_companyid_importdate_idx on "Age"("CompanyId", "ImportDate");

CREATE TABLE "AgeType"
(
    "Id" bigserial NOT NULL
    , "Name" text NOT NULL
    , "Description" text NOT NULL
    , CONSTRAINT "AgeTypeId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "AgeType" OWNER TO :db;
insert into "AgeType" ( "Name", "Description" ) values ( 'anniversary', 'Anniversary' );
insert into "AgeType" ( "Name", "Description" ) values ( 'washed', 'Washed' );
insert into "AgeType" ( "Name", "Description" ) values ( 'actual', 'Actual' );


alter table "AgeType" add column "Enabled" boolean NULL;
update "AgeType" set "Enabled" = true;
update "AgeType" set "Enabled" = false where "Name" = 'actual';
alter table "AgeBand" add column "AnniversaryMonth" integer NULL;
alter table "AgeBand" add column "AnniversaryDay" integer NULL;
alter table "Age" add column "AgeOn" text NULL;
ALTER TABLE "Age" ALTER COLUMN "Age" DROP NOT NULL;

-- Create the function last_day so we can easily calculate the last day of
-- the month for any given date.
CREATE OR REPLACE FUNCTION last_day(date)
RETURNS date AS
$$
SELECT (date_trunc('MONTH', $1) + INTERVAL '1 MONTH - 1 day')::date;
$$ LANGUAGE 'sql'
IMMUTABLE STRICT;


alter table "AgeBand" add column "AgeTypeId" integer NULL;


-- Remove PlanTypeAgeBand.  We will move the attributes for AgeBand into
-- the PlanTypes table.  ( Along with Tobacco too.)
DROP TABLE IF EXISTS "PlanTypeAgeBand";
ALTER TABLE "PlanTypes" add column "AgeBand" boolean NULL;
ALTER TABLE "PlanTypes" add column "Tobacco" boolean NULL;
update "PlanTypes" set "AgeBand" = true, "Tobacco" = true where "Name" = 'hospital_indemity';
update "PlanTypes" set "AgeBand" = true, "Tobacco" = false where "Name" = 'basic_life';
update "PlanTypes" set "AgeBand" = true, "Tobacco" = false where "Name" = 'basic_add';
update "PlanTypes" set "AgeBand" = true, "Tobacco" = true where "Name" = 'supplemental_term_life';
update "PlanTypes" set "AgeBand" = true, "Tobacco" = false where "Name" = 'short_term_disability';
update "PlanTypes" set "AgeBand" = true, "Tobacco" = false where "Name" = 'volume_std';
update "PlanTypes" set "AgeBand" = true, "Tobacco" = false where "Name" = 'long_term_disability';
update "PlanTypes" set "AgeBand" = true, "Tobacco" = false where "Name" = 'vol_ltd';
update "PlanTypes" set "AgeBand" = false, "Tobacco" = true where "Name" = 'medical';
update "PlanTypes" set "AgeBand" = false, "Tobacco" = true where "Name" = 'supplemental_add';
update "PlanTypes" set "AgeBand" = false, "Tobacco" = false where "Name" = 'dental';
update "PlanTypes" set "AgeBand" = false, "Tobacco" = false where "Name" = 'vision';
update "PlanTypes" set "AgeBand" = false, "Tobacco" = false where "Name" = 'accident';
update "PlanTypes" set "AgeBand" = false, "Tobacco" = false where "Name" = 'legal';
update "PlanTypes" set "AgeBand" = false, "Tobacco" = false where "Name" = 'flexible_spending_accounts';
update "PlanTypes" set "AgeBand" = false, "Tobacco" = false where "Name" = 'health_savings_account';
update "PlanTypes" set "AgeBand" = false, "Tobacco" = false where "Name" = 'universal_plan';

-- To do the Age calculation, we need to do updates with joins.
-- To make things easier, I want to goin only one level table on the update.
-- To do that, I need to pull in a few more datapoints that already exist, but
-- in other locations.
ALTER TABLE "Age" add column "WashRule" integer NULL;
ALTER TABLE "Age" add column "DateOfBirth" date NULL;

-- Gender is no longer an attribute for summerization.
ALTER TABLE "SummaryData" DROP COLUMN "Gender";
ALTER TABLE "CompanyCoverageTier" DROP COLUMN "GenderIgnored";

-- The default value on TobaccoIgnored was true, make it false.
ALTER TABLE "CompanyCoverageTier" DROP COLUMN "TobaccoIgnored";
ALTER TABLE "CompanyCoverageTier" ADD COLUMN "TobaccoIgnored" boolean NOT NULL DEFAULT false;


CREATE TABLE "CompanyReport"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "ImportDate" date NOT NULL
    , "ReportTypeId" integer NOT NULL
    , "CarrierId" integer NOT NULL
    , CONSTRAINT "CompanyReportId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyReport" OWNER TO :db;
CREATE INDEX companyreport_id_idx on "CompanyReport"("Id");
CREATE INDEX companyreport_companyid_importdate_idx on "Age"("CompanyReport", "ImportDate");

CREATE TABLE "ReportType"
(
  "Id" integer NOT NULL,
  "Name" text NOT NULL,
  "Display" text NOT NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "ReportType" OWNER TO :db;

insert into "ReportType" ("Id", "Name", "Display") values ( 1, 'summary', 'Summary');
insert into "ReportType" ("Id", "Name", "Display") values ( 2, 'detail', 'Detail');


-- CompanyLife
-- This table will store individuals that we identify from the import data.
CREATE TABLE "CompanyLife"
(
	"Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
	, "LifeKey" text NULL
	, "FirstName" text NOT NULL
	, "LastName" text NOT NULL
	, "MiddleName" text NULL
    , "EmployeeId" text NULL
	, "SSN" text NULL
	, "SSNDisplay" text NULL
	, "DateOfBirth" date NULL
	, "Relationship" text NULL
	, CONSTRAINT "CompanyLifeId" PRIMARY KEY("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyLife" OWNER TO :db;
CREATE INDEX companylife_id_idx on "CompanyLife"("Id");
CREATE INDEX companylife_companyid_idx on "CompanyLife"("CompanyId");

-- LifeData
-- This table will link the ImportData and the CompanyLife tables.
CREATE TABLE "LifeData"
(
	"Id" bigserial NOT NULL
	, "CompanyId" integer NOT NULL
	, "ImportDataId" integer NOT NULL
	, "ImportDate" date NOT NULL
	, "LifeId" integer NULL
	, CONSTRAINT "LifeDataId" PRIMARY KEY ("Id")
);
ALTER TABLE "LifeData" OWNER TO :db;
CREATE INDEX lifedata_id_idx on "LifeData"("Id");
CREATE INDEX lifedata_companyid_importdate_idx on "LifeData"("CompanyId", "ImportDate");

-- Alter the WashedData so it references the life table and no longer stores life data.
ALTER TABLE "WashedData" ADD COLUMN "LifeId" integer NULL;
ALTER TABLE "WashedData" DROP COLUMN "EmployeeIdentifier";
ALTER TABLE "WashedData" DROP COLUMN "EmployeeIdentifierType";


-- ADJUSTMENT TYPE
-- Table used to store different types of adjustments.
CREATE TABLE "AdjustmentType"
(
  "Id" integer NOT NULL
  , "Name" text NOT NULL
  , "Display" text NOT NULL
  , CONSTRAINT "AdjustmentTypeId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "AdjustmentType" OWNER TO :db;
insert into "AdjustmentType" ("Id", "Name", "Display") values ( 1, 'manual', 'Manual Adjustment');
insert into "AdjustmentType" ("Id", "Name", "Display") values ( 2, 'add', 'Retro Add');
insert into "AdjustmentType" ("Id", "Name", "Display") values ( 3, 'term', 'Retro Term');
insert into "AdjustmentType" ("Id", "Name", "Display") values ( 4, 'change-effective-dt', 'Retro Change');
insert into "AdjustmentType" ( "Id", "Name", "Display" ) values ( 5, 'change-employer-cost', 'Retro Change' );
insert into "AdjustmentType" ( "Id", "Name", "Display" ) values ( 6, 'change-coverage-tier', 'Retro Change' );


-- RETRO DATA
-- Table used to store line item retro logic for an import.  To aid in
-- the cacluation of automatic adjustments.
CREATE TABLE "RetroData"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "ImportDataId" integer NOT NULL
    , "ImportDate" date NOT NULL
    , "PlanTypeCode" text not null
    , "LifeId" integer NOT NULL
    , "CarrierId" integer NOT NULL
    , "PlanTypeId" integer NOT NULL
    , "PlanId" integer NOT NULL
    , "CoverageTierId" integer NOT NULL
    , "CoverageTierKey" text NULL
    , "CoverageStartDate" date NULL
    , "CoverageEndDate" date NULL
    , "MonthlyCost" decimal(18,4) NULL
    , "Volume" decimal(18,4) NULL
    , "Before-CoverageTierKey" text NULL
    , "Before-CoverageStartDate" date NULL -- actual Before-CoverateStartDate if we can boil it down to a single value.
    , "Before-CoverageEndDate" date NULL
    , "Before-MonthlyCost" text NULL --, "Before-MonthlyCost" decimal(18,4) NULL
    , "Before-Volume" text NULL --, "Before-Volume" decimal(18,4) NULL
    , "AdjustmentType" integer NULL
    , "RetroDescription" text NULL
    , CONSTRAINT "RetroDataId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "RetroData" OWNER TO :db;
CREATE INDEX retrodata_id_idx on "RetroData"("Id");
CREATE INDEX retrodata_companyid_importdate_idx on "RetroData"("CompanyId", "ImportDate");


-- Moving RetroRules to months rather than days to support automatic adjustments.
-- This includes a migration of existing retro rules.
update "RetroRules" set "Name"='1', "Display"='1 Month' where "Name" = '30';
update "RetroRules" set "Name"='2', "Display"='2 Months' where "Name" = '60';
update "RetroRules" set "Name"='3', "Display"='3 Months' where "Name" = '90';
update "CompanyPlanType" set "RetroRule"='1' where "RetroRule"='30';
update "CompanyPlanType" set "RetroRule"='2' where "RetroRule"='60';
update "CompanyPlanType" set "RetroRule"='3' where "RetroRule"='90';

-- AUTOMATIC ADJUSTMENT
CREATE TABLE "AutomaticAdjustment"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "ImportDate" date NOT NULL
    , "TargetDate" date NOT NULL
    , "ParentRetroDataId" integer NULL
    , "RetroDataId" integer NOT NULL
    , "LifeId" integer NOT NULL
    , "CarrierId" integer NOT NULL
    , "PlanTypeId" integer NOT NULL
    , "PlanId" integer NOT NULL
    , "CoverageTierId" integer NOT NULL
    , "AdjustmentType" integer NULL
    , "Volume" decimal(18,4) NULL
    , "MonthlyCost" decimal(18,4) NULL
    , "Memo" text NULL
    , CONSTRAINT "AutomaticAdjustmentId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "AutomaticAdjustment" OWNER TO :db;
CREATE INDEX automaticadjustment_id_idx on "AutomaticAdjustment"("Id");
CREATE INDEX automaticadjustment_companyid_importdate_idx on "AutomaticAdjustment"("CompanyId", "ImportDate");


-- MANUAL ADJUSTMENT
CREATE TABLE "ManualAdjustment"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "ImportDate" date NOT NULL
    , "CarrierId" integer NOT NULL
    , "PlanTypeId" integer NULL
    , "PlanId" integer NULL
    , "CoverageTierId" integer NULL
    , "LifeId" integer NULL
    , "AdjustmentType" integer NOT NULL
    , "Amount" decimal(18,4) NOT NULL
    , "Memo" text NOT NULL
    , CONSTRAINT "ManualAdjustmentId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "ManualAdjustment" OWNER TO :db;
CREATE INDEX manualadjustment_id_idx on "ManualAdjustment"("Id");
CREATE INDEX manualadjustment_companyid_importdate_idx on "ManualAdjustment"("CompanyId", "ImportDate");


-- CompanyRelationship
-- Here are all the relationships that we have seen for a given company
-- and what they last picked as a mapping for that relationship.
CREATE TABLE "CompanyRelationship"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "RelationshipNormalized" text NOT NULL
    , "UserDescription" text NOT NULL
    , "RelationshipCode" text NULL
    , CONSTRAINT "CompanyRelationshipId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyRelationship" OWNER TO :db;

-- Relationship
-- These are the relationships that a2p will code against.
CREATE TABLE "Relationship"
(
    "Code" text NOT NULL
    , "Description" text NOT NULL
    , CONSTRAINT "RelationshipCode" PRIMARY KEY ("Code")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "Relationship" OWNER TO :db;
insert into "Relationship" ( "Code", "Description" ) values ( 'spouse', 'Spouse' );
insert into "Relationship" ( "Code", "Description" ) values ( 'dependent', 'Dependent' );
insert into "Relationship" ( "Code", "Description" ) values ( 'employee', 'Employee' );

-- RelationshipMapping
-- When presenting a list of relationships that need to be mapped,
-- we will auto-select the relationship for them if we can tell what they
-- would probably pick.  This table holds user descriptions and what we
-- think they should pick.
CREATE TABLE "RelationshipMapping"
(
    "RelationshipCode" text NOT NULL
    , "UserDescription" text NOT NULL
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "RelationshipMapping" OWNER TO :db;
insert into "RelationshipMapping" ( "RelationshipCode", "UserDescription") values ('spouse', 'Spouse');
insert into "RelationshipMapping" ( "RelationshipCode", "UserDescription") values ('dependent', 'Dependent');
insert into "RelationshipMapping" ( "RelationshipCode", "UserDescription") values ('dependent', 'Child');
insert into "RelationshipMapping" ( "RelationshipCode", "UserDescription") values ('employee', 'Employee');

-- RelationshipData
-- When evaluating relationships, this table will hold altered import data
-- columns generated based on the relationship processing rules.
CREATE TABLE "RelationshipData"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "ImportDataId" integer NOT NULL
    , "ImportDate" date NOT NULL
    , "RelationshipCode" text NOT NULL
    , "MonthlyCost" decimal(18,4) NULL
    , "Volume" decimal(18,4) NULL
    , "Memo" text NULL
    , CONSTRAINT "RelationshipDataId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "RelationshipData" OWNER TO :db;
CREATE INDEX relationshipdata_id_idx on "RelationshipData"("Id");
CREATE INDEX relationshipdata_companyid_importdate_idx on "RelationshipData"("CompanyId", "ImportDate");


insert into "PlanTypes" ( "Name","Display","AgeBand","Tobacco" ) values ( 'critical_illness', 'Critical Illness', true, true );


CREATE TABLE "HistoryChangeToCompany"
(
    "Id" bigserial NOT NULL
    , "UserId" integer NOT NULL
    , "CompanyId" integer NOT NULL
    , "ChangedToDate" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
    , CONSTRAINT "HistoryChangeToCompanyId" PRIMARY KEY ("Id")
    , CHECK(EXTRACT(TIMEZONE FROM "ChangedToDate") = '0')    -- Force UTC
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "HistoryChangeToCompany" OWNER TO :db;
CREATE INDEX historychangetocompany_id_idx on "HistoryChangeToCompany"("Id");
CREATE INDEX historychangetocompany_userid_idx on "HistoryChangeToCompany"("UserId");
CREATE INDEX historychangetocompany_userid_companyid_idx on "HistoryChangeToCompany"("UserId", "CompanyId");


CREATE TABLE "HistoryFailedJob"
(
    "Id" bigserial NOT NULL
    , "UserId" integer NOT NULL
    , "JobId" integer NOT NULL
    , "ClearedDate" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
    , CONSTRAINT "HistoryFailedJobId" PRIMARY KEY ("Id")
    , CHECK(EXTRACT(TIMEZONE FROM "ClearedDate") = '0')    -- Force UTC
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "HistoryFailedJob" OWNER TO :db;
CREATE INDEX historyfailedjob_id_idx on "HistoryFailedJob"("Id");
CREATE INDEX historyfailedjob_userid_idx on "HistoryFailedJob"("UserId");
CREATE INDEX historyfailedjob_userid_jobid_idx on "HistoryFailedJob"("UserId", "JobId");


CREATE TABLE "SummaryDataYTD"
(
    "Id" bigserial NOT NULL
    , "CompanyId" integer NOT NULL
    , "ImportDate" date NOT NULL
    , "CarrierId" integer NOT NULL
    , "PlanTypeId" integer NULL
    , "PlanId" integer NULL
    , "CoverageTierId" integer NULL
    , "AgeBandId" integer NULL
    , "TobaccoUser" boolean NULL
    , "PremiumYTD" decimal(18,4) NULL default 0
    , "AdjustedPremiumYTD" decimal(18,4) NULL default 0
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "SummaryDataYTD" OWNER TO :db;
CREATE INDEX summarydataytd_id_idx on "SummaryDataYTD"("Id");
CREATE INDEX summarydataytd_companyid_importdate_idx on "SummaryDataYTD"("CompanyId", "ImportDate");
CREATE INDEX summarydataytd_companyid_importdate_carrierid_plantypeid_idx on "SummaryDataYTD"("CompanyId", "ImportDate", "CarrierId", "PlanTypeId");
CREATE INDEX summarydataytd_companyid_importdate_all_unique_ids_idx on "SummaryDataYTD"("CompanyId", "ImportDate", "CarrierId", "PlanId", "PlanTypeId", "CoverageTierId", "AgeBandId", "TobaccoUser");


-- Add the age calculation type of "issued".
INSERT into "AgeType" ( "Name", "Description", "Enabled" ) values ( 'issued', 'Issued', true );

-- Add the coverage start date to the AGE table so we can calculate the age for the issued type.
ALTER TABLE "Age" add column "IssuedDate" date NULL;
ALTER TABLE "Age" add column "AnniversaryDay" integer NULL;
ALTER TABLE "Age" add column "AnniversaryMonth" integer NULL;




DROP TABLE "Wizard";

CREATE TABLE "Wizard"
(
  "Id" bigserial NOT NULL,
  "CompanyId" integer NOT NULL,
  "UserId" integer NOT NULL,
  "WizardDate" timestamp with time zone NOT NULL DEFAULT now(),
  "StartupComplete" boolean NOT NULL DEFAULT false,
  "UploadComplete" boolean NOT NULL DEFAULT false,
  "ParsingComplete" boolean NOT NULL DEFAULT false,
  "MatchComplete" boolean NOT NULL DEFAULT false,
  "ValidationComplete" boolean NOT NULL DEFAULT false,
  "CorrectionComplete" boolean NOT NULL DEFAULT false,
  "SavingComplete" boolean NOT NULL DEFAULT false,
  "RelationshipComplete" boolean NOT NULL DEFAULT false,
  "LivesComplete" boolean NOT NULL DEFAULT false,
  "PlanReviewComplete" boolean NOT NULL DEFAULT false,
  "ReportGenerationComplete" boolean NOT NULL DEFAULT false,
  "AdjustmentsComplete" boolean NOT NULL DEFAULT true,
  "Finalizing" boolean NOT NULL DEFAULT false,
  "UploadFile" text,
  CHECK(EXTRACT(TIMEZONE FROM "WizardDate") = '0'),    -- Force UTC
  CONSTRAINT "WizardId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "Wizard" OWNER TO advice2pay;


ALTER TABLE "CompanyLife" add column "Enabled" boolean default true;
ALTER TABLE "LifeData" add column "NewLifeFlg" boolean default false;
ALTER TABLE "LifeData" add column "EIDExistedLastMonthFlg" boolean;


CREATE TABLE "CompanyLifeDiff"
(
  "Id" bigserial NOT NULL,
  "CompanyId" integer NOT NULL,
  "ImportDate" date NOT NULL,
  "LifeId" integer NOT NULL,
  "EmployeeId" text NOT NULL,
  "UpdatedLifeId" integer,
  "NewLifeFlg" boolean NOT NULL DEFAULT false,
  "SavedFlg" boolean NOT NULL DEFAULT false,
  CONSTRAINT "CompanyLifeDiffId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyLifeDiff" OWNER TO advice2pay;

CREATE TABLE "CompanyLifeResearch"
(
  "Id" bigserial NOT NULL,
  "CompanyId" integer NOT NULL,
  "ImportDate" date not NULL,
  "EmployeeId" text NOT NULL,
  "LifeDataId" integer not NULL,
  "PreviousLifeKey" text NOT NULL,
  "CurrentLifeKey" text,
  CONSTRAINT "CompanyLifeResearchId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyLifeResearch" OWNER TO advice2pay;


CREATE TABLE "CompanyLifeCompare"
(
  "Id" bigserial NOT NULL,
  "CompanyId" integer NOT NULL,
  "ImportDate" date not NULL,
  "LifeId" integer NOT NULL,
  "LifeDataId" integer NOT NULL,
  "IsNewLife" boolean,
  "UpdatesLifeId" integer,
  "RollbackLifeKey" text NULL,
  "RollbackFirstName" text NULL,
  "RollbackLastName" text NULL,
  "RollbackMiddleName" text NULL,
  "RollbackEmployeeId" text NULL,
  "RollbackSSN" text NULL,
  "RollbackSSNDisplay" text NULL,
  "RollbackDateOfBirth" date NULL,
  "RollbackRelationship" text NULL,
  CONSTRAINT "CompanyLifeCompareId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyLifeCompare" OWNER TO advice2pay;


ALTER table "CompanyPlanType" ADD COLUMN "PlanAnniversaryMonth" integer NULL;

-- Add a new adjustment type that we can use to update automatic adjustments
-- that have been calculated, but need to be ignored due to their plan anniversary settings.
INSERT into "AdjustmentType" ( "Id", "Name", "Display" ) values (7, 'plan-annniversary-ignored', 'Ignored');


-- Add storage columns on the CompanyPlan to keep track of the PlanFee settings
-- the user specifies.
ALTER TABLE "CompanyPlan" ADD COLUMN "ASOFee" decimal(18,4) NULL;
ALTER TABLE "CompanyPlan" ADD COLUMN "ASOFeeCarrierId" integer null;
ALTER TABLE "CompanyPlan" ADD COLUMN "ASOFeePlanTypeId" integer null;
ALTER TABLE "CompanyPlan" ADD COLUMN "StopLossFee" decimal(18,4) NULL;
ALTER TABLE "CompanyPlan" ADD COLUMN "StopLossFeeCarrierId" integer null;
ALTER TABLE "CompanyPlan" ADD COLUMN "StopLossFeePlanTypeId" integer null;
ALTER TABLE "CompanyPlan" ADD COLUMN "PremiumEquivalent" boolean NOT NULL DEFAULT false;

-- Add a table we can use to summerize PremiumEquivalent data outside of the
-- SummaryData table.
CREATE TABLE "SummaryDataPremiumEquivalent"
(
    "Id" bigserial NOT NULL
    , "PreparedDate" date NOT NULL
    , "CompanyId" integer NOT NULL
    , "ImportDate" date NOT NULL
    , "ParentCarrierId" integer NOT NULL
    , "CarrierId" integer NOT NULL
    , "PlanTypeId" integer NULL
    , "PlanId" integer NULL
    , "CoverageTierId" integer NULL
    , "AgeBandId" integer NULL
    , "TobaccoUser" boolean NULL
    , "Gender" text NULL
    , "Lives" integer not null default 0
    , "Volume" decimal(18,4) NULL default 0
    , "Premium" decimal(18,4) NULL default 0
    , "AdjustedLives" integer not null default 0
    , "AdjustedVolume" decimal(18,4) NULL default 0
    , "AdjustedPremium" decimal(18,4) NULL default 0
    , CONSTRAINT "SummaryDataPremiumEquivalentId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "SummaryDataPremiumEquivalent" OWNER TO :db;
CREATE INDEX summarydatape_id_idx on "SummaryDataPremiumEquivalent"("Id");
CREATE INDEX summarydatape_companyid_importdate_idx on "SummaryDataPremiumEquivalent"("CompanyId", "ImportDate");

-- Add PremiumEquivalent reports.
insert into "ReportType" ("Id", "Name", "Display") values ( 3, 'pe_summary', 'Premium Equivalent Summary');
insert into "ReportType" ("Id", "Name", "Display") values ( 4, 'pe_detail', 'Premium Equivalent Detail');

-- Add a column to PlanTypes so we can keep track of which plantypes
-- allow PlanFees.
alter TABLE "PlanTypes" ADD COLUMN "PlanFees" boolean default false;
update "PlanTypes" set "PlanFees" = true where "Name" = 'medical';
update "PlanTypes" set "PlanFees" = true where "Name" = 'dental';
update "PlanTypes" set "PlanFees" = true where "Name" = 'vision';
update "PlanTypes" set "PlanFees" = true where "Name" = 'flexible_spending_accounts';
update "PlanTypes" set "PlanFees" = true where "Name" = 'health_savings_account';
update "PlanTypes" set "PlanFees" = true where "Name" = 'accident';
update "PlanTypes" set "PlanFees" = true where "Name" = 'critical_illness';
update "PlanTypes" set "PlanFees" = true where "Name" = 'hospital_indemity';


-- Create PlanTypes for each of the PlanFees we may generate.
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'medical_aso', 'Medical ASO', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'medical_stoploss', 'Medical Stop Loss', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'dental_aso', 'Dental ASO', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'dental_stoploss', 'Dental Stop Loss', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'vision_aso', 'Vision ASO', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'vision_stoploss', 'Vision Stop Loss', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'flexible_spending_accounts_aso', 'Flexible Spending Accounts ASO', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'flexible_spending_accounts_stoploss', 'Flexible Spending Accounts Stop Loss', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'health_savings_account_aso', 'Health Savings ASO', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'health_savings_account_stoploss', 'Health Savings Stop Loss', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'accident_aso', 'Accident ASO', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'accident_stoploss', 'Accident Stop Loss', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'critical_illness_aso', 'Critical Illness ASO', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'critical_illness_stoploss', 'Critical Illness Stop Loss', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'hospital_indemity_aso', 'Hospital Indemity ASO', false, false, false );
insert into "PlanTypes" ( "Name", "Display", "AgeBand", "Tobacco", "PlanFees" ) values ( 'hospital_indemity_stoploss', 'Hospital Indemity Stop Loss', false, false, false );


-- Function: clone_schema(text, text)

-- DROP FUNCTION clone_schema(text, text);

CREATE OR REPLACE FUNCTION clone_schema(
    source_schema text,
    dest_schema text,
    include_recs boolean)
  RETURNS void AS
$BODY$

--  This function will clone all sequences, tables, data, views & functions from any existing schema to a new one
-- SAMPLE CALL:
-- SELECT clone_schema('public', 'new_schema', TRUE);

DECLARE
  src_oid          oid;
  tbl_oid          oid;
  func_oid         oid;
  object           text;
  buffer           text;
  srctbl           text;
  default_         text;
  column_          text;
  qry              text;
  dest_qry         text;
  v_def            text;
  seqval           bigint;
  sq_last_value    bigint;
  sq_max_value     bigint;
  sq_start_value   bigint;
  sq_increment_by  bigint;
  sq_min_value     bigint;
  sq_cache_value   bigint;
  sq_log_cnt       bigint;
  sq_is_called     boolean;
  sq_is_cycled     boolean;
  sq_cycled        char(10);

BEGIN

-- Check that source_schema exists
  SELECT oid INTO src_oid
    FROM pg_namespace
   WHERE nspname = quote_ident(source_schema);
  IF NOT FOUND
    THEN
    RAISE NOTICE 'source schema % does not exist!', source_schema;
    RETURN ;
  END IF;

  -- Check that dest_schema does not yet exist
  PERFORM nspname
    FROM pg_namespace
   WHERE nspname = quote_ident(dest_schema);
  IF FOUND
    THEN
    RAISE NOTICE 'dest schema % already exists!', dest_schema;
    RETURN ;
  END IF;

  EXECUTE 'CREATE SCHEMA ' || quote_ident(dest_schema) ;

  -- Create sequences
  -- TODO: Find a way to make this sequence's owner is the correct table.
  FOR object IN
    SELECT sequence_name::text
      FROM information_schema.sequences
     WHERE sequence_schema = quote_ident(source_schema)
  LOOP
    EXECUTE 'CREATE SEQUENCE ' || quote_ident(dest_schema) || '.' || quote_ident(object);
    srctbl := quote_ident(source_schema) || '.' || quote_ident(object);

    EXECUTE 'SELECT last_value, max_value, start_value, increment_by, min_value, cache_value, log_cnt, is_cycled, is_called
              FROM ' || quote_ident(source_schema) || '.' || quote_ident(object) || ';'
              INTO sq_last_value, sq_max_value, sq_start_value, sq_increment_by, sq_min_value, sq_cache_value, sq_log_cnt, sq_is_cycled, sq_is_called ;

    IF sq_is_cycled
      THEN
        sq_cycled := 'CYCLE';
    ELSE
        sq_cycled := 'NO CYCLE';
    END IF;

    EXECUTE 'ALTER SEQUENCE '   || quote_ident(dest_schema) || '.' || quote_ident(object)
            || ' INCREMENT BY ' || sq_increment_by
            || ' MINVALUE '     || sq_min_value
            || ' MAXVALUE '     || sq_max_value
            || ' START WITH '   || sq_start_value
            || ' RESTART '      || sq_min_value
            || ' CACHE '        || sq_cache_value
            || sq_cycled || ' ;' ;

    buffer := quote_ident(dest_schema) || '.' || quote_ident(object);
    IF include_recs
        THEN
            EXECUTE 'SELECT setval( ''' || buffer || ''', ' || sq_last_value || ', ' || sq_is_called || ');' ;
    ELSE
            EXECUTE 'SELECT setval( ''' || buffer || ''', ' || sq_start_value || ', ' || sq_is_called || ');' ;
    END IF;

  END LOOP;

-- Create tables
  FOR object IN
    SELECT TABLE_NAME::text
      FROM information_schema.tables
     WHERE table_schema = quote_ident(source_schema)
       AND table_type = 'BASE TABLE'

  LOOP
    buffer := dest_schema || '.' || quote_ident(object);
    EXECUTE 'CREATE TABLE ' || buffer || ' (LIKE ' || quote_ident(source_schema) || '.' || quote_ident(object)
        || ' INCLUDING ALL)';

    IF include_recs
      THEN
      -- Insert records from source table
      EXECUTE 'INSERT INTO ' || buffer || ' SELECT * FROM ' || quote_ident(source_schema) || '.' || quote_ident(object) || ';';
    END IF;

    FOR column_, default_ IN
      SELECT column_name::text,
             REPLACE(column_default::text, source_schema, dest_schema)
        FROM information_schema.COLUMNS
       WHERE table_schema = dest_schema
         AND TABLE_NAME = object
         AND column_default LIKE 'nextval(%' || quote_ident(source_schema) || '%::regclass)'
    LOOP
      EXECUTE 'ALTER TABLE ' || buffer || ' ALTER COLUMN ' || column_ || ' SET DEFAULT ' || default_;
    END LOOP;

  END LOOP;

--  add FK constraint
  FOR qry IN
    SELECT 'ALTER TABLE ' || quote_ident(dest_schema) || '.' || quote_ident(rn.relname)
                          || ' ADD CONSTRAINT ' || quote_ident(ct.conname) || ' ' || pg_get_constraintdef(ct.oid) || ';'
      FROM pg_constraint ct
      JOIN pg_class rn ON rn.oid = ct.conrelid
     WHERE connamespace = src_oid
       AND rn.relkind = 'r'
       AND ct.contype = 'f'

    LOOP
      EXECUTE qry;

    END LOOP;


-- Create views
  FOR object IN
    SELECT table_name::text,
           view_definition
      FROM information_schema.views
     WHERE table_schema = quote_ident(source_schema)

  LOOP
    buffer := dest_schema || '.' || quote_ident(object);
    SELECT view_definition INTO v_def
      FROM information_schema.views
     WHERE table_schema = quote_ident(source_schema)
       AND table_name = quote_ident(object);

    EXECUTE 'CREATE OR REPLACE VIEW ' || buffer || ' AS ' || v_def || ';' ;

  END LOOP;

-- Create functions
  FOR func_oid IN
    SELECT oid
      FROM pg_proc
     WHERE pronamespace = src_oid

  LOOP
    SELECT pg_get_functiondef(func_oid) INTO qry;
    SELECT replace(qry, source_schema, dest_schema) INTO dest_qry;
    EXECUTE dest_qry;

  END LOOP;

  RETURN;

END;

$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
--ALTER FUNCTION clone_schema(text, text, boolean)
--  OWNER TO advice2pay;


DROP TABLE "Wizard";
CREATE TABLE "Wizard"
(
  "Id" bigserial NOT NULL,
  "CompanyId" integer NOT NULL,
  "UserId" integer NOT NULL,
  "WizardDate" timestamp with time zone NOT NULL DEFAULT now(),
  "StartupComplete" boolean NOT NULL DEFAULT false,
  "UploadComplete" boolean NOT NULL DEFAULT false,
  "ParsingComplete" boolean NOT NULL DEFAULT false,
  "MatchComplete" boolean NOT NULL DEFAULT false,
  "ValidationComplete" boolean NOT NULL DEFAULT false,
  "CorrectionComplete" boolean NOT NULL DEFAULT false,
  "SavingComplete" boolean NOT NULL DEFAULT false,
  "RelationshipComplete" boolean NOT NULL DEFAULT false,
  "LivesComplete" boolean NOT NULL DEFAULT false,
  "PlanReviewComplete" boolean NOT NULL DEFAULT false,
  "ClarificationsComplete" boolean NOT NULL DEFAULT true,
  "ReportGenerationComplete" boolean NOT NULL DEFAULT false,
  "AdjustmentsComplete" boolean NOT NULL DEFAULT true,
  "Finalizing" boolean NOT NULL DEFAULT false,
  "UploadFile" text,
  CHECK(EXTRACT(TIMEZONE FROM "WizardDate") = '0'),    -- Force UTC
  CONSTRAINT "WizardId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "Wizard" OWNER TO advice2pay;

CREATE TABLE "RetroDataLifeEvent"
(
  "Id" bigserial NOT NULL,
  "CompanyId" integer NOT NULL,
  "ImportDate" date NOT NULL,
  "RetroDataId" integer NOT NULL,
  "CoverageStartDate" date NULL,
  "Before-CoverageStartDateList" text NULL,
  "CarrierId" integer NULL,
  "PlanTypeId" integer NULL,
  "PlanId" integer NULL,
  "Before-PlanId" integer NULL,
  "CoverageTierId" integer NULL,
  "Before-CoverageTierIdList" text NULL,
  "MonthlyCost" decimal(18,4) NULL,
  "Before-MonthlyCost" text NULL,
  "Volume" decimal(18,4) NULL,
  "Before-Volume" text NULL,
  "LifeEvent" boolean default NULL,
  "AutoSelected" boolean default false,
  CONSTRAINT "RetroDataLifeEventId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "RetroDataLifeEvent" OWNER TO advice2pay;


CREATE TABLE "LifeEventCompare"
(
  "Id" bigserial NOT NULL,
  "CompanyId" integer NOT NULL,
  "ImportDate" date NOT NULL,
  "LifeEvent" boolean default NULL,
  "CarrierId" integer NULL,
  "PlanTypeId" integer NULL,
  "PlanId" integer NULL,
  "CoverageTierId" integer NULL,
  "CoverageStartDate" date NULL,
  "Before-CoverageStartDateList" text NULL,
  "MonthlyCost" decimal(18,4) NULL,
  "Before-MonthlyCost" text NULL,
  "Volume" decimal(18,4) NULL,
  "Before-Volume" text NULL,
  CONSTRAINT "LifeEventCompareId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "LifeEventCompare" OWNER TO advice2pay;


ALTER TABLE "AdjustmentType" ADD COLUMN "Ignored" boolean default false;
UPDATE "AdjustmentType" set "Ignored" = true where "Id" = 7;
INSERT into "AdjustmentType" ( "Id", "Name", "Display", "Ignored" ) values (8, 'life-event-ignored', 'Ignored', true);


ALTER TABLE "RetroData" ADD COLUMN "Before-CoverageStartDateList" text default NULL;
ALTER TABLE "RetroData" ADD COLUMN "Before-PlanId" integer NULL;


-- Add a new column to the company life table so we can tell if
-- the settings were done by software or a human.
alter table "CompanyLifeCompare" add column "AutoSelected" boolean default false;


ALTER TABLE "ProcessQueue" ADD COLUMN "ProcessId" text;

CREATE TABLE "AppOption"
(
  "Key" text NOT NULL,
  "Value" text,
  CONSTRAINT "AppOptionId" PRIMARY KEY ("Key")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "AppOption" OWNER TO advice2pay;


insert into "AdjustmentType" ( "Id", "Name", "Display", "Ignored" ) values ( 9, 'limit-retro-period', 'Ignored', true );
insert into "AdjustmentType" ( "Id", "Name", "Display", "Ignored" ) values ( 10, 'zero-cost-on-termination', 'Ignored', true );


ALTER TABLE "User" ADD COLUMN "Deleted" boolean default false;


ALTER TABLE "RetroData" ADD COLUMN "Before-PlanList" text NULL;


CREATE TABLE "CompanyMappingColumn"
(
  "Id" bigserial NOT NULL,
  "CompanyId" integer NOT NULL,
  "ColumnName" text NULL,
  "ColumnNameNormalized" text NULL,
  "MappingColumnName" text NULL,
  "ColumnNumber" integer NULL,
  CONSTRAINT "CompanyMappingColumnId" PRIMARY KEY ("Id")
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "CompanyMappingColumn" OWNER TO :db;


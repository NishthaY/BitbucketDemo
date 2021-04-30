\set db advice2pay

CREATE INDEX RelationshipData_ImportDataId_index ON "RelationshipData" ("ImportDataId");
CREATE INDEX Age_ImportDataId_index ON "Age" ("ImportDataId");
CREATE INDEX AgeBand_CompanyCoverageTierId_AgeBandStart_AgeBandEnd_index ON "AgeBand" ("CompanyCoverageTierId", "AgeBandStart", "AgeBandEnd");
CREATE INDEX SummaryData_CarrierId_PlanTypeId_PlanId_CoverageTierId_AgeBandId_TobaccoUser_ImportDate_Id_index ON "SummaryData" ("CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "AgeBandId", "TobaccoUser", "ImportDate", "Id");
CREATE INDEX RetroData_ImportDataId_index ON "RetroData" ("ImportDataId");
CREATE INDEX CompanyLife_Id_Enabled_index ON "CompanyLife" ("Id", "Enabled");
CREATE INDEX CompanyPlanType_PlanTypeCode_Ignored_index ON "CompanyPlanType" ("PlanTypeCode", "Ignored");

-- Additional Mappings for Transamerica
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'policy', 'MASTER_POLICY');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'ssn', 'PERSON_SSN');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'eid', 'EMPLOYEE_OR_MEMBER_ID');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'relationship', 'RELATIONSHIP_TO_EMPLOYEE');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'last_name', 'PERSON_LAST_NAME_OR_TRUST_NAME');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'first_name', 'PERSON_FIRST_NAME');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'middle_name', 'PERSON_MI');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'suffix', 'PERSON_SUFFIX');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'gender', 'PERSON_GENDER');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'dob', 'PERSON_DOB');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'tobacco_user', 'TOBACCO_RESPONSE');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'phone1', 'PRIMARY_PHONE');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'address1', 'STREET_ADDRESS');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'address2', 'STREET_ADDRESS2');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'enrollment_state', 'DOMICILE_STATE');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'plan_type', 'PRODUCT_CODE');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'plan', 'PLAN_CODE');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'volume', 'BENEFIT_FACE_AMOUNT');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'monthly_cost', 'TOTAL_PREMIUM');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'coverage_start_date', 'COVERAGE_EFFECTIVE_DATE');
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'group_number', 'GROUP_NUMBER');

-- Add a new log table that just holds timer data.
create table "LogTimer"
(
  "Id" serial not null constraint timerlog_pkey primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "Message" text not null,
  "Minutes" double precision,
  "Seconds" double precision,
  "Hours" double precision,
  "Timestamp" date default now() not null
);
ALTER TABLE "LogTimer" OWNER TO :db;

-- Add a table so we can turn timers on for a specific company.
create table "LogTimerRelationship"
(
  "Id" serial not null constraint "LogTimerRelationship_pkey" primary key,
  "CompanyId" integer not null
);
ALTER TABLE "LogTimerRelationship" OWNER TO :db;


\set db advice2pay


create table "EligibilityReport"
(
  "Id" serial not null constraint "EligibilityReport_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "ImportDataId" integer not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "EmployeeNumber" text,
  "ProductType" text not null,
  "Option" text not null,
  "Tier" text not null,
  "LostItem" boolean default false not null
);
ALTER TABLE "EligibilityReport" OWNER TO :db;
CREATE INDEX eligibility_two_idx on "EligibilityReport"("CompanyId", "ImportDate");
CREATE INDEX eligibility_three_idx on "EligibilityReport"("CompanyId", "ImportDate", "ImportDataId");
CREATE INDEX eligibility_eight_idx on "EligibilityReport"("CompanyId", "ImportDate", "EmployeeNumber", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId");


create table "EligibilityReportLives"
(
  "Id" serial not null constraint "EligibilityReportLives_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "ImportDataId" integer not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "EmployeeNumber" text,
  "EmployeeGroupNumber" text,
  "AddressLine1" text,
  "AddressLine2" text,
  "City" text,
  "State" text,
  "ZipCode" text,
  "ZipCodeExpansion" text,
  "CountryCode" text,
  "PhoneNumber" text,
  "IssueState" text,
  "PaidToDate" date,
  "RelationshipCode" text,
  "Status" text,
  "FirstName" text,
  "LastName" text,
  "MiddleInitial" text,
  "EffectiveDate" date,
  "TerminationDate" date,
  "DateOfBirth" date,
  "Gender" text,
  "CreditableCoverage" text,
  "IndemnityAmount" text,
  "ProductType" text not null,
  "Option" text not null,
  "Tier" text not null,
  "SortId" integer
);
ALTER TABLE "EligibilityReportLives" OWNER TO :db;
CREATE INDEX eligibilitylives_two_idx on "EligibilityReportLives"("CompanyId", "ImportDate");
CREATE INDEX eligibilitylives_three_idx on "EligibilityReportLives"("CompanyId", "ImportDate", "ImportDataId");
CREATE INDEX eligibilitylives_seven_idx on "EligibilityReportLives"("CompanyId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "EmployeeNumber");

-- Adding a new report type.
insert into "ReportType" ( "Id", "Name", "Display" ) values ( 5, 'eligibility', 'Eligibility' );

-- Adding a mapping table
create table "ObjectMapping"
(
  "Id" serial not null constraint "ObjectMapping_pkey" primary key,
  "ObjectType" text not null,
  "Input" text not null,
  "Output" text not null
);
ALTER TABLE "ObjectMapping" OWNER TO :db;

-- Adding STATE mapping items.
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Armed Forces America', 'AA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Armed Forces', 'AE' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Armed Forces Pacific', 'AP' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Alaska', 'AK' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Alabama', 'AL' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Arkansas', 'AR' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Arizona', 'AZ' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'California', 'CA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Colorado', 'CO' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Connecticut', 'CT' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Washington DC', 'DC' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'District of Columbia', 'DC' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Delaware', 'DE' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Florida', 'FL' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Georgia', 'GA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Guam', 'GU' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Hawaii', 'HI' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Iowa', 'IA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Idaho', 'ID' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Illinois', 'IL' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Indiana', 'IN' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Kansas', 'KS' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Kentucky', 'KY' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Louisiana', 'LA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Massachusetts', 'MA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Maryland', 'MD' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Maine', 'ME' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Michigan', 'MI' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Minnesota', 'MN' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Missouri', 'MO' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Mississippi', 'MS' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Montana', 'MT' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'North Carolina', 'NC' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'North Dakota', 'ND' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Nebraska', 'NE' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'New Hampshire', 'NH' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'New Jersey', 'NJ' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'New Mexico', 'NM' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Nevada', 'NV' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'New York', 'NY' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Ohio', 'OH' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Oklahoma', 'OK' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Oregon', 'OR' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Pennsylvania', 'PA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Puerto Rico', 'PR' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Rhode Island', 'RI' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'South Carolina', 'SC' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'South Dakota', 'SD' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Tennessee', 'TN' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Texas', 'TX' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Utah', 'UT' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Virginia', 'VA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Virgin Islands', 'VI' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Vermont', 'VT' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Washington', 'WA'	 );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Wisconsin', 'WI' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'West Virginia', 'WV' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'Wyoming', 'WY' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'AA', 'AA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'AE', 'AE' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'AP', 'AP' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'AK', 'AK' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'AL', 'AL' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'AR', 'AR' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'AZ', 'AZ' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'CA', 'CA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'CO', 'CO' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'CT', 'CT' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'DC', 'DC' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'DE', 'DE' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'FL', 'FL' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'GA', 'GA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'GU', 'GU' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'HI', 'HI' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'IA', 'IA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'ID', 'ID' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'IL', 'IL' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'IN', 'IN' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'KS', 'KS' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'KY', 'KY' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'LA', 'LA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'MA', 'MA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'MD', 'MD' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'ME', 'ME' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'MI', 'MI' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'MN', 'MN' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'MO', 'MO' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'MS', 'MS' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'MT', 'MT' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'NC', 'NC' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'ND', 'ND' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'NE', 'NE' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'NH', 'NH' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'NJ', 'NJ' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'NM', 'NM' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'NV', 'NV' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'NY', 'NY' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'OH', 'OH' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'OK', 'OK' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'OR', 'OR' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'PA', 'PA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'PR', 'PR' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'RI', 'RI' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'SC', 'SC' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'SD', 'SD' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'TN', 'TN' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'TX', 'TX' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'UT', 'UT' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'VA', 'VA' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'VI', 'VI' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'VT', 'VT' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'WA', 'WA'	);
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'WI', 'WI' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'WV', 'WV' );
insert into "ObjectMapping" ( "ObjectType", "Input", "Output" ) values ( 'United States State Code', 'WY', 'WY' );


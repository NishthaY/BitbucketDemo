\set db advice2pay

alter table "ReportTransamericaEligibilityDetails" add "RelationshipId" text;
alter table "ReportTransamericaEligibilityDetails" add "RelationshipSSN" text;
alter table "ReportTransamericaEligibilityDetails" add "RelationshipEID" text;


alter table "ReportTransamericaEligibility" add "RelationshipId" text;
alter table "ReportTransamericaEligibility" add "RelationshipSSN" text;
alter table "ReportTransamericaEligibility" add "RelationshipEID" text;

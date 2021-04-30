insert into "ReportTransamericaEligibilityDetails" (
  "CompanyId", "ImportDate", "ImportDataId", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "EmployeeNumber"
  , "EmployeeGroupNumber", "AddressLine1", "AddressLine2", "City", "State", "ZipCode", "ZipCodeExpansion", "CountryCode", "PhoneNumber", "IssueState", "PaidToDate"
  , "RelationshipCode", "Status", "FirstName", "LastName", "MiddleInitial", "EffectiveDate", "TerminationDate", "DateOfBirth", "Gender"
  , "CreditableCoverage", "IndemnityAmount"
  , "ProductType", "Option", "Tier", "SortId", "MasterPolicy", "EmployeeSSN", "RelationshipSSN", "RelationshipEID")
  select
    "ReportTransamericaEligibility"."CompanyId"
    , "ReportTransamericaEligibility"."ImportDate"
    , "ReportTransamericaEligibility"."ImportDataId"
    , "ReportTransamericaEligibility"."LifeId"
    , "ReportTransamericaEligibility"."CarrierId"
    , "ReportTransamericaEligibility"."PlanTypeId"
    , "ReportTransamericaEligibility"."PlanId"
    , "ReportTransamericaEligibility"."CoverageTierId"
    , "ReportTransamericaEligibility"."EmployeeNumber"

    , "ImportData"."GroupNumber"  as "EmployeeGroupNumber"
    , "ImportData"."Address1"     as "AddressLine1"
    , "ImportData"."Address2"     as "AddressLine2"
    , "ImportData"."City"         as "City"
    , "ImportData"."State"        as "State"
    , "ImportData"."PostalCode"   as "ZipCode"
    , "ImportData"."PostalCode"   as "ZipCodeExpansion"
    , 'US'                        as "CountryCode"
    , CASE
      WHEN coalesce("ImportData"."Phone1", '') = '' THEN "ImportData"."Phone2"
      ELSE "ImportData"."Phone1"
      END                         as "PhoneNumber"
    , "ImportData"."EnrollmentState"  as "IssueState"
    , CASE
      WHEN "ImportData"."CoverageEndDate" is not null  THEN "ImportData"."CoverageEndDate"
      ELSE "ImportData"."ImportDate" + INTERVAL '+1 month' + INTERVAL '-1 day'
      END                             as "PaidToDate"

    , "Relationship"."Code"           as "RelationshipCode"
    , CASE
      WHEN "ReportTransamericaEligibility"."LostItem" = true THEN  'I'
      WHEN "ImportData"."CoverageEndDate" is null THEN ' '
      WHEN "ImportData"."CoverageEndDate" < "ImportData"."ImportDate" + INTERVAL '+1 month' THEN 'I'  -- Anytime this month
      ELSE ' '
      END                             as "Status"
    , "ImportData"."FirstName"        as "FirstName"
    , "ImportData"."LastName"         as "LastName"
    , "ImportData"."MiddleName"       as "MiddleInitial"
    , "ImportData"."CoverageStartDate"  as "EffectiveDate"
    , CASE
      WHEN "ReportTransamericaEligibility"."LostItem" = true AND "ImportData"."CoverageEndDate" is null THEN to_char("ReportTransamericaEligibility"."ImportDate" + interval '-1 day', 'MM/DD/YY')::date
      ELSE "ImportData"."CoverageEndDate"
      END                                 as "TerminationDate"
    , "ImportData"."DateOfBirth"          as "DateOfBirth"
    , "ImportData"."Gender"               as "Gender"

    , '00'                                as "CreditableCoverage"
    , "ImportData"."Volume"               as "IndemnityAmount"

    , "ReportTransamericaEligibility"."ProductType" as "ProductType"
    , "ReportTransamericaEligibility"."Option" as "Option"
    , "ReportTransamericaEligibility"."Tier" as "Tier"
    , CASE
      WHEN "Relationship"."Code" = 'employee' THEN 1
      WHEN "Relationship"."Code" = 'spouse' THEN 2
      ELSE 3
      END as "SortId"
    , "ImportData"."Policy" as "MasterPolicy"
    , "ImportData"."EmployeeSSN" as "EmployeeSSN"
    , coalesce ( "ImportData"."EmployeeSSN", "ImportData"."EmployeeId" ) as "RelationshipSSN"  -- Assumes EmployeeSSN is the field that will hold the relationship between employee and dependents
    , coalesce ( "ImportData"."EmployeeId", "ImportData"."EmployeeSSN" ) as "RelationshipEID"  -- Assumes EmployeeId is the field that will hold the relationship between employee and dependents

  from
    "ReportTransamericaEligibility"
    join "ImportData" on ("ImportData"."Id" = "ReportTransamericaEligibility"."ImportDataId")
    join "RelationshipData" on ( "RelationshipData"."ImportDataId" = "ImportData"."Id" )
    join "Relationship" on ( "Relationship"."Code" = "RelationshipData"."RelationshipCode")
    left join "ReportTransamericaEligibilityDetails" on (
      "ReportTransamericaEligibilityDetails"."CompanyId" = "ReportTransamericaEligibility"."CompanyId"
      and "ReportTransamericaEligibilityDetails"."ImportDate" = "ReportTransamericaEligibility"."ImportDate"
      and "ReportTransamericaEligibilityDetails"."ImportDataId" = "ReportTransamericaEligibility"."ImportDataId"
      )
  where
    "ReportTransamericaEligibility"."CompanyId" = ?
    and "ReportTransamericaEligibility"."ImportDate" = ?
    and "ReportTransamericaEligibility"."CarrierId" = ?
    and "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" + interval '+1 month'
    and "ReportTransamericaEligibilityDetails"."ImportDataId" is null


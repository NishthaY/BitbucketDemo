insert into "ReportTransamericaActuarialDetails" ("CompanyId", "ImportDate", "ImportDataId", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "EmployeeNumber", "PolicyNumber", "GroupNumber", "ResidentState", "StatusCode", "IssueDate", "PaidToDate", "SystemTerminationDate", "BillingMode", "ModalPremium", "InsuredDOB", "InsuredSex", "InsuredState", "InsuredZIP", "InsuredSSN", "InsuredFirstName", "InsuredLastName", "ProductType", "Option", "Tier", "EmployeeSSN")
  select
    "ReportTransamericaActuarial"."CompanyId"
    , "ReportTransamericaActuarial"."ImportDate"
    , "ReportTransamericaActuarial"."ImportDataId"
    , "ReportTransamericaActuarial"."LifeId"
    , "ReportTransamericaActuarial"."CarrierId"
    , "ReportTransamericaActuarial"."PlanTypeId"
    , "ReportTransamericaActuarial"."PlanId"
    , "ReportTransamericaActuarial"."CoverageTierId"
    , "ReportTransamericaActuarial"."EmployeeNumber"

    , "ImportData"."EmployeeId"   as "PolicyNumber"
    , "ImportData"."GroupNumber"  as "GroupNumber"
    , "ImportData"."State"        as "ResidentState"
    , CASE
      WHEN "ReportTransamericaActuarial"."LostItem" = true THEN  'I'
      WHEN "ImportData"."CoverageEndDate" is null THEN ' '
      WHEN "ImportData"."CoverageEndDate" < "ImportData"."ImportDate" THEN 'I'
      ELSE ' '
      END                         as "StatusCode"
    , oed."EffectiveDate"  as "IssueDate"                   -- Transamerica requested this field be the OED EffecitveDate, not CoverageStartDt
    , CASE
      WHEN "ImportData"."CoverageEndDate" is not null  THEN "ImportData"."CoverageEndDate"
      ELSE "ImportData"."ImportDate" + INTERVAL '+1 month' + INTERVAL '-1 day'
      END                         as "PaidToDate"
    , CASE
      WHEN "ReportTransamericaActuarial"."LostItem" = true AND "ImportData"."CoverageEndDate" is null THEN to_char("ReportTransamericaActuarial"."ImportDate" + interval '-1 day', 'MM/DD/YY')::date
      ELSE "ImportData"."CoverageEndDate"
      END                         as "TerminationDate"
    , 'Monthly'                   as "BillingMode"
    , "ImportData"."MonthlyCost"  as "ModalPremium"
    , "ImportData"."DateOfBirth"  as "InsuredDOB"
    , "ImportData"."Gender"       as "InsuredSex"
    , "ImportData"."EnrollmentState" as "InsuredState"
    , "ImportData"."PostalCode" as "InsuredZIP"
    , "ImportData"."EmployeeSSN" as "InsuredSSN"            -- Transamerica requested this field be EmployeeSSN, not PersonSSN
    , "ImportData"."FirstName"    as "InsuredFirstName"
    , "ImportData"."LastName"    as "InsuredLastName"



    , "ReportTransamericaActuarial"."ProductType" as "ProductType"
    , "ReportTransamericaActuarial"."Option" as "Option"
    , "ReportTransamericaActuarial"."Tier" as "Tier"
    , "ImportData"."EmployeeSSN" as "EmployeeSSN"

  from
    "ReportTransamericaActuarial"
    join "ImportData" on ("ImportData"."Id" = "ReportTransamericaActuarial"."ImportDataId")
    join "RelationshipData" on ( "RelationshipData"."ImportDataId" = "ImportData"."Id" )
    join "Relationship" on ( "Relationship"."Code" = "RelationshipData"."RelationshipCode")
    join "LifeOriginalEffectiveDate" oed on ( oed."LifeId" = "ReportTransamericaActuarial"."LifeId" and oed."CarrierId" = "ReportTransamericaActuarial"."CarrierId" and oed."PlanTypeId" = "ReportTransamericaActuarial"."PlanTypeId" and oed."PlanId" = "ReportTransamericaActuarial"."PlanId" and oed."CoverageTierId" = "ReportTransamericaActuarial"."CoverageTierId")
    left join "ReportTransamericaActuarialDetails" on (
    "ReportTransamericaActuarialDetails"."CompanyId" = "ReportTransamericaActuarial"."CompanyId"
    and "ReportTransamericaActuarialDetails"."ImportDate" = "ReportTransamericaActuarial"."ImportDate"
    and "ReportTransamericaActuarialDetails"."ImportDataId" = "ReportTransamericaActuarial"."ImportDataId"
    )
  where
    "ReportTransamericaActuarial"."CompanyId" = ?
    and "ReportTransamericaActuarial"."ImportDate" = ?
    and "ReportTransamericaActuarial"."CarrierId" = ?
    and "Relationship"."Code" = 'employee'
    and oed."EffectiveDate" < "ImportData"."ImportDate" + interval '+1 month'
    and "ReportTransamericaActuarialDetails"."ImportDataId" is null


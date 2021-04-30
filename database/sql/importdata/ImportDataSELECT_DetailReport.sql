select
    coalesce("ImportData"."EmployeeId", "CompanyUniversalEmployee"."UniversalEmployeeId") as "UEID"
     , "ImportData"."Division"
     , "ImportData"."Department"
     , "ImportData"."BusinessUnit"
     , "ImportData"."EmployeeId"
     , "ImportData"."EmployeeSSNDisplay" as "EmployeeSSN"
     , "CompanyLife"."SSNDisplay" as "PersonSSN"
     , "Relationship"."Description" as "Relationship"
     , "ImportData"."FirstName"
     , "ImportData"."MiddleName"
     , "ImportData"."LastName"
     , "ImportData"."Suffix"
     , "ImportData"."Address1"
     , "ImportData"."Address2"
     , "ImportData"."City"
     , "ImportData"."State"
     , "ImportData"."PostalCode"
     , "ImportData"."Phone1"
     , "ImportData"."Phone2"
     , "ImportData"."Email1"
     , "ImportData"."Email2"
     , "ImportData"."DateOfBirth"
     , CASE WHEN "AgeType"."Enabled" = true THEN "Age"."Age" ELSE null END as "Age"
     , "ImportData"."Gender"
     , "ImportData"."TobaccoUser"
     , "CompanyCarrier"."UserDescription" as "Carrier"
     , "CompanyPlanType"."UserDescription" as "PlanType"
     , "CompanyPlan"."UserDescription" as "Plan"
     , "CompanyCoverageTier"."UserDescription" as "CoverageTier"
     , "ImportData"."CoverageStartDate"
     , "ImportData"."CoverageEndDate"
     , coalesce("RelationshipData"."Volume", "ImportData"."Volume" ) as "Volume"
     , coalesce("RelationshipData"."MonthlyCost", "ImportData"."MonthlyCost" ) as "MonthlyCost"
     , "ImportData"."AnnualSalary"
     , "ImportData"."EmploymentActive"
     , "ImportData"."EmploymentStart"
     , "ImportData"."EmploymentEnd"
     , "ImportData"."Reason"
     , "ImportData"."Policy"
     , "ImportData"."GroupNumber"
     , "ImportData"."EnrollmentState"
     , "ImportData"."PolicyRole"
     , "ImportData"."ImportDate" as "ReportDate"
     , 'ImportData' as "RecordType"
     , coalesce("RelationshipData"."Memo", null) as "Memo"
from
    "ImportData"
        left join "RelationshipData" on ( "RelationshipData"."ImportDataId" = "ImportData"."Id" )
        left join "Relationship" on ( "Relationship"."Code" = "RelationshipData"."RelationshipCode")
        join "LifeData" on ( "LifeData"."ImportDataId" = "ImportData"."Id" )
        join "CompanyLife" on ( "CompanyLife"."CompanyId" = "ImportData"."CompanyId" and "CompanyLife"."Id" = "LifeData"."LifeId" and "CompanyLife"."Enabled" = true )
        join "WashedData" on ( "WashedData"."ImportDataId" = "ImportData"."Id" )
        join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "WashedData"."CarrierId" )
        join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "WashedData"."PlanTypeId" )
        join "CompanyPlan" on ( "CompanyPlan"."Id" = "WashedData"."PlanId" )
        join "CompanyCoverageTier" on ( "CompanyCoverageTier"."Id" = "WashedData"."CoverageTierId" )
        join "Age" on ( "Age"."ImportDataId" = "ImportData"."Id" )
        join "AgeType" on ( "Age"."AgeTypeId" = "AgeType"."Id" )
        left join "LifeOriginalEffectiveDate" on
        (
                    "LifeOriginalEffectiveDate"."LifeId" = "LifeData"."LifeId"
                and "LifeOriginalEffectiveDate"."CarrierId" = "CompanyCarrier"."Id"
                and "LifeOriginalEffectiveDate"."PlanTypeId" = "CompanyPlanType"."Id"
                and "LifeOriginalEffectiveDate"."PlanId" = "CompanyPlan"."Id"
                and "LifeOriginalEffectiveDate"."CoverageTierId" = "CompanyCoverageTier"."Id"
            )
        left join "CompanyUniversalEmployee" on ( "CompanyUniversalEmployee"."CompanyId" = "ImportData"."CompanyId" and "CompanyUniversalEmployee"."EmployeeSSN" = "ImportData"."EmployeeSSN" )
where
        "ImportData"."CompanyId" = ?
  and "ImportData"."ImportDate" = ?
  and "CompanyCarrier"."Id" = ?
  and "WashedData"."WashedOutFlg" = false
  and "CompanyPlan"."PremiumEquivalent" = ?
order by
    "CompanyCarrier"."UserDescription" asc
       , "CompanyPlanType"."UserDescription" asc
       , "CompanyPlan"."UserDescription" asc
       , "CompanyCoverageTier"."UserDescription" asc
       , "LastName" asc
       , "FirstName" asc
       , "DateOfBirth" asc
--, "ImportData"."EmployeeId"::int asc  -- NO!  This could have alpha characters.

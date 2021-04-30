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
     , CASE
           WHEN ( ParentRetroData."CoverageStartDate" is not null ) THEN ParentRetroData."CoverageStartDate"
           WHEN ( "RetroData"."CoverageStartDate" is not null ) THEN "RetroData"."CoverageStartDate"
           ELSE "ImportData"."CoverageStartDate"
    END as "CoverageStartDate"
     , CASE
           WHEN ( ParentRetroData."CoverageEndDate" is not null ) THEN ParentRetroData."CoverageEndDate"
           WHEN ( "RetroData"."CoverageEndDate" is not null ) THEN "RetroData"."CoverageEndDate"
           ELSE "ImportData"."CoverageEndDate"
    END as "CoverageEndDate"
     , "AutomaticAdjustment"."Volume"
     , "AutomaticAdjustment"."MonthlyCost"
     , "ImportData"."AnnualSalary"
     , "ImportData"."EmploymentActive"
     , "ImportData"."EmploymentStart"
     , "ImportData"."EmploymentEnd"
     , "ImportData"."Reason" as "Reason"
     , "ImportData"."Policy"
     , "ImportData"."GroupNumber"
     , "ImportData"."EnrollmentState"
     , "ImportData"."PolicyRole"
     , "AutomaticAdjustment"."ImportDate" as "ReportDate"
     , CASE
           WHEN ( "AutomaticAdjustment"."AdjustmentType" = 1 ) THEN 'Manual Adjustment'
           WHEN ( "AutomaticAdjustment"."AdjustmentType" = 2 ) THEN 'Retro Add'
           WHEN ( "AutomaticAdjustment"."AdjustmentType" = 3 ) THEN 'Retro Term'
           WHEN ( "AutomaticAdjustment"."AdjustmentType" = 4 ) THEN 'Retro Change Date'
           WHEN ( "AutomaticAdjustment"."AdjustmentType" = 5 ) THEN 'Retro Change Cost'
           WHEN ( "AutomaticAdjustment"."AdjustmentType" = 6 ) THEN 'Retro Change Tier'
           ELSE 'Unknown'
    END as "RecordType"
     , "AutomaticAdjustment"."Memo" as "Memo"

from
    "AutomaticAdjustment"
        join "AdjustmentType" on ( "AdjustmentType"."Id" = "AutomaticAdjustment"."AdjustmentType" )
        join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "AutomaticAdjustment"."CarrierId" )
        join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "AutomaticAdjustment"."PlanTypeId" )
        join "CompanyPlan" on ( "CompanyPlan"."Id" = "AutomaticAdjustment"."PlanId" )
        join "CompanyCoverageTier" on ( "CompanyCoverageTier"."Id" = "AutomaticAdjustment"."CoverageTierId" )
        join "RetroData" on ( "RetroData"."Id" = "AutomaticAdjustment"."RetroDataId" )
        join "ImportData" on ( "ImportData"."Id" = "RetroData"."ImportDataId" )
        join "LifeData" on ( "LifeData"."ImportDataId" = "ImportData"."Id" )
        join "CompanyLife" on ( "CompanyLife"."CompanyId" = "AutomaticAdjustment"."CompanyId" and "CompanyLife"."Id" = "LifeData"."LifeId" and "CompanyLife"."Enabled" = true )
        join "Age" on ( "Age"."ImportDataId" = "ImportData"."Id" )
        join "AgeType" on ( "Age"."AgeTypeId" = "AgeType"."Id" )
        left join "RetroData" as ParentRetroData on ( "RetroData"."Id" = "AutomaticAdjustment"."ParentRetroDataId")
        left join "RelationshipData" on ( "RelationshipData"."ImportDataId" = "ImportData"."Id" )
        left join "Relationship" on ( "Relationship"."Code" = "RelationshipData"."RelationshipCode")
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
        "AutomaticAdjustment"."CompanyId" = ?
  and "AutomaticAdjustment"."ImportDate" = ?
  and "AutomaticAdjustment"."CarrierId" = ?
  and "AdjustmentType"."Ignored" = false -- Exclude ignored adjustments
  and "CompanyPlan"."PremiumEquivalent" = ?
order by
    "LastName" asc
       , "FirstName" asc
       , case when "Relationship"."Code" = 'employee' then 1 when "Relationship"."Code" = 'spouse' then 2 when "Relationship"."Code" = 'dependent' then 3 else 4 end asc
       , "LifeData"."LifeId" asc
       , "CompanyPlanType"."UserDescription" asc
       , "CompanyPlan"."UserDescription" asc
       , "AutomaticAdjustment"."TargetDate" asc
--"ImportData"."EmployeeId"::int asc --NO! This field could have alpha characters.

--order by
--	"CompanyCarrier"."UserDescription" asc
--	, "CompanyPlanType"."UserDescription" asc
--	, "CompanyPlan"."UserDescription" asc
--	, "CompanyCoverageTier"."UserDescription" asc
--	, "EmployeeId" asc
--	, "LastName" asc
--	, "FirstName" asc
--	, "DateOfBirth" asc

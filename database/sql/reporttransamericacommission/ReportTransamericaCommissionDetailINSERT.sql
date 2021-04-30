insert into "ReportTransamericaCommissionDetail" (
  "CompanyId", "ImportDate", "ImportDataId", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "ProductType", "Option", "Tier", "MasterPolicy", "EmployeeId",
  "TierEffectiveDate", "TierMonthlyPremium", "CurrentCertStatus", "OriginalCertIssueDate", "CertTermDate", "MonthPaidFor", "FirstName", "LastName", "MiddleName", "Suffix", "EmployeeSSN",
  "PremiumFirstYear", "PremiumRenewal"
)
select
  "ReportTransamericaCommission"."CompanyId"
  , "ReportTransamericaCommission"."ImportDate"
  , "ReportTransamericaCommission"."ImportDataId"
  , "ReportTransamericaCommission"."LifeId"
  , "ReportTransamericaCommission"."CarrierId"
  , "ReportTransamericaCommission"."PlanTypeId"
  , "ReportTransamericaCommission"."PlanId"
  , "ReportTransamericaCommission"."CoverageTierId"
  , "ReportTransamericaCommission"."ProductType"
  , "ReportTransamericaCommission"."Option"
  , "ReportTransamericaCommission"."Tier"
  , "ReportTransamericaCommission"."MasterPolicy"
  , "ReportTransamericaCommission"."EmployeeNumber" as "EmployeeId"
  ,"ImportData"."CoverageStartDate" as "TierEffectiveDate"
  ,"ImportData"."MonthlyCost" as "TierMonthlyPremium"
  , CASE
    WHEN "ImportData"."CoverageEndDate" < "ImportData"."ImportDate" THEN 'T'
    ELSE 'A'
    END as "CurrentCertStatus"
  , "LifeOriginalEffectiveDate"."EffectiveDate" as "OriginalCertIssueDate"
  , "ImportData"."CoverageEndDate" as "CertTermDate"
  , "ImportData"."ImportDate" as "MonthPaidFor"
  , "ImportData"."FirstName"
  , "ImportData"."LastName"
  , "ImportData"."MiddleName"
  , "ImportData"."Suffix"
  , "ImportData"."EmployeeSSN"
  , "CompanyCommissionSummary"."CommissionablePremiumAgedOneYearOrLess"
  , "CompanyCommissionSummary"."CommissionablePremiumAgedMoreThanOneYear"

from
  "ReportTransamericaCommission"
  join "ImportData" on ( "ImportData"."Id" = "ReportTransamericaCommission"."ImportDataId")
  join "WashedData" on ( "ImportData"."Id" = "WashedData"."ImportDataId")
  join "LifeData" on ( "LifeData"."ImportDataId" = "ImportData"."Id" )
  join "LifeOriginalEffectiveDate" on (
    "LifeOriginalEffectiveDate"."LifeId" = "LifeData"."LifeId"
    and "LifeOriginalEffectiveDate"."CarrierId" = "WashedData"."CarrierId"
    and "LifeOriginalEffectiveDate"."PlanTypeId" = "WashedData"."PlanTypeId"
    and "LifeOriginalEffectiveDate"."PlanId" = "WashedData"."PlanId"
    and "LifeOriginalEffectiveDate"."CoverageTierId" = "WashedData"."CoverageTierId"
  )
  left join "ReportTransamericaCommissionDetail" on (
    "ReportTransamericaCommissionDetail"."CompanyId" = "ReportTransamericaCommission"."CompanyId"
    and "ReportTransamericaCommissionDetail"."ImportDate" = "ReportTransamericaCommission"."ImportDate"
    and "ReportTransamericaCommissionDetail"."ImportDataId" = "ReportTransamericaCommission"."ImportDataId"
  )
  left join "CompanyCommissionSummary" on (
    "CompanyCommissionSummary"."CompanyId" = "ReportTransamericaCommission"."CompanyId"
    and "CompanyCommissionSummary"."ImportDate" = "ReportTransamericaCommission"."ImportDate"
    and "CompanyCommissionSummary"."LifeId" = "ReportTransamericaCommission"."LifeId"
    and "CompanyCommissionSummary"."CarrierId" = "ReportTransamericaCommission"."CarrierId"
    and "CompanyCommissionSummary"."PlanTypeId" = "ReportTransamericaCommission"."PlanTypeId"
    and "CompanyCommissionSummary"."PlanId" = "ReportTransamericaCommission"."PlanId"
    )
where
  "ReportTransamericaCommission"."CompanyId" = ?
  and "ReportTransamericaCommission"."ImportDate" = ?
  and "ReportTransamericaCommission"."LostLife" = false
  and "ReportTransamericaCommissionDetail"."Id" is null
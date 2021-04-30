insert into "ReportTransamericaCommissionDetail" (
    "CompanyId", "ImportDate", "ImportDataId", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "ProductType", "Option", "Tier", "MasterPolicy", "EmployeeId",
    "TierEffectiveDate", "TierMonthlyPremium", "CurrentCertStatus", "OriginalCertIssueDate", "CertTermDate", "MonthPaidFor", "FirstName", "LastName", "MiddleName", "Suffix", "EmployeeSSN",
    "PremiumFirstYear", "PremiumRenewal"
)
select
    prev."CompanyId"
     , lost."ImportDate"
     , prev."ImportDataId"
     , prev."LifeId"
     , prev."CarrierId"
     , prev."PlanTypeId"
     , prev."PlanId"
     , prev."CoverageTierId"
     , prev."ProductType"
     , prev."Option"
     , prev."Tier"
     , prev."MasterPolicy"
     , prev."EmployeeId"
     , prev."TierEffectiveDate"
     , prev."TierMonthlyPremium"
     , 'T' as "CurrentCertStatus"
     , prev."OriginalCertIssueDate"
     , CASE
           WHEN prev."CertTermDate" is not null  THEN prev."CertTermDate"
           ELSE lost."ImportDate" + INTERVAL '+1 month' + INTERVAL '-1 day'
        END                         as "CertTermDate"
     , prev."MonthPaidFor"
     , prev."FirstName"
     , prev."LastName"
     , prev."MiddleName"
     , prev."Suffix"
     , prev."EmployeeSSN"
     , prev."PremiumFirstYear"
     , prev."PremiumRenewal"
from
    "ReportTransamericaCommission" lost
    join "ReportTransamericaCommissionDetail" prev on (
        prev."CompanyId" = lost."CompanyId"
        and prev."ImportDate" = ?       -- previous month
        and prev."LifeId" = lost."LifeId"
        and prev."CarrierId" = lost."CarrierId"
        and prev."PlanTypeId" = lost."PlanTypeId"
        and prev."PlanId" = lost."PlanId"
        and prev."CoverageTierId" = lost."CoverageTierId"
    )
where
    lost."CompanyId" = ?
    and lost."ImportDate" = ?       -- current month
    and lost."LostLife" = true
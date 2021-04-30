select
  r."MasterPolicy"
  , r."EmployeeId"
  , r."TierEffectiveDate"
  , r."Tier"
  , r."TierMonthlyPremium"
  , r."CurrentCertStatus"
  , r."OriginalCertIssueDate"
  , r."CertTermDate"
  , r."MonthPaidFor"
  , r."FirstName"
  , r."LastName"
  , r."MiddleName"
  , r."Suffix"
  , r."EmployeeSSN"
  , r."PremiumFirstYear"
  , r."PremiumRenewal"
from
  "ReportTransamericaCommissionDetail" r
where
  r."CompanyId" = ?
  and r."ImportDate" = ?
order by "LastName", "FirstName", "ProductType", "Option", "Tier"
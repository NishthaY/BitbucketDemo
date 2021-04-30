-- Mark records for this month that have the same commissionable premium and monthly cost.
update "CompanyCommissionValidate"
set "Validated" = true
where
  "CompanyId" = ?
  and "ImportDate" = ?
  and "CommissionablePremium" = "MonthlyCost"
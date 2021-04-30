update "CompanyCommission" set
  "CommissionablePremium" =  ("CommissionablePremium" + ? )
where
  "Id" = ?
  and "CompanyId" = ?
  and "ImportDate" = ?
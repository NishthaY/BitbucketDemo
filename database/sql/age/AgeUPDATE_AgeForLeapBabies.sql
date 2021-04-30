update "Age" set
  "Age" = "Age" + 1
where
  "CompanyId" = ?
  and "ImportDate" = ?
  and "LeapBabyFlg" = true
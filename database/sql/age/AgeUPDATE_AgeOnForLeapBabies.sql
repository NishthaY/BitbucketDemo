update "Age" set
  "AgeOn" = format('2/28/%s', ?)
where
  "CompanyId" = ?
  and "ImportDate" = ?
  and "LeapBabyFlg" = true
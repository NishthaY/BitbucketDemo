-- If we have a NEW record, mark it as an OEDReset
update "LifeOriginalEffectiveDateCompare"
set "OEDReset" = true
where
  "CompanyId" = ?
  and "ImportDate" = ?
  and "LifeOriginalEffectiveDateCompare"."Code" = 'NEW'
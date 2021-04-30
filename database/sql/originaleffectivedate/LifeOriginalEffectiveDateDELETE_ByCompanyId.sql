DELETE
FROM "LifeOriginalEffectiveDate"
USING "CompanyLife"
WHERE
  "CompanyLife"."Id" = "LifeOriginalEffectiveDate"."LifeId"
  and "CompanyLife"."CompanyId" = ?
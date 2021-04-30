\set db advice2pay

-- Add an index to the LifeOriginalEffectiveDateCompare table that we can use for
-- the SelectIntoInserts to deal with large queries.
CREATE INDEX LifeOriginalEffectiveDateCompare_CompanyId_ImportDate_ImportDataId_index ON public."LifeOriginalEffectiveDateCompare" ("CompanyId", "ImportDate", "ImportDataId");
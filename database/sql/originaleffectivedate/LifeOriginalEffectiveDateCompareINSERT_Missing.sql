insert into "LifeOriginalEffectiveDateCompare" ( "ImportDataId", "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "CoverageStartDate", "OriginalEffectiveDate", "Calculated-EffectiveDate", "IsCoverageStartDate", "Code" )
select
    null as "ImportDataId"
  , prev."CompanyId"
  , (prev."ImportDate" + interval '1 month')::date as "ImportDate"
  , prev."LifeId"
  , prev."CarrierId"
  , prev."PlanTypeId"
  , prev."PlanId"
  , prev."CoverageTierId"
  , prev."CoverageStartDate"
  , prev."OriginalEffectiveDate"
  , prev."Calculated-EffectiveDate"
  , prev."IsCoverageStartDate"
  , 'MISSING' as "Code"
from
  -- Look at last months records.
  "LifeOriginalEffectiveDateCompare" prev

  -- Pull in the coresponding record for this month so we can tell if they are missing from this month and last.
  left join "LifeOriginalEffectiveDateCompare" now on
                                                     (
                                                       now."CompanyId" = prev."CompanyId"
                                                       AND now."ImportDate" = (prev."ImportDate" + interval '1 month')::date
                                                       and now."LifeId" = prev."LifeId"
                                                       and now."CarrierId" = prev."CarrierId"
                                                       and now."PlanTypeId" = prev."PlanTypeId"
                                                       and now."PlanId" = prev."PlanId"
                                                       and now."CoverageTierId" = prev."CoverageTierId"
                                                       )

  -- Also, grab the lockbox record for this record so we can tell if the lost date has been set.
  left join "LifeOriginalEffectiveDate" oed on
                                         (
                                          oed."LifeId" = prev."LifeId"
                                          and oed."CarrierId" = prev."CarrierId"
                                          and oed."PlanTypeId" = prev."PlanTypeId"
                                          and oed."PlanId" = prev."PlanId"
                                          and oed."CoverageTierId" = prev."CoverageTierId"
                                          )
where
  prev."CompanyId" = ?
  AND prev."ImportDate" = ?
  AND coalesce(prev."Code",'') <> 'MISSING'
  AND now."ImportDataId" is null
  and oed."LostDate" is null  -- If we don't have a lost date in the lock box, this is missing!
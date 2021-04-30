update "LifeOriginalEffectiveDateCompare"
set
  "OldestLifePlanEffectiveDate" = subquery."OldestLifePlanEffectiveDate"
  , "OldestLifePlanDiscoveryDate" = subquery."OldestLifePlanDiscoveryDate"
from
  (
    select
      now."LifeId"
      , now."CarrierId"
      , now."PlanTypeId"
      , now."PlanId"
      , before."OldestLifePlanEffectiveDate"
      , before."OldestLifePlanDiscoveryDate"
    from
      "LifeOriginalEffectiveDateCompare" before
      join "LifeOriginalEffectiveDateCompare" now on ( before."CompanyId" = now."CompanyId" and before."ImportDate" + interval '+1 month' = now."ImportDate" and before."LifeId" = now."LifeId" and before."CarrierId" = now."CarrierId"  and before."PlanTypeId" = now."PlanTypeId" and before."PlanId" = now."PlanId"
                                                       and now."Code" <> 'NEW' )
    WHERE
      before."CompanyId" = ?
      and before."ImportDate" = ?::DATE + interval '-1 month'
      and before."OldestLifePlanEffectiveDate" is not null
    group by
      now."LifeId", now."CarrierId", now."PlanTypeId", now."PlanId",  before."OldestLifePlanEffectiveDate", before."OldestLifePlanDiscoveryDate"
    order by
      before."OldestLifePlanEffectiveDate" desc, before."OldestLifePlanDiscoveryDate" desc
    --limit 1
  ) as subquery
where
  "LifeOriginalEffectiveDateCompare"."CompanyId" = ?
  and "LifeOriginalEffectiveDateCompare"."ImportDate" = ?
  and "LifeOriginalEffectiveDateCompare"."Code" <> 'NEW'
  and "LifeOriginalEffectiveDateCompare"."LifeId" = subquery."LifeId"
  and "LifeOriginalEffectiveDateCompare"."CarrierId" = subquery."CarrierId"
  and "LifeOriginalEffectiveDateCompare"."PlanTypeId" = subquery."PlanTypeId"
  and "LifeOriginalEffectiveDateCompare"."PlanId" = subquery."PlanId"
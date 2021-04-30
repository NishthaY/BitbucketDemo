-- Select * from this month from the compare table that is NEW and has not already been
-- flagged as an OEDReset.
-- Then grab things from this month on the same plan.  If there are NONE, we want them.
-- This is the list of things that are NEW that will need to be compared against the
-- OED table to find out if there is a coverage gap or not.
select
  c1."CompanyId"
  , c1."ImportDate"
  , c1."LifeId"
  , c1."CarrierId"
  , c1."PlanTypeId"
  , c1."PlanId"
  , c1."CoverageTierId"
  , c1."Code"
  , c1."CoverageStartDate"
from
  "LifeOriginalEffectiveDateCompare" c1
  left join "LifeOriginalEffectiveDateCompare" c2 on
                                                    (
                                                      c1."CompanyId" = c2."CompanyId"
                                                      and c1."ImportDate" = c2."ImportDate"
                                                      and c1."LifeId" = c2."LifeId"
                                                      and c1."CarrierId" = c2."CarrierId"
                                                      and c1."PlanTypeId" = c2."PlanTypeId"
                                                      and c1."PlanId" = c2."PlanId"
                                                      and c2."Code" <> 'NEW'
                                                      )

where
  c1."CompanyId" = ?
  and c1."ImportDate" = ?
  and c1."Code" = 'NEW'
  and ( c1."OEDReset" is null OR c1."OEDReset" <> true )
  and c2."Code" is null
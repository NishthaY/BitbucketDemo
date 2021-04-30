select "LifeId", "PlanTypeCode", string_agg("CoverageTierId", ',') as "CoverageTierList" from
(
  select
    "CompanyId"
    , "ImportDate"
    , "LifeId"
    , "PlanTypeCode"
    , "CoverageTierId"::text
  from
    "RetroData"
  where
    "RetroData"."CompanyId" = ?
    and "ImportDate" = ?
  group by "CompanyId", "ImportDate", "LifeId", "PlanTypeCode", "CoverageTierId"
  order by "CompanyId", "ImportDate", "LifeId", "PlanTypeCode", "CoverageTierId"
) as x
group by x."LifeId", x."PlanTypeCode"

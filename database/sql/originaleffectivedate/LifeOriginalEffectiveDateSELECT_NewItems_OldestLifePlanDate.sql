select "OldestLifePlanEffectiveDate", "OldestLifePlanDiscoveryDate" from
  (
    SELECT
      "EffectiveDate" AS "OldestLifePlanEffectiveDate"
      , "DiscoveryDate" as "OldestLifePlanDiscoveryDate"
    FROM
      "LifeOriginalEffectiveDate" oed
    WHERE
      oed."LifeId" = ?
      AND oed."CarrierId" = ?
      AND oed."PlanTypeId" = ?
      AND oed."PlanId" = ?

    UNION

    SELECT
      "CoverageStartDate" AS "OldestLifePlanEffectiveDate"
      , "ImportDate" as "OldestLifePlanDiscoveryDate"
    FROM
      "LifeOriginalEffectiveDateCompare" c
    WHERE
      c."CompanyId" = ?
      AND c."ImportDate" = ?
      AND c."LifeId" = ?
      AND c."CarrierId" = ?
      AND c."PlanTypeId" = ?
      AND c."PlanId" = ?
      AND c."Code" = 'NEW'
  ) as x
order by "OldestLifePlanEffectiveDate" asc
limit 1
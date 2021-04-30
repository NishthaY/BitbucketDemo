select
  case when count(*) = 0 then false else true end as "IsCoverageStartDateCorrection"
from
  "RetroData" r
  join "WashedData" w on
                        (
                          w."CompanyId" = r."CompanyId" and w."ImportDate" = r."ImportDate" and w."LifeId" = r."LifeId" and w."CarrierId" = r."CarrierId" and w."PlanTypeId" = r."PlanTypeId" and w."PlanId" = r."PlanId" and w."CoverageTierId" = r."CoverageTierId"
                          and (w."WashedOutFlg" is null or w."WashedOutFlg" = false )
                          )
where
  r."CompanyId" = ?
  and r."ImportDate" = ?
  and r."LifeId" = ?
  and r."CarrierId" = ?
  and r."PlanTypeId" = ?
  and r."PlanId" = ?
  and r."CoverageTierId" = ?

  -- RetroChange is in play.
  and r."AdjustmentType" in ( 4, 5, 6 )

  -- We noticed specifically the coverage start date changed in some way.
  and ( r."Before-CoverageStartDate" is not null OR r."Before-CoverageStartDateList" is not null )
insert into "CompanyCommission" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CommissionEffectiveDate", "CommissionablePremium", "ResetRecord"  )
select
  cc_before."CompanyId"
  , now."ImportDate"
  , cc_before."LifeId"
  , cc_before."CarrierId"
  , cc_before."PlanTypeId"
  , cc_before."PlanId"
  , cc_before."CommissionEffectiveDate"
  , cc_before."CommissionablePremium"

  -- if NOW is an OEDReset, then this will need to be one too.
  -- otherwise, keep the value from before.
  , case when now."OEDReset" = true then now."OEDReset" else cc_before."ResetRecord" end as "ResetRecord"
  --, now."ResetRecord" as "ResetRecord"
  --, cc_before."OEDReset" as "ResetRecord"
from
  "CompanyCommissionDataCompare" now
  join "CompanyCommission" cc_before on ( now."CompanyId" = cc_before."CompanyId" and now."ImportDate" + interval '{OFFSET} month' = cc_before."ImportDate" and now."LifeId" = cc_before."LifeId" and now."CarrierId" = cc_before."CarrierId" and now."PlanTypeId" = cc_before."PlanTypeId" and now."PlanId" = cc_before."PlanId" )
where
  now."CompanyId" = ?
  and now."ImportDate" = ?
  and coalesce(now."Code", '') in ('', 'WARNING')
  and now."CoverageGapOffset" = ?
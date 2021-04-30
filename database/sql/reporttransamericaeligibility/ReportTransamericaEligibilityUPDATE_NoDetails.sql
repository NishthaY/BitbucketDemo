-- We had a production situation where the data provided was a bit
-- bogus and we had a life, but every detail record was suppressed.
-- When that happens, Transamerica wants those lives to drop off the
-- report.
--
-- To do that, we will scan the master table for records that have
-- no details.  When we start processing the file, we will skip those
-- rows.
update "ReportTransamericaEligibility"
set "IssueCode" = 'NO_DETAILS'
where "Id" in
(
  select
    r."Id"
  from
    "ReportTransamericaEligibility" r
    left join "ReportTransamericaEligibilityDetails" d on
    (
      d."CompanyId" = r."CompanyId"
      and d."CarrierId" = r."CarrierId"
      and d."PlanTypeId" = r."PlanTypeId"
      and d."PlanId" = r."PlanId"
      and d."CoverageTierId" = r."CoverageTierId"
      and d."EmployeeNumber" = r."EmployeeNumber"
      and ( d."IssueCode" is null OR d."IssueCode" not in ( 'TIER_EC_IGNORE', 'TIER_EO_IGNORE', 'TIER_ES_IGNORE', 'TIER_CHANGE_IGNORE' ) )
  )
  where
    r."CompanyId" = ?
    and r."ImportDate" = ?
    and d."EmployeeNumber" is null
)
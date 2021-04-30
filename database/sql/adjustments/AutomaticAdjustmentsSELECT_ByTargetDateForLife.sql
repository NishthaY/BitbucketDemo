select
	"AutomaticAdjustment"."Volume"
	, "AutomaticAdjustment"."MonthlyCost"
	, "CompanyLife"."FirstName"
	, "CompanyLife"."LastName"
	, "CompanyLife"."MiddleName"
	, "CompanyLife"."EmployeeId"
	, "AutomaticAdjustment"."ImportDate"
    , to_char("AutomaticAdjustment"."ImportDate", 'Mon YYYY') as "MemoImportDate"
    , to_char("AutomaticAdjustment"."TargetDate", 'Mon YYYY') as "MemoTargetDate"
from
	"AutomaticAdjustment"
	join "CompanyLife" on ( "CompanyLife"."CompanyId" = "AutomaticAdjustment"."CompanyId" and "CompanyLife"."Id" = "AutomaticAdjustment"."LifeId" and "CompanyLife"."Enabled" = true )
where
	"AutomaticAdjustment"."CompanyId" = ?
	and "AutomaticAdjustment"."CarrierId" = ?
	and "AutomaticAdjustment"."PlanTypeId" = ?
    and "AutomaticAdjustment"."PlanId" = ?
	and "AutomaticAdjustment"."CoverageTierId" = ?
	and "AutomaticAdjustment"."LifeId" = ?
	and "AutomaticAdjustment"."TargetDate" = ?

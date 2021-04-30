select
	"ImportData"."CompanyId"
	, "ImportData"."ImportDate"
	, "ImportData"."Finalized"
	, "ImportData"."EmployeeId"
	, "ImportData"."PlanType"
	, "ImportData"."FirstName"
	, "ImportData"."LastName"
	, "ImportData"."CoverageStartDate"
	, "ImportData"."CoverageEndDate"
	, "ImportData"."AnnualSalary"
	, "ImportData"."Carrier"
	, "ImportData"."CoverageTier"
	, "ImportData"."DateOfBirth"
	, coalesce("RelationshipData"."MonthlyCost", "ImportData"."MonthlyCost") as "MonthlyCost"
	, "ImportData"."EmploymentActive"
	, "ImportData"."EmploymentEnd"
	, "ImportData"."EmploymentStart"
	, "ImportData"."MiddleName"
	, "ImportData"."Gender"
	, "ImportData"."Plan"
	, "ImportData"."SSN"
	, "ImportData"."TobaccoUser"
	, coalesce("RelationshipData"."Volume", "ImportData"."Volume") as "Volume"
	, "CompanyLife"."FirstName" as "MemoFirstName"
	, "CompanyLife"."LastName" as "MemoLastName"
	, "CompanyLife"."MiddleName" as "MemoMiddleName"
	, "CompanyLife"."EmployeeId" as "MemoEmployeeId"
    , to_char("ImportData"."ImportDate", 'Mon YYYY') as "MemoTargetDate"
from
	"ImportData"
	left join "RelationshipData" on ( "RelationshipData"."ImportDataId" = "ImportData"."Id" )
	join "LifeData" on ( "LifeData"."ImportDataId" = "ImportData"."Id")
	join "CompanyLife" on ( "CompanyLife"."CompanyId" = "ImportData"."CompanyId" and "LifeData"."LifeId" = "CompanyLife"."Id" and "CompanyLife"."Enabled" = true )
	join "CompanyCarrier" on
	(
		"CompanyCarrier"."CompanyId" = "ImportData"."CompanyId"
		and "CompanyCarrier"."CarrierNormalized" = upper("ImportData"."Carrier")
	)
	join "CompanyPlanType" on
 	(
 		"CompanyPlanType"."CarrierId" =  "CompanyCarrier"."Id"
 		and "CompanyPlanType"."PlanTypeNormalized" = upper("ImportData"."PlanType")
 	)
	join "CompanyPlan" on
 	(
		"CompanyPlan"."CarrierId" =  "CompanyCarrier"."Id"
		and "CompanyPlan"."PlanTypeId" = "CompanyPlanType"."Id"
 		and "CompanyPlan"."PlanNormalized" = upper("ImportData"."Plan")
 	)
	join "CompanyCoverageTier" on
	(
		"CompanyCoverageTier"."CarrierId" =  "CompanyCarrier"."Id"
		and "CompanyCoverageTier"."PlanTypeId" = "CompanyPlanType"."Id"
		and "CompanyCoverageTier"."PlanId" = "CompanyPlan"."Id"
		and "CompanyCoverageTier"."CoverageTierNormalized" = upper("ImportData"."CoverageTier")
	)
where
	"ImportData"."CompanyId" = ?
	and "ImportData"."ImportDate" = ?
	and "CompanyCarrier"."Id" = ?
	and "CompanyPlanType"."Id" = ?
	and "CompanyPlan"."Id" = ?
	and "CompanyCoverageTier"."Id" = ?
	and "LifeData"."LifeId" = ?

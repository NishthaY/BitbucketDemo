update "CompanyPlan"
	set "UserDescription" = (select "Plan" from "ImportData" where "ImportData"."CompanyId" = ? and upper("ImportData"."Carrier") = ? and upper("ImportData"."PlanType") = ? and upper("ImportData"."Plan") = ? limit 1	)
where "Id" = ?

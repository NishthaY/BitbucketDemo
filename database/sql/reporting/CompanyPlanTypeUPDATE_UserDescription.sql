update "CompanyPlanType"
	set "UserDescription" = (select "PlanType" from "ImportData" where "ImportData"."CompanyId" = ? and upper("ImportData"."Carrier") = ? and upper("ImportData"."PlanType") = ? limit 1)
where "Id" = ?

update "CompanyCoverageTier"
	set "UserDescription" = (select "CoverageTier" from "ImportData" where "ImportData"."CompanyId" = ? and upper("ImportData"."Carrier") = ? and upper("ImportData"."PlanType") = ? and upper("ImportData"."Plan") = ? and upper("ImportData"."CoverageTier") = ? limit 1	)
where "Id" = ?

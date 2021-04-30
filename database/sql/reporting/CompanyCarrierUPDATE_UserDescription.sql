update "CompanyCarrier"
	set "UserDescription" = (select "Carrier" from "ImportData" where "ImportData"."CompanyId" = ? and upper("ImportData"."Carrier") = ? limit 1)
where "Id" = ?

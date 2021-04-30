update "RelationshipData" set
	"MonthlyCost" = 0
	, "Volume" = 0
	, "Memo" = 'Cost and Volume set to zero dollars based on user elected relationship pricing model.'
where
	"RelationshipData"."Id" in (
		WITH subquery AS (

            -- Select all of our dependents, order them by EmployeeId and then
            -- add a new 'subindex' called rk that will number all dependents starting with 1
            -- by EmployeeId.
			select
				"RelationshipData"."Id" as "RelationshipDataId"
				, "ImportData"."FirstName"
				, "ImportData"."LastName"
				, "RelationshipData"."ImportDataId"
				, "RelationshipData"."RelationshipCode"
				, ROW_NUMBER() OVER(PARTITION BY "ImportData"."EmployeeId" order by "ImportData"."EmployeeId" asc) as rk
			from
				"RelationshipData"
				join "ImportData" on ( "ImportData"."Id" = "RelationshipData"."ImportDataId" )
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
			where
				"RelationshipData"."CompanyId" = ?
				and "RelationshipData"."ImportDate" = ?
				and "RelationshipData"."RelationshipCode" = 'dependent'
				and "CompanyCarrier"."Id" = ?
				and "CompanyPlanType"."PlanTypeCode" = ?
			order by "ImportData"."DateOfBirth" asc
		)
		select
			subquery."RelationshipDataId"
		from
			subquery
		where
			subquery.rk <> 1  -- Give us all dependents, except the first one.
	)

insert into "WashedData" ( "CompanyId", "ImportDataId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "CoverageStartDate", "CoverageEndDate", "PlanTypeCode", "WashRule", "WashedOutFlg", "WashDescription" )
select
	"ImportData"."CompanyId" as "CompanyId"
	, "ImportData"."Id" as "ImportDataId"
	, "ImportData"."ImportDate"
	, "LifeData"."LifeId" as "LifeId"
	, "CompanyCarrier"."Id" as "CarrierId"
	, "CompanyPlanType"."Id" as "PlanTypeId"
	, "CompanyPlan"."Id" as "PlanId"
	, "CompanyCoverageTier"."Id" as "CoverageTierId"
	, "ImportData"."CoverageStartDate" as "CoverageStartDate"
	, "ImportData"."CoverageEndDate" as "CoverageEndDate"
	, "CompanyPlanType"."PlanTypeCode" as "PlanTypeCode"
	, "CompanyPlanType"."WashRule" as "WashRule"
	, CASE
		-- Wash Rules: ( First )
		WHEN "CompanyPlanType"."WashRule" = '1' THEN
			CASE

				-- Coverage ends before it starts.
				WHEN "ImportData"."CoverageStartDate" is not null AND "ImportData"."CoverageEndDate" is not null AND "ImportData"."CoverageEndDate" < "ImportData"."CoverageStartDate" THEN true

				-- If person has a start date before the month and a terminate date after the month, count them.
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" IS NULL THEN false
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" >= "ImportData"."ImportDate" + interval '1 month'  THEN false

				-- If a person has a start date within the month and a terminate date of after the month, count them.
				WHEN "ImportData"."CoverageStartDate" >= "ImportData"."ImportDate" AND "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" + interval '1 month' AND "ImportData"."CoverageEndDate" IS NULL THEN false
				WHEN "ImportData"."CoverageStartDate" >= "ImportData"."ImportDate" AND "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" + interval '1 month' AND "ImportData"."CoverageEndDate" >= "ImportData"."ImportDate" + interval '1 month' THEN false

				-- If a person has a start date before the month and a terminate date that is the last day of the current month, count them.
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" > "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" = "ImportData"."ImportDate" + interval '1 month' + interval '-1 day' THEN false

				ELSE true
			END
		-- Wash Rules: ( Fifteenth )
		WHEN "CompanyPlanType"."WashRule" = '15' THEN
			CASE

				-- Coverage ends before it starts.
				WHEN "ImportData"."CoverageStartDate" is not null AND "ImportData"."CoverageEndDate" is not null AND "ImportData"."CoverageEndDate" < "ImportData"."CoverageStartDate" THEN true

				-- If a person has a start date before the month and a terminate date after the month, count them.
				-- If a person has a start date on or before the 15th of the month and a terminate date after the 15th of the month, count them.
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" + interval '15 days' AND "ImportData"."CoverageEndDate" IS NULL THEN false
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" + interval '15 days' AND "ImportData"."CoverageEndDate" >= "ImportData"."ImportDate" + interval '15 days'  THEN false

				-- If a person has a start date of the 16th or after and a terminate date after the month, do not count them.
				WHEN "ImportData"."CoverageStartDate" >= "ImportData"."ImportDate" + interval '15 days' AND "ImportData"."CoverageEndDate" IS NULL THEN true
				WHEN "ImportData"."CoverageStartDate" >= "ImportData"."ImportDate" + interval '15 days' AND "ImportData"."CoverageEndDate" >= "ImportData"."ImportDate" + interval '1 month'  THEN true

				-- If a person has a start date before the month and a terminate date on or before the 15th of the month, do not count them.
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" < "ImportData"."ImportDate" + interval '15 days' THEN true

				-- If a person has a start date before the month and a terminate date on the 16th or after, count them.
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" IS NULL THEN false
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" >=  "ImportData"."ImportDate" + interval '15 days' THEN false

				ELSE true

			END

		ELSE true
	END as "WashedOutFlg"
	, CASE
		-- Wash Rules: ( First )
		WHEN "CompanyPlanType"."WashRule" = '1' THEN
			CASE

				-- Coverage ends before it starts.
				WHEN "ImportData"."CoverageStartDate" is not null AND "ImportData"."CoverageEndDate" is not null AND "ImportData"."CoverageEndDate" < "ImportData"."CoverageStartDate" THEN 'WARNING:Coverage ends before it starts.'

				-- If person has a start date before the month and a terminate date after the month, count them.
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" IS NULL THEN 'If person has a start date before the month and a terminate date after the month, count them.'
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" > "ImportData"."ImportDate" + interval '1 month'  THEN 'If person has a start date before the month and a terminate date after the month, count them.'

				-- If a person has a start date within the month and a terminate date of after the month, count them.
				WHEN "ImportData"."CoverageStartDate" >= "ImportData"."ImportDate" AND "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" + interval '1 month' AND "ImportData"."CoverageEndDate" IS NULL THEN 'If a person has a start date within the month and a terminate date of after the month, count them.'
				WHEN "ImportData"."CoverageStartDate" >= "ImportData"."ImportDate" AND "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" + interval '1 month' AND "ImportData"."CoverageEndDate" > "ImportData"."ImportDate" + interval '1 month' THEN 'If a person has a start date within the month and a terminate date of after the month, count them.'

				-- If a person has a start date before the month and a terminate date that is the last day of the current month, count them.
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" > "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" = "ImportData"."ImportDate" + interval '1 month' + interval '-1 day' THEN 'If a person has a start date before the month and a terminate date that is the last day of the current month, count them.'

				ELSE 'Washed Out: Found no wash rules to indicate we should charge for this data'
			END
		-- Wash Rules: ( Fifteenth )
		WHEN "CompanyPlanType"."WashRule" = '15' THEN
			CASE

				-- Coverage ends before it starts.
				WHEN "ImportData"."CoverageStartDate" is not null AND "ImportData"."CoverageEndDate" is not null AND "ImportData"."CoverageEndDate" < "ImportData"."CoverageStartDate" THEN 'WARNING:Coverage ends before it starts.'

				-- If a person has a start date before the month and a terminate date after the month, count them.
				-- If a person has a start date on or before the 15th of the month and a terminate date after the 15th of the month, count them.
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" + interval '15 days' AND "ImportData"."CoverageEndDate" IS NULL THEN 'If a person has a start date on or before the 15th of the month and a terminate date after the month, count them.'
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" + interval '15 days' AND "ImportData"."CoverageEndDate" > "ImportData"."ImportDate" + interval '15 days'  THEN 'If a person has a start date on or before the 15th of the month and a terminate date after the 15th of the month, count them.'

				-- If a person has a start date of the 16th or after and a terminate date after the month, do not count them.
				WHEN "ImportData"."CoverageStartDate" >= "ImportData"."ImportDate" + interval '15 days' AND "ImportData"."CoverageEndDate" IS NULL THEN 'Washed Out. If a person has a start date of the 16th or after and a terminate date after the month, do not count them'
				WHEN "ImportData"."CoverageStartDate" >= "ImportData"."ImportDate" + interval '15 days' AND "ImportData"."CoverageEndDate" > "ImportData"."ImportDate" + interval '1 month'  THEN 'Washed Out. If a person has a start date of the 16th or after and a terminate date after the month, do not count them'

				-- If a person has a start date before the month and a terminate date on or before the 15th of the month, do not count them.
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" < "ImportData"."ImportDate" + interval '15 days' THEN 'Washed Out. If a person has a start date before the month and a terminate date on or before the 15th of the month, do not count them.'

				-- If a person has a start date before the month and a terminate date on the 16th or after, count them.
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" IS NULL THEN 'If a person has a start date before the month and a terminate date on the 16th or after, count them.'
				WHEN "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" AND "ImportData"."CoverageEndDate" >=  "ImportData"."ImportDate" + interval '15 days' THEN 'If a person has a start date before the month and a terminate date on the 16th or after, count them.'

				ELSE 'Washed Out: Found no wash rules to indicate we should charge for this data'


			END

		ELSE 'WARNING:Unsupported wash rule.'
	END as "WashDescription"
from
	"ImportData"
	join "LifeData" on ( "LifeData"."ImportDataId" = "ImportData"."Id" )
	join "CompanyLife" on ( "CompanyLife"."CompanyId" = "ImportData"."CompanyId" and "LifeData"."LifeId" = "CompanyLife"."Id" AND "CompanyLife"."Enabled" = true ) -- BAH, the life must be enabled.
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
	and "ImportData"."Finalized" = false
	and "CompanyPlanType"."Ignored" = false
	and "CompanyPlanType"."PlanTypeCode" is not null

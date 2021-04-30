insert into "RelationshipData" ( "CompanyId", "ImportDataId", "ImportDate", "RelationshipCode", "MonthlyCost", "Volume" )
select
	"ImportData"."CompanyId"
	, "ImportData"."Id" as "ImportDataId"
	, "ImportData"."ImportDate"
	, "CompanyRelationship"."RelationshipCode"
	, "ImportData"."MonthlyCost"
	, "ImportData"."Volume"
from
	"ImportData"
	left join "CompanyRelationship" on
	(
		"CompanyRelationship"."CompanyId" = "ImportData"."CompanyId"
		and "CompanyRelationship"."RelationshipNormalized" = trim(both ' ' from upper("ImportData"."Relationship"))
	)
    left join "Relationship" on ( "Relationship"."Code" = "CompanyRelationship"."RelationshipCode" )
where
	"ImportData"."CompanyId" = ?
	and "ImportData"."ImportDate" = ?
	and "CompanyRelationship"."Id" is not null
order by "ImportData"."Id" asc

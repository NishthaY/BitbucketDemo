select
	CASE when count(*) = 0 then true else false end as "AllMapped"
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
	and ( "CompanyRelationship"."RelationshipCode" is null OR "CompanyRelationship"."RelationshipCode" = '' )
limit 1

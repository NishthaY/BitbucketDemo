select
    "CompanyRelationship"."Id" as "CompanyRelationshipId"
	, "CompanyRelationship"."CompanyId"
	, "CompanyRelationship"."UserDescription"
	, "CompanyRelationship"."RelationshipCode"
    , "Relationship"."Description" as "RelationshipDescription"
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
group by
    "CompanyRelationship"."Id"
	, "CompanyRelationship"."CompanyId"
	, "CompanyRelationship"."UserDescription"
	, "CompanyRelationship"."RelationshipCode"
    , "Relationship"."Description"
order by
	"CompanyRelationship"."UserDescription" asc

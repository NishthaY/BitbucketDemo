insert into "CompanyRelationship" ( "CompanyId", "RelationshipNormalized", "UserDescription" )
select

	"ImportData"."CompanyId"
	, trim(both ' ' from upper("ImportData"."Relationship")) as "RelationshipNormalized"
	, trim(both ' ' from "ImportData"."Relationship") as "UserDescription"
from
	"ImportData"
	left join "CompanyRelationship" on
	(
		"CompanyRelationship"."CompanyId" = "ImportData"."CompanyId"
		and "CompanyRelationship"."RelationshipNormalized" = trim(both ' ' from upper("ImportData"."Relationship"))
	)
where
	"ImportData"."CompanyId" = ?
	and "ImportData"."ImportDate" = ?
	and "CompanyRelationship"."Id" is null
	and "ImportData"."Relationship" is not null
	and trim(both ' ' from "ImportData"."Relationship") <> ''
group by
	"ImportData"."CompanyId"
	, trim(both ' ' from upper("ImportData"."Relationship"))
	, trim(both ' ' from "ImportData"."Relationship")

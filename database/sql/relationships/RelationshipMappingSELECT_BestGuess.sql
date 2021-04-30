select
	"Relationship"."Code" as "RelationshipCode"
	, "Relationship"."Description" as "RelationshipDescription"
from
	"RelationshipMapping"
	join "Relationship" on ( "Relationship"."Code" = "RelationshipMapping"."RelationshipCode" )
where
	trim(both ' ' from upper("RelationshipMapping"."UserDescription")) = trim(both ' ' from upper(?))
limit 1

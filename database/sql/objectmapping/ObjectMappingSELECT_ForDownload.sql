select
  DISTINCT("Input") as "AllowedValues"
from
  "ObjectMappingProperty"
  join "ObjectMapping" on ( "ObjectMapping"."Code" = "ObjectMappingProperty"."Code")
where
  "ObjectMappingProperty"."Id" = ?
order by "Input" asc
select
    "Name" as "name"
     ,"Display" as "display"
     ,"Required" as "required"
     ,"Encrypted" as "encrypted"
     ,"DefaultValue" as "default_value"
     ,"Conditional" as "conditional"
     ,"ConditionalList" as "conditional_list"
     ,"NormalizationRegEx" as "normalization_regex"
from
    "CompanyParentMappingColumn"
WHERE
    "CompanyParentId" = ?

select
    pref."CompanyParentId"
    , replace(coalesce(cmc."Display",mc."Display"), ' ', '') as "ColumnName"
    , replace(upper(coalesce(cmc."Display",mc."Display")),' ','') as "ColumnNameNormalized"
    , coalesce(cmc."Name",mc."Name") as "MappingColumnName"
    , ? as "ColumnNumber"
from
    "CompanyParentPreference" pref
    left join "CompanyMappingColumn" cmc on ( pref."CompanyParentId" = cmc."CompanyId" and pref."Value" = cmc."Name" )
    left join "MappingColumns" mc on ( pref."Value" = mc."Name" )
where
    pref."CompanyParentId" = ?
    and pref."Group" = 'column_map'
    and pref."GroupCode" = 'col' || ?::TEXT
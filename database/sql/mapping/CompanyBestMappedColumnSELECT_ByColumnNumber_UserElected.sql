select
  cp."CompanyId"
  , replace(coalesce(cmc."Display",mc."Display"), ' ', '') as "ColumnName"
  , replace(upper(coalesce(cmc."Display",mc."Display")),' ','') as "ColumnNameNormalized"
  , coalesce(cmc."Name",mc."Name") as "MappingColumnName"
  , ? as "ColumnNumber"
from
  "CompanyPreference" cp
  left join "CompanyMappingColumn" cmc on
                                         (
                                           cp."CompanyId" = cmc."CompanyId"
                                           and cp."Value" = cmc."Name"
                                           )
  left join "MappingColumns" mc on
                                  (
                                    cp."Value" = mc."Name"
                                    )
where
  cp."CompanyId" = ?
  and cp."Group" = 'column_map'
  and cp."GroupCode" = 'col' || ?::TEXT
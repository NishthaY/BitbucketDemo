select * from "CompanyParentPreference"
where "CompanyParentId" = ? and "Group" = 'column_map'
order by trim(leading 'col'  from "GroupCode")::int asc
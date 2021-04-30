select * from "CompanyPreference"
where "CompanyId" = ? and "Group" = 'column_map'
order by trim(leading 'col'  from "GroupCode")::int asc
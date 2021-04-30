select CASE WHEN count(*) > 0 then true else false end as "HasClarifications" from "RetroDataLifeEvent" where "CompanyId" = ? and "ImportDate" = ? and "AutoSelected" = false

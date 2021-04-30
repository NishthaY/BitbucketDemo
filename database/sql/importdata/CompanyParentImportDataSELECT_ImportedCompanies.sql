select
    d."Id" as "ImportDataId"
     , d."CompanyParentId"
     , d."Company" as "ImportDescription"
     , map."CompanyNormalized"
     , map."UserDescription"
     , map."CompanyId"
     , case when map."Ignored" is null then false else map."Ignored" end as "Ignored"
from
    "CompanyParentImportData" d
        left join "CompanyParentMapCompany" map on ( map."CompanyParentId" = d."CompanyParentId" and  map."CompanyNormalized" = trim(upper("Company")) )
where
        d."CompanyParentId" = ?
order by d."Company" asc
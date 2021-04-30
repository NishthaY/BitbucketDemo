select
    m."UserDescription"
     ,c."CompanyName"
     ,c."Id" as "CompanyId"
from
    "CompanyParentMapCompany" m
        join "Company" c on (c."Id" = m."CompanyId")
        join "CompanyParentImportData" i on ( upper(i."Company") = m."CompanyNormalized")
where i."CompanyParentId" = ?
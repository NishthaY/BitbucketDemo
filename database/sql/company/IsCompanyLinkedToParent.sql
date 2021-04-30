select
    CASE WHEN ( count(*) >= 1 ) THEN 1 ELSE 0 END as linked
from "CompanyParentCompanyRelationship" where "CompanyId"=? and "CompanyParentId"=?

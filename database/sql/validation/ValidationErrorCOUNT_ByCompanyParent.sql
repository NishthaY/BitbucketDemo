select
    CASE when count(*) = 0 THEN true ELSE false END as valid
from "ValidationErrors"
where "CompanyParentId" = ?
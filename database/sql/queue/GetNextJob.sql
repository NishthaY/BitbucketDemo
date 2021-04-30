SELECT
  *
FROM
  "ProcessQueue"
WHERE
  "EndTime" IS NULL
  AND "StartTime" IS NULL
  AND "ExecutionTime"<=NOW()
  and
  (
    -- Only select items that do not have a group number or do have a group number, but that group numer is not currently running.
    "GroupId" is null
    OR "GroupId" not in ( select "GroupId" from "ProcessQueue" where "StartTime" is not null AND "EndTime" is null and "GroupId" is not null )
  )
ORDER BY "QueueTime" ASC
LIMIT 1

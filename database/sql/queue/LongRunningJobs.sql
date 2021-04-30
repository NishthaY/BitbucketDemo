select * from "ProcessQueue" where "StartTime" < ( NOW() - INTERVAL '60 minutes' ) and "EndTime" is null

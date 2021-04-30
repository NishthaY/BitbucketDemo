UPDATE "ProcessQueue" SET "EndTime"=NOW(), "Failed"=true, "ErrorMessage"=? WHERE "Id"=?

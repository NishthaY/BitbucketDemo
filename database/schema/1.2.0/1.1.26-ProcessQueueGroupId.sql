\set db advice2pay


-- Add the GroupId column to the ProcessQueue table.
DO $$
BEGIN
  BEGIN
    ALTER TABLE "ProcessQueue" ADD "GroupId" INT NULL;
    EXCEPTION
    WHEN duplicate_column THEN RAISE NOTICE 'column GroupId already exists in ProcessQueue.';
  END;
END;
$$
\set db advice2pay

-- column was a DATE.  Hard to see the time difference with that.
ALTER TABLE public."LogTimer" ALTER COLUMN "Timestamp" TYPE TIMESTAMP WITH TIME ZONE USING "Timestamp"::TIMESTAMP WITH TIME ZONE;
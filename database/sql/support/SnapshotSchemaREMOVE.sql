-- Drop the snapshot schema if it currently exists.
DO $$
BEGIN

    IF EXISTS(
        SELECT schema_name
          FROM information_schema.schemata
          WHERE schema_name = 'snapshot'
      )
    THEN
      drop schema "snapshot" CASCADE;
    END IF;

END
$$; 

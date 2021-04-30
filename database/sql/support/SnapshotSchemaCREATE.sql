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

-- Copy the public schema to the snapshot schema, not bring data.
select clone_schema('public','snapshot', FALSE);

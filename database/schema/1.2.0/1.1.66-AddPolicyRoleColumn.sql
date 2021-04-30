\set db advice2pay

-- 1. Add the new column to the Mapping Columns table.
insert into "MappingColumns" ( "Name", "Display", "Required", "DefaultValue", "ColumnName", "Encrypted", "Conditional", "ConditionalList", "NormalizationRegEx" ) VALUES ( 'policy_role', 'Policy Role', false, null, null, false, false, null, null );

-- 2. Add new records to the MappingColumnHeaders table
insert into "MappingColumnHeaders" ("Name", "Header") values ( 'policy_role', 'POLICY_ROLE' );
insert into "MappingColumnHeaders" ("Name", "Header") values ( 'policy_role', 'PERSON_POLICY_ROLE');

-- 3. Add the new columns to the import tables.
ALTER TABLE "ImportData"                ADD "PolicyRole" TEXT NULL;
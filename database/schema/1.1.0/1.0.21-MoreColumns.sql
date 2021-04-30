\set db advice2pay

-- 1. Add the new column to the Mapping Columns table.
insert into "MappingColumns" ( "Name", "Display", "Required", "DefaultValue", "ColumnName", "Encrypted" ) VALUES ( 'group_number', 'Group Number', false, null, null, false );
insert into "MappingColumns" ( "Name", "Display", "Required", "DefaultValue", "ColumnName", "Encrypted" ) VALUES ( 'enrollment_state', 'Enrollment State', false, null, null, false );

-- 2. Add new records to the MappingColumnHeaders table
insert into "MappingColumnHeaders" ("Name", "Header") values ( 'group_number', 'Group Number' );
insert into "MappingColumnHeaders" ("Name", "Header") values ( 'enrollment_state', 'Enrollment State' );

-- 4. Add the new columns to the ImportData table.
ALTER TABLE "ImportData" ADD "GroupNumber" TEXT NULL;
ALTER TABLE "ImportData" ADD "EnrollmentState" TEXT NULL;

\set db advice2pay

-- Add an Optional Column Mapping called Policy.
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'policy', 'Policy', false );
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'policy', 'Policy');
ALTER TABLE "ImportData" ADD COLUMN "Policy" text NULL;

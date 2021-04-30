\set db advice2pay

ALTER TABLE "MappingColumns" ADD "Encrypted" BOOLEAN DEFAULT FALSE  NULL;

-- Keep these columns encrypted in the database.
update "MappingColumns" set "Encrypted" = true where "Name" = 'last_name';
update "MappingColumns" set "Encrypted" = true where "Name" = 'first_name';
update "MappingColumns" set "Encrypted" = true where "Name" = 'email2';
update "MappingColumns" set "Encrypted" = true where "Name" = 'ssn';
update "MappingColumns" set "Encrypted" = true where "Name" = 'email1';
update "MappingColumns" set "Encrypted" = true where "Name" = 'city';
update "MappingColumns" set "Encrypted" = true where "Name" = 'state';
update "MappingColumns" set "Encrypted" = true where "Name" = 'address2';
update "MappingColumns" set "Encrypted" = true where "Name" = 'address1';
update "MappingColumns" set "Encrypted" = true where "Name" = 'phone1';
update "MappingColumns" set "Encrypted" = true where "Name" = 'phone2';
update "MappingColumns" set "Encrypted" = true where "Name" = 'eid';
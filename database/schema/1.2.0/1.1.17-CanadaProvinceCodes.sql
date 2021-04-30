\set db advice2pay

insert into "ObjectMappingProperty" ( "Id", "Code", "Display", "Downloadable" ) values ( 2, 'CAProvinces', 'Canada Provinces', true);
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 200, 'CAProvinces', 'AB', 'AB' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 201, 'CAProvinces', 'BC', 'BC' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 202, 'CAProvinces', 'MB', 'MB' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 203, 'CAProvinces', 'NB', 'NB' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 204, 'CAProvinces', 'NL', 'NL' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 205, 'CAProvinces', 'NT', 'NT' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 206, 'CAProvinces', 'NS', 'NS' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 207, 'CAProvinces', 'NU', 'NU' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 208, 'CAProvinces', 'ON', 'ON' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 209, 'CAProvinces', 'PE', 'PE' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 210, 'CAProvinces', 'QC', 'QC' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 211, 'CAProvinces', 'SK', 'SK' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 212, 'CAProvinces', 'YT', 'YT' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 213, 'CAProvinces', 'Alberta', 'AB' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 214, 'CAProvinces', 'British Columbia', 'BC' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 215, 'CAProvinces', 'Manitoba', 'MB' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 216, 'CAProvinces', 'New Brunswick', 'NB' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 217, 'CAProvinces', 'Newfoundland and Labrador', 'NL' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 218, 'CAProvinces', 'Northwest Territories', 'NT' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 219, 'CAProvinces', 'Nova Scotia', 'NS' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 220, 'CAProvinces', 'Nunavut', 'NU' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 221, 'CAProvinces', 'Ontario', 'ON' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 222, 'CAProvinces', 'Prince Edward Island', 'PE' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 223, 'CAProvinces', 'Quebec', 'QC' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 224, 'CAProvinces', 'Saskatchewan', 'SK' ) on conflict("Id") do nothing;
insert into "ObjectMapping" ( "Id", "Code", "Input", "Output" ) values ( 225, 'CAProvinces', 'Yukon', 'YT' ) on conflict("Id") do nothing;

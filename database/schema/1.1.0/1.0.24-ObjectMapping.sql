\set db advice2pay

drop table "ObjectMapping";

create table "ObjectMapping"
(
  "Id" serial not null constraint "ObjectMapping_pkey" primary key,
  "Code" text not null,
  "Input" text not null,
  "Output" text not null
);
ALTER TABLE "ObjectMapping" OWNER TO :db;

-- Adding STATE mapping items.
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Armed Forces America', 'AA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Armed Forces', 'AE' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Armed Forces Pacific', 'AP' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Alaska', 'AK' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Alabama', 'AL' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Arkansas', 'AR' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Arizona', 'AZ' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'California', 'CA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Colorado', 'CO' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Connecticut', 'CT' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Washington DC', 'DC' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'District of Columbia', 'DC' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Delaware', 'DE' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Florida', 'FL' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Georgia', 'GA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Guam', 'GU' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Hawaii', 'HI' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Iowa', 'IA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Idaho', 'ID' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Illinois', 'IL' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Indiana', 'IN' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Kansas', 'KS' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Kentucky', 'KY' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Louisiana', 'LA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Massachusetts', 'MA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Maryland', 'MD' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Maine', 'ME' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Michigan', 'MI' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Minnesota', 'MN' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Missouri', 'MO' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Mississippi', 'MS' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Montana', 'MT' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'North Carolina', 'NC' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'North Dakota', 'ND' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Nebraska', 'NE' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'New Hampshire', 'NH' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'New Jersey', 'NJ' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'New Mexico', 'NM' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Nevada', 'NV' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'New York', 'NY' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Ohio', 'OH' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Oklahoma', 'OK' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Oregon', 'OR' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Pennsylvania', 'PA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Puerto Rico', 'PR' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Rhode Island', 'RI' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'South Carolina', 'SC' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'South Dakota', 'SD' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Tennessee', 'TN' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Texas', 'TX' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Utah', 'UT' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Virginia', 'VA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Virgin Islands', 'VI' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Vermont', 'VT' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Washington', 'WA'	 );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Wisconsin', 'WI' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'West Virginia', 'WV' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'Wyoming', 'WY' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'AA', 'AA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'AE', 'AE' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'AP', 'AP' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'AK', 'AK' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'AL', 'AL' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'AR', 'AR' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'AZ', 'AZ' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'CA', 'CA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'CO', 'CO' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'CT', 'CT' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'DC', 'DC' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'DE', 'DE' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'FL', 'FL' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'GA', 'GA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'GU', 'GU' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'HI', 'HI' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'IA', 'IA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'ID', 'ID' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'IL', 'IL' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'IN', 'IN' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'KS', 'KS' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'KY', 'KY' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'LA', 'LA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'MA', 'MA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'MD', 'MD' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'ME', 'ME' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'MI', 'MI' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'MN', 'MN' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'MO', 'MO' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'MS', 'MS' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'MT', 'MT' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'NC', 'NC' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'ND', 'ND' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'NE', 'NE' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'NH', 'NH' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'NJ', 'NJ' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'NM', 'NM' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'NV', 'NV' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'NY', 'NY' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'OH', 'OH' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'OK', 'OK' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'OR', 'OR' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'PA', 'PA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'PR', 'PR' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'RI', 'RI' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'SC', 'SC' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'SD', 'SD' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'TN', 'TN' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'TX', 'TX' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'UT', 'UT' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'VA', 'VA' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'VI', 'VI' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'VT', 'VT' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'WA', 'WA'	);
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'WI', 'WI' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'WV', 'WV' );
insert into "ObjectMapping" ( "Code", "Input", "Output" ) values ( 'USAStates', 'WY', 'WY' );





create table "ObjectMappingProperty"
(
  "Id" integer not null constraint "ObjectMappingProperty_pkey" primary key,
  "Code" text not null,
  "Display" text not null,
  "Downloadable" boolean default false not null
);
ALTER TABLE "ObjectMappingProperty" OWNER TO :db;
insert into "ObjectMappingProperty" ( "Id", "Code", "Display", "Downloadable" ) values ( 1, 'USAStates', 'USA States', true);



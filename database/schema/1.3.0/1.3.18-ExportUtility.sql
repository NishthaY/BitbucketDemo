\set db advice2pay


-- CREATE the Export table.
create table "Export"
(
    "Id" serial not null constraint """Export""_pk" primary key,
    "Identifier" text not null,
    "IdentifierType" text not null,
    "Created" timestamp with time zone default now() not null,
    "Status" text,
    "Modified" timestamp with time zone default now()
);
create unique index """Export""_""Id""_uindex" on "Export" ("Id");



-- Create the ExportProperty table.
create table "ExportProperty"
(
    "Id" serial not null constraint """ExportProperty""_pk" primary key,
    "ExportId" integer not null,
    "PropertyKey" text not null,
    "PropertyValue" text
);
create unique index """ExportProperty""_""Id""_uindex" on "ExportProperty" ("Id");



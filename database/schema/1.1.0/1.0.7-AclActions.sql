\set db advice2pay

create table "AclAction"
(
  "Id" serial not null
    constraint "AclAction_pkey"
    primary key,
  "Name" text not null,
  "Description" text not null
)
;
create table "AclActionRelationship"
(
  "AclId" integer not null,
  "AclActionId" integer not null
)
;

ALTER TABLE "AclAction" OWNER TO :db;
ALTER TABLE "AclActionRelationship" OWNER TO :db;

-- Create new access control lists
insert into "Acl" ( "Name", "Description" ) values ( 'All', 'no restrictions.  do good.' );
insert into "Acl" ( "Name", "Description" ) values ( 'Staff', 'read/write access to all companies and parent companies.' );
insert into "Acl" ( "Name", "Description" ) values ( 'User', 'read-only access to company data.' );
insert into "Acl" ( "Name", "Description" ) values ( 'Manager', 'read/write access to company data.' );
insert into "Acl" ( "Name", "Description" ) values ( 'Parent User', 'read access to parent company data with read/write access to authorized companies data associated with the parent.' );
insert into "Acl" ( "Name", "Description" ) values ( 'Parent Manager', 'read/write access to a parent company with read/write access to companies associated with the parent.' );
insert into "Acl" ( "Name", "Description" ) values ( 'Support', 'access to support tools' );

-- Create access control actions.
insert into "AclAction" ( "Name", "Description" ) values ('company_read', 'read-only access to company data.' );
insert into "AclAction" ( "Name", "Description" ) values ('company_write', 'read/write access to company data.' );
insert into "AclAction" ( "Name", "Description" ) values ('parent_company_read', 'read-only access to assigned parent company data.' );
insert into "AclAction" ( "Name", "Description" ) values ('parent_company_write', 'read/write access to parent company data.' );
insert into "AclAction" ( "Name", "Description" ) values ('support_write', 'read/write access to support tools.' );
insert into "AclAction" ( "Name", "Description" ) values ('support_read', 'read-only access to support tools.' );

-- Create relationships between the access control list and the possible actions.
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'Staff' ), ( select "Id" from "AclAction" where "Name" = 'company_write' ) );
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'Staff' ), ( select "Id" from "AclAction" where "Name" = 'company_read' ) );
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'Staff' ), ( select "Id" from "AclAction" where "Name" = 'parent_company_write' ) );
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'Staff' ), ( select "Id" from "AclAction" where "Name" = 'parent_company_read' ) );
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'Staff' ), ( select "Id" from "AclAction" where "Name" = 'support_read' ) );
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'User' ), ( select "Id" from "AclAction" where "Name" = 'company_read' ) );
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'Manager' ), ( select "Id" from "AclAction" where "Name" = 'company_write' ) );
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'Manager' ), ( select "Id" from "AclAction" where "Name" = 'company_read' ) );
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'Parent User' ), ( select "Id" from "AclAction" where "Name" = 'parent_company_read' ) );
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'Parent Manager' ), ( select "Id" from "AclAction" where "Name" = 'parent_company_write' ) );
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'Parent Manager' ), ( select "Id" from "AclAction" where "Name" = 'parent_company_read' ) );
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'Support' ), ( select "Id" from "AclAction" where "Name" = 'support_write' ) );





-- MIGRATION!
-- Move any existing users into the new structure.

-- Migrate admin -> staff
update "UserAcl" set
  "AclId" = ( select "Id" from "Acl" where "Name" = 'Staff' )
where
  "UserAcl"."AclId" = ( select "Id" from "Acl" where "Name" = 'admin' );

-- Migrate customer_write -> Manager
update "UserAcl" set
  "AclId" = ( select "Id" from "Acl" where "Name" = 'Manager' )
where
  "UserAcl"."AclId" = ( select "Id" from "Acl" where "Name" = 'company_write' );

-- Migrate customer_read -> User
update "UserAcl" set
  "AclId" = ( select "Id" from "Acl" where "Name" = 'User' )
where
  "UserAcl"."AclId" = ( select "Id" from "Acl" where "Name" = 'company_read' );
-- Migrate parent_customer_write -> Parent Manager
update "UserAcl" set
  "AclId" = ( select "Id" from "Acl" where "Name" = 'Parent Manager' )
where
  "UserAcl"."AclId" = ( select "Id" from "Acl" where "Name" = 'company_parent_write' );

-- Migrate parent_customer_write -> Parent User
update "UserAcl" set
  "AclId" = ( select "Id" from "Acl" where "Name" = 'Parent User' )
where
  "UserAcl"."AclId" = ( select "Id" from "Acl" where "Name" = 'company_parent_write' );




-- clean up the old Acl records.
delete from "Acl" where "Id" < (select "Id" from "Acl" where "Name" = 'All');
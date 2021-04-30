\set db advice2pay

insert into "AclAction" ("Id", "Name", "Description" ) values ( 7, 'pii_download', 'Permission to download reports containing PII data.' );
insert into "Acl" ("Id", "Name", "Description") values ( 13, 'PII Download', 'download access ro reports containing pii data.');
insert into "AclActionRelationship" ("AclId", "AclActionId" ) values ( ( select "Id" from "Acl" where "Name" = 'PII Download' ), ( select "Id" from "AclAction" where "Name" = 'pii_download' ) );

-- MANUAL - One Time Only - SANDBOX
-- Manually run these commands once manually.
--insert into "UserAcl" ( 44, 13, null, null ) values ( "UserId", "AclId", "Target", "TargetId");
--insert into "UserAcl" ( 8, 13, null, null ) values ( "UserId", "AclId", "Target", "TargetId");
--insert into "UserAcl" ( 41, 13, null, null ) values ( "UserId", "AclId", "Target", "TargetId");
--insert into "UserAcl" ( 43, 13, null, null ) values ( "UserId", "AclId", "Target", "TargetId");
--insert into "UserAcl" ( 45, 13, null, null ) values ( "UserId", "AclId", "Target", "TargetId");

-- MANUAL - One Time Only - PROD
-- Manually run these two commands in production post launch.
-- insert into "UserAcl" ( 7, 13, null, null ) values ( "UserId", "AclId", "Target", "TargetId");
-- insert into "UserAcl" ( 8, 13, null, null ) values ( "UserId", "AclId", "Target", "TargetId");
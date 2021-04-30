-- User
delete from "public"."User" where "Id" in ( select "Id" from "snapshot"."User" );
insert into "public"."User" ( "Id", "EmailAddress", "Password", "FirstName", "LastName", "Enabled" )  select "Id", "EmailAddress", "Password", "FirstName", "LastName", "Enabled" from "snapshot"."User";

-- UserAcl
delete from "public"."UserAcl" where "UserId" in ( select "UserId" from "snapshot"."UserAcl" );
insert into "public"."UserAcl" ( "UserId", "AclId" ) select "UserId", "AclId" from "snapshot"."UserAcl";

-- Company
delete from "public"."Company" where "Id" in ( select "Id" from "snapshot"."Company" );
insert into "public"."Company" ( "Id", "CompanyName", "CompanyAddress", "CompanyCity", "CompanyState", "CompanyPostal", "Enabled" ) select "Id", "CompanyName", "CompanyAddress", "CompanyCity", "CompanyState", "CompanyPostal", "Enabled" from "snapshot"."Company";

-- UserCompany
delete from "public"."UserCompany" where "UserId" in ( select "UserId" from "snapshot"."UserCompany" );
insert into "public"."UserCompany" ( "UserId", "CompanyId" ) select "UserId", "CompanyId"  from "snapshot"."UserCompany";

-- CompanyParent
delete from "public"."CompanyParent" where "Id" in ( select "Id" from "snapshot"."CompanyParent" );
insert into "public"."CompanyParent" ( "Id", "Name", "Address", "City", "State", "Postal", "Seats", "Enabled" ) select "Id", "Name", "Address", "City", "State", "Postal", "Seats", "Enabled" from "snapshot"."CompanyParent";

-- CompanyParentCompanyRelationship
delete from "public"."CompanyParentCompanyRelationship" where "CompanyParentId" in ( select "CompanyParentId" from "snapshot"."CompanyParentCompanyRelationship" );
insert into "public"."CompanyParentCompanyRelationship" (  "CompanyParentId", "CompanyId" ) select "CompanyParentId", "CompanyId" from "snapshot"."CompanyParentCompanyRelationship";

-- UserCompanyParentRelationship
delete from "public"."UserCompanyParentRelationship" where "UserId" in ( select "UserId" from "snapshot"."UserCompanyParentRelationship" );
insert into "public"."UserCompanyParentRelationship" ( "UserId", "CompanyParentId" ) select "UserId", "CompanyParentId" from "snapshot"."UserCompanyParentRelationship";

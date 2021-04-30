-- User
insert into "snapshot"."User" ( "Id", "EmailAddress", "Password", "FirstName", "LastName", "Enabled" )  select "Id", "EmailAddress", "Password", "FirstName", "LastName", "Enabled" from "public"."User";

-- UserAcl
insert into "snapshot"."UserAcl" ( "UserId", "AclId" ) select "UserId", "AclId" from "public"."UserAcl";

-- Company
insert into "snapshot"."Company" ( "Id", "CompanyName", "CompanyAddress", "CompanyCity", "CompanyState", "CompanyPostal", "Enabled" ) select "Id", "CompanyName", "CompanyAddress", "CompanyCity", "CompanyState", "CompanyPostal", "Enabled" from "public"."Company";

-- UserCompany
insert into "snapshot"."UserCompany" ( "UserId", "CompanyId" ) select "UserId", "CompanyId"  from "public"."UserCompany";

-- CompanyParent
insert into "snapshot"."CompanyParent" ( "Id", "Name", "Address", "City", "State", "Postal", "Seats", "Enabled" ) select "Id", "Name", "Address", "City", "State", "Postal", "Seats", "Enabled" from "public"."CompanyParent";

-- CompanyParentCompanyRelationship
insert into "snapshot"."CompanyParentCompanyRelationship" (  "CompanyParentId", "CompanyId" ) select "CompanyParentId", "CompanyId" from "public"."CompanyParentCompanyRelationship";

-- UserCompanyParentRelationship
insert into "snapshot"."UserCompanyParentRelationship" ( "UserId", "CompanyParentId" ) select "UserId", "CompanyParentId" from "public"."UserCompanyParentRelationship";

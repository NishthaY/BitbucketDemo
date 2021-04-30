\set db advice2pay

-- Adding Manasa Shetty as a power user for transition.
INSERT INTO "User" ( "EmailAddress", "Password", "FirstName", "LastName", "Enabled") VALUES ( 'manasa.shetty@transamerica.com', '', 'Manasa', 'Shetty', true);
insert into "UserAcl" ( "UserId", "AclId" )  values ( (select "Id" from "User" where "EmailAddress" = 'manasa.shetty@transamerica.com'), (select "Id" from "Acl" where "Name" = 'All' ) );
insert into "UserCompany" ( "UserId", "CompanyId" ) values ( (select "Id" from "User" where "EmailAddress"='manasa.shetty@transamerica.com'), (select "Id" from "Company" where "CompanyName" = 'Advice2Pay'));
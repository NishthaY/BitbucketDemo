\set db advice2pay

-- Physically remove the guest
delete from "UserAcl" where "UserId" = 1;
delete from "UserCompany" where "UserId" = 1;
delete from "UserCompanyParentRelationship" where "UserId" = 1;
delete from "UserPreference" where "UserId" = 1;
delete from "UserResponsibleForCompany" where "UserId" = 1;
delete from "Login" where "UserId" = 1;
delete from "Audit" where "UserId" = 1;
delete from "HistoryChangeToCompany" where "UserId" = 1;
delete from "HistoryFailedJob" where "UserId" = 1;
delete from "Log" where "UserId" = 1;
delete from "Wizard" where "UserId" = 1;
delete from "User" where "Id" = 1;



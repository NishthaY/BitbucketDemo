select case when count(*) = 0 then true else false end as "CodeExpired" from "Login" where "UserId" = ? and "TwoFactorExpiration" > NOW()
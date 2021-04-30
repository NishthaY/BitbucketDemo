\set db advice2pay

ALTER TABLE "Login" ADD COLUMN "TwoFactorExpiration" TIMESTAMP default NOW();

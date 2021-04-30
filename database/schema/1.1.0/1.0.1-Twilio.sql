\set db advice2pay

CREATE TABLE "Login"
(
    "UserId" integer NOT NULL
  , "TwoFactorEnabled" boolean NOT NULL DEFAULT TRUE
  , "TwoFactorPhoneNumber" text NULL
  , "TwoFactorHash" text NULL
  , CONSTRAINT "UserId" PRIMARY KEY ("UserId")

)
WITH (
OIDS=FALSE
);
ALTER TABLE "Login" OWNER TO :db;